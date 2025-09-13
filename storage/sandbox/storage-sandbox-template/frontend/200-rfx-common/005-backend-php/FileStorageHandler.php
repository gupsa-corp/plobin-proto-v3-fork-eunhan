<?php

/**
 * 파일 저장소 핸들러
 * 파일 업로드, 다운로드, 목록 관리, 삭제 기능을 제공
 */
class FileStorageHandler
{
    private $uploadPath;
    private $downloadPath;
    private $maxFileSize;
    private $allowedExtensions;
    private $maxFilesPerUpload;

    public function __construct()
    {
        $this->uploadPath = dirname(__FILE__) . '/uploads/';
        $this->downloadPath = dirname(__FILE__) . '/../downloads/';
        $this->maxFileSize = 50 * 1024 * 1024; // 50MB
        $this->maxFilesPerUpload = 20;
        $this->allowedExtensions = [
            // 이미지
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg',
            // 문서
            'pdf', 'doc', 'docx', 'txt', 'rtf', 'odt',
            // 스프레드시트
            'xls', 'xlsx', 'csv', 'ods',
            // 프레젠테이션
            'ppt', 'pptx', 'odp',
            // 압축파일
            'zip', 'rar', '7z', 'tar', 'gz',
            // 미디어
            'mp4', 'avi', 'mov', 'wmv', 'flv', 'mp3', 'wav', 'flac',
            // 기타
            'json', 'xml', 'html', 'css', 'js', 'php', 'py', 'java', 'c', 'cpp'
        ];

        $this->ensureDirectoryExists($this->uploadPath);
        $this->ensureDirectoryExists($this->downloadPath);
    }

    /**
     * 디렉토리 존재 확인 및 생성
     */
    private function ensureDirectoryExists($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * 다중 파일 업로드 처리
     */
    public function handleMultipleUpload($files)
    {
        $results = [
            'success' => [],
            'failed' => [],
            'summary' => [
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
                'total_size' => 0
            ]
        ];

        if (!is_array($files['name'])) {
            // 단일 파일을 배열로 변환
            $files = $this->normalizeSingleFileArray($files);
        }

        $fileCount = count($files['name']);
        $results['summary']['total'] = $fileCount;

        // 파일 개수 제한 확인
        if ($fileCount > $this->maxFilesPerUpload) {
            throw new Exception("한 번에 업로드할 수 있는 최대 파일 개수는 {$this->maxFilesPerUpload}개입니다.");
        }

        for ($i = 0; $i < $fileCount; $i++) {
            try {
                $fileInfo = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];

                $uploadResult = $this->handleSingleUpload($fileInfo);
                $results['success'][] = $uploadResult;
                $results['summary']['successful']++;
                $results['summary']['total_size'] += $uploadResult['size'];

            } catch (Exception $e) {
                $results['failed'][] = [
                    'filename' => $files['name'][$i] ?? 'unknown',
                    'error' => $e->getMessage()
                ];
                $results['summary']['failed']++;
            }
        }

        return $results;
    }

    /**
     * 단일 파일 업로드 처리
     */
    private function handleSingleUpload($fileInfo)
    {
        // 업로드 에러 확인
        if ($fileInfo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception($this->getUploadErrorMessage($fileInfo['error']));
        }

        // 파일 크기 확인
        if ($fileInfo['size'] > $this->maxFileSize) {
            throw new Exception("파일 크기가 너무 큽니다. 최대 " . $this->formatFileSize($this->maxFileSize) . "까지 업로드 가능합니다.");
        }

        // 파일 확장자 확인
        $extension = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new Exception("지원하지 않는 파일 형식입니다. ({$extension})");
        }

        // 파일명 중복 방지 처리
        $originalName = pathinfo($fileInfo['name'], PATHINFO_FILENAME);
        $safeFilename = $this->generateSafeFilename($originalName, $extension);
        $destinationPath = $this->uploadPath . $safeFilename;

        // 파일 이동
        if (!move_uploaded_file($fileInfo['tmp_name'], $destinationPath)) {
            throw new Exception("파일 업로드에 실패했습니다.");
        }

        // 파일 메타데이터 저장
        $metadata = $this->createFileMetadata($safeFilename, $fileInfo);
        $this->saveFileMetadata($metadata);

        return $metadata;
    }

    /**
     * 안전한 파일명 생성
     */
    private function generateSafeFilename($originalName, $extension)
    {
        // 파일명에서 특수문자 제거
        $safeName = preg_replace('/[^a-zA-Z0-9가-힣_-]/', '_', $originalName);
        $timestamp = date('YmdHis');
        $random = substr(md5(uniqid()), 0, 6);
        
        $filename = $safeName . '_' . $timestamp . '_' . $random . '.' . $extension;
        
        // 파일명이 이미 존재하는 경우 숫자 추가
        $counter = 1;
        $baseFilename = $filename;
        while (file_exists($this->uploadPath . $filename)) {
            $filename = pathinfo($baseFilename, PATHINFO_FILENAME) . '_' . $counter . '.' . $extension;
            $counter++;
        }

        return $filename;
    }

    /**
     * 파일 메타데이터 생성
     */
    private function createFileMetadata($filename, $fileInfo)
    {
        return [
            'id' => uniqid(),
            'filename' => $filename,
            'original_name' => $fileInfo['name'],
            'size' => $fileInfo['size'],
            'type' => $this->getFileType($filename),
            'mime_type' => $fileInfo['type'],
            'extension' => strtolower(pathinfo($filename, PATHINFO_EXTENSION)),
            'upload_date' => date('Y-m-d H:i:s'),
            'file_path' => $this->uploadPath . $filename,
            'download_count' => 0,
            'hash' => md5_file($this->uploadPath . $filename)
        ];
    }

    /**
     * 파일 타입 분류
     */
    private function getFileType($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
        $documentTypes = ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt'];
        $spreadsheetTypes = ['xls', 'xlsx', 'csv', 'ods'];
        $archiveTypes = ['zip', 'rar', '7z', 'tar', 'gz'];
        $videoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv'];
        $audioTypes = ['mp3', 'wav', 'flac'];

        if (in_array($extension, $imageTypes)) return 'image';
        if (in_array($extension, $documentTypes)) return 'document';
        if (in_array($extension, $spreadsheetTypes)) return 'spreadsheet';
        if (in_array($extension, $archiveTypes)) return 'archive';
        if (in_array($extension, $videoTypes)) return 'video';
        if (in_array($extension, $audioTypes)) return 'audio';
        
        return 'other';
    }

    /**
     * 파일 메타데이터 저장
     */
    private function saveFileMetadata($metadata)
    {
        $metadataFile = $this->uploadPath . 'files_metadata.json';
        
        $allMetadata = [];
        if (file_exists($metadataFile)) {
            $allMetadata = json_decode(file_get_contents($metadataFile), true) ?? [];
        }
        
        $allMetadata[] = $metadata;
        file_put_contents($metadataFile, json_encode($allMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * 업로드된 파일 목록 조회
     */
    public function getFileList($filters = [])
    {
        $metadataFile = $this->uploadPath . 'files_metadata.json';
        
        if (!file_exists($metadataFile)) {
            return [];
        }
        
        $allFiles = json_decode(file_get_contents($metadataFile), true) ?? [];
        
        // 실제 파일 존재 여부 확인
        $allFiles = array_filter($allFiles, function($file) {
            return file_exists($file['file_path']);
        });
        
        // 필터 적용
        if (!empty($filters)) {
            $allFiles = $this->applyFilters($allFiles, $filters);
        }
        
        return $allFiles;
    }

    /**
     * 필터 적용
     */
    private function applyFilters($files, $filters)
    {
        if (isset($filters['type']) && $filters['type'] !== 'all') {
            $files = array_filter($files, function($file) use ($filters) {
                return $file['type'] === $filters['type'];
            });
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $files = array_filter($files, function($file) use ($search) {
                return strpos(strtolower($file['original_name']), $search) !== false ||
                       strpos(strtolower($file['filename']), $search) !== false;
            });
        }
        
        if (isset($filters['sort'])) {
            $files = $this->sortFiles($files, $filters['sort']);
        }
        
        return array_values($files); // 배열 인덱스 재정렬
    }

    /**
     * 파일 정렬
     */
    private function sortFiles($files, $sortBy)
    {
        switch ($sortBy) {
            case 'name-asc':
                usort($files, function($a, $b) {
                    return strcasecmp($a['original_name'], $b['original_name']);
                });
                break;
            case 'name-desc':
                usort($files, function($a, $b) {
                    return strcasecmp($b['original_name'], $a['original_name']);
                });
                break;
            case 'size-asc':
                usort($files, function($a, $b) {
                    return $a['size'] - $b['size'];
                });
                break;
            case 'size-desc':
                usort($files, function($a, $b) {
                    return $b['size'] - $a['size'];
                });
                break;
            case 'date-asc':
                usort($files, function($a, $b) {
                    return strtotime($a['upload_date']) - strtotime($b['upload_date']);
                });
                break;
            case 'date-desc':
            default:
                usort($files, function($a, $b) {
                    return strtotime($b['upload_date']) - strtotime($a['upload_date']);
                });
                break;
        }
        
        return $files;
    }

    /**
     * 파일 다운로드 처리
     */
    public function downloadFile($fileId, $copyToDownloads = true)
    {
        $files = $this->getFileList();
        $file = null;
        
        foreach ($files as $f) {
            if ($f['id'] === $fileId) {
                $file = $f;
                break;
            }
        }
        
        if (!$file) {
            throw new Exception("파일을 찾을 수 없습니다.");
        }
        
        if (!file_exists($file['file_path'])) {
            throw new Exception("파일이 존재하지 않습니다.");
        }
        
        // downloads 폴더에 복사 (옵션)
        if ($copyToDownloads) {
            $downloadFilePath = $this->downloadPath . $file['original_name'];
            
            // 파일명 중복 방지
            $counter = 1;
            $originalDownloadPath = $downloadFilePath;
            while (file_exists($downloadFilePath)) {
                $pathInfo = pathinfo($originalDownloadPath);
                $downloadFilePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $counter . '.' . $pathInfo['extension'];
                $counter++;
            }
            
            copy($file['file_path'], $downloadFilePath);
        }
        
        // 다운로드 카운트 증가
        $this->incrementDownloadCount($fileId);
        
        return [
            'file_path' => $file['file_path'],
            'download_path' => $copyToDownloads ? $downloadFilePath : null,
            'filename' => $file['original_name'],
            'size' => $file['size'],
            'mime_type' => $file['mime_type']
        ];
    }

    /**
     * 다운로드 카운트 증가
     */
    private function incrementDownloadCount($fileId)
    {
        $metadataFile = $this->uploadPath . 'files_metadata.json';
        
        if (!file_exists($metadataFile)) {
            return;
        }
        
        $allFiles = json_decode(file_get_contents($metadataFile), true) ?? [];
        
        foreach ($allFiles as &$file) {
            if ($file['id'] === $fileId) {
                $file['download_count'] = ($file['download_count'] ?? 0) + 1;
                $file['last_download'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        file_put_contents($metadataFile, json_encode($allFiles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * 파일 삭제
     */
    public function deleteFile($fileId)
    {
        $metadataFile = $this->uploadPath . 'files_metadata.json';
        
        if (!file_exists($metadataFile)) {
            throw new Exception("파일 메타데이터를 찾을 수 없습니다.");
        }
        
        $allFiles = json_decode(file_get_contents($metadataFile), true) ?? [];
        $fileToDelete = null;
        $newFiles = [];
        
        foreach ($allFiles as $file) {
            if ($file['id'] === $fileId) {
                $fileToDelete = $file;
            } else {
                $newFiles[] = $file;
            }
        }
        
        if (!$fileToDelete) {
            throw new Exception("삭제할 파일을 찾을 수 없습니다.");
        }
        
        // 실제 파일 삭제
        if (file_exists($fileToDelete['file_path'])) {
            unlink($fileToDelete['file_path']);
        }
        
        // 메타데이터 업데이트
        file_put_contents($metadataFile, json_encode($newFiles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return $fileToDelete;
    }

    /**
     * 여러 파일 일괄 삭제
     */
    public function deleteMultipleFiles($fileIds)
    {
        $results = [
            'deleted' => [],
            'failed' => []
        ];
        
        foreach ($fileIds as $fileId) {
            try {
                $deletedFile = $this->deleteFile($fileId);
                $results['deleted'][] = $deletedFile;
            } catch (Exception $e) {
                $results['failed'][] = [
                    'file_id' => $fileId,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * 스토리지 정보 조회
     */
    public function getStorageInfo()
    {
        $files = $this->getFileList();
        $totalSize = 0;
        $typeCount = [
            'image' => 0,
            'document' => 0,
            'spreadsheet' => 0,
            'archive' => 0,
            'video' => 0,
            'audio' => 0,
            'other' => 0
        ];
        
        foreach ($files as $file) {
            $totalSize += $file['size'];
            $typeCount[$file['type']] = ($typeCount[$file['type']] ?? 0) + 1;
        }
        
        return [
            'total_files' => count($files),
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatFileSize($totalSize),
            'type_breakdown' => $typeCount,
            'upload_path' => $this->uploadPath,
            'download_path' => $this->downloadPath,
            'max_file_size' => $this->maxFileSize,
            'max_file_size_formatted' => $this->formatFileSize($this->maxFileSize),
            'allowed_extensions' => $this->allowedExtensions
        ];
    }

    /**
     * 단일 파일 배열 정규화
     */
    private function normalizeSingleFileArray($file)
    {
        return [
            'name' => [$file['name']],
            'type' => [$file['type']],
            'tmp_name' => [$file['tmp_name']],
            'error' => [$file['error']],
            'size' => [$file['size']]
        ];
    }

    /**
     * 업로드 에러 메시지 반환
     */
    private function getUploadErrorMessage($errorCode)
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => "업로드된 파일이 php.ini의 upload_max_filesize 설정값을 초과합니다.",
            UPLOAD_ERR_FORM_SIZE => "업로드된 파일이 HTML 폼의 MAX_FILE_SIZE 설정값을 초과합니다.",
            UPLOAD_ERR_PARTIAL => "파일이 부분적으로만 업로드되었습니다.",
            UPLOAD_ERR_NO_FILE => "파일이 업로드되지 않았습니다.",
            UPLOAD_ERR_NO_TMP_DIR => "임시 폴더가 없습니다.",
            UPLOAD_ERR_CANT_WRITE => "디스크에 파일을 쓸 수 없습니다.",
            UPLOAD_ERR_EXTENSION => "PHP 확장 모듈에 의해 파일 업로드가 중단되었습니다."
        ];
        
        return $messages[$errorCode] ?? "알 수 없는 업로드 오류가 발생했습니다.";
    }

    /**
     * 파일 크기 포맷팅
     */
    public function formatFileSize($bytes)
    {
        if ($bytes == 0) return '0 B';
        
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        
        return round(($bytes / pow($k, $i)), 2) . ' ' . $sizes[$i];
    }
}