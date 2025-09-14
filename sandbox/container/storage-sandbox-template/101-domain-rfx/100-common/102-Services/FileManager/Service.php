<?php

namespace App\Services\FileManager;

/**
 * RFX 도메인 파일 관리 서비스
 * (기존 FileManagerService.php 내용 통합)
 */
class Service
{
    private $repository;
    private const UPLOAD_DISK = 'sandbox_downloads';
    private const DOWNLOAD_DISK = 'sandbox_downloads';
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB per file
    private const MAX_TOTAL_SIZE = 50 * 1024 * 1024; // 50MB total
    private const ALLOWED_CATEGORIES = [
        'document', 'image', 'video', 'audio', 'archive', 'general'
    ];

    public function __construct()
    {
        $this->repository = new Repository();
    }

    /**
     * 파일 저장 (기존 FileManagerService 로직)
     */
    public function storeFile($file, $options = []): array
    {
        try {
            // 비즈니스 로직 검증
            $this->validateFileStorage($file, $options);

            // 파일 카테고리 결정
            $category = $this->determineFileCategory($file['type'], $options['category'] ?? null);

            // 전체 파일 크기 검증
            $managedBy = $options['managed_by'] ?? 'system';
            $totalSize = $this->repository->getTotalFileSize(['managed_by' => $managedBy]);
            
            if ($totalSize + $file['size'] > self::MAX_TOTAL_SIZE) {
                throw new \Exception('총 파일 크기가 50MB를 초과합니다.');
            }

            // 파일 물리적 저장
            $fileData = $this->saveFileToStorage($file, $options);

            // Repository를 통한 데이터 저장
            $fileId = $this->repository->create([
                'original_name' => $fileData['original_name'],
                'stored_name' => $fileData['stored_name'],
                'file_path' => $fileData['file_path'],
                'download_path' => $fileData['download_path'],
                'file_size' => $fileData['file_size'],
                'mime_type' => $fileData['mime_type'],
                'file_category' => $category,
                'managed_by' => $managedBy
            ]);

            return [
                'id' => $fileId,
                'original_name' => $fileData['original_name'],
                'stored_name' => $fileData['stored_name'],
                'file_path' => $fileData['file_path'],
                'file_size' => $fileData['file_size'],
                'file_category' => $category,
                'download_url' => $this->generateDownloadUrl($fileData['stored_name'])
            ];

        } catch (\Exception $e) {
            error_log("File storage error: " . $e->getMessage());
            // 실패 시 파일 정리
            if (isset($fileData['file_path']) && file_exists($fileData['file_path'])) {
                unlink($fileData['file_path']);
            }
            throw $e;
        }
    }

    /**
     * 파일 목록 조회
     */
    public function getFileList($options = [])
    {
        try {
            $limit = $options['limit'] ?? 20;
            $offset = $options['offset'] ?? 0;
            $conditions = [
                'file_category' => $options['category'] ?? null,
                'mime_type' => $options['file_type'] ?? null,
                'managed_by' => $options['managed_by'] ?? null,
                'search' => $options['search'] ?? null
            ];
            
            // 빈 조건 제거
            $conditions = array_filter($conditions);
            
            $files = $this->repository->getFiles($conditions, $limit, $offset);
            
            // 비즈니스 로직: 각 파일에 추가 정보 제공
            foreach ($files as &$file) {
                $file = $this->enrichFileData($file);
            }
            
            return $files;

        } catch (\Exception $e) {
            error_log("Get file list error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 다운로드 처리
     */
    public function downloadFile($identifier, $byStoredName = false)
    {
        try {
            // 파일 정보 조회
            if ($byStoredName) {
                $file = $this->repository->findByStoredName($identifier);
            } else {
                $file = $this->repository->findById($identifier);
            }

            if (!$file) {
                throw new \Exception('파일을 찾을 수 없습니다.');
            }

            if (!file_exists($file['file_path'])) {
                throw new \Exception('파일이 물리적으로 존재하지 않습니다.');
            }

            // 접근 기록 업데이트
            $this->repository->updateAccess($file['id']);

            return [
                'file_info' => $this->enrichFileData($file),
                'file_path' => $file['file_path'],
                'download_name' => $file['original_name']
            ];

        } catch (\Exception $e) {
            error_log("Download file error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 삭제
     */
    public function deleteFile($fileId, $permanent = false)
    {
        try {
            if (empty($fileId)) {
                throw new \Exception('파일 ID는 필수입니다.');
            }

            // 파일 정보 조회
            $fileInfo = $this->repository->findById($fileId);

            if (!$fileInfo) {
                throw new \Exception('파일을 찾을 수 없습니다.');
            }

            if ($permanent) {
                // 영구 삭제: 실제 파일과 DB 레코드 모두 삭제
                if (file_exists($fileInfo['file_path'])) {
                    unlink($fileInfo['file_path']);
                }
                $deletedRows = $this->repository->delete($fileId);
                $message = '파일이 영구 삭제되었습니다.';
            } else {
                // 소프트 삭제: 상태만 변경
                $deletedRows = $this->repository->updateStatus($fileId, 'deleted');
                $message = '파일이 삭제되었습니다.';
            }

            return [
                'success' => true, 
                'message' => $message,
                'affected_rows' => $deletedRows
            ];

        } catch (\Exception $e) {
            error_log("Delete file error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 관리 통계
     */
    public function getManagementStatistics()
    {
        try {
            $categoryStats = $this->repository->getCategoryStatistics();
            $totalSize = $this->repository->getTotalFileSize();
            $totalCount = $this->repository->countFiles();
            
            return [
                'total_files' => $totalCount,
                'total_size' => $totalSize,
                'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                'by_category' => $categoryStats,
                'storage_usage_percent' => round(($totalSize / self::MAX_TOTAL_SIZE) * 100, 2),
                'popular_files' => $this->repository->getPopularFiles(5),
                'recent_files' => $this->repository->getRecentlyAccessedFiles(5)
            ];

        } catch (\Exception $e) {
            error_log("Get management statistics error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 인기 파일 조회
     */
    public function getPopularFiles($limit = 10)
    {
        try {
            $files = $this->repository->getPopularFiles($limit);
            
            foreach ($files as &$file) {
                $file = $this->enrichFileData($file);
            }
            
            return $files;

        } catch (\Exception $e) {
            error_log("Get popular files error: " . $e->getMessage());
            throw $e;
        }
    }

    // === 비즈니스 로직 헬퍼 메서드들 ===

    /**
     * 파일 저장 검증
     */
    private function validateFileStorage($file, $options)
    {
        if (empty($file) || !is_array($file)) {
            throw new \Exception('유효하지 않은 파일입니다.');
        }

        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('업로드된 파일이 아닙니다.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('파일 업로드 중 오류가 발생했습니다.');
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            throw new \Exception('파일 크기가 너무 큽니다. 최대 10MB까지 허용됩니다.');
        }

        if (empty($file['name'])) {
            throw new \Exception('파일명이 필요합니다.');
        }

        // 카테고리 검증
        if (isset($options['category']) && !in_array($options['category'], self::ALLOWED_CATEGORIES)) {
            throw new \Exception('허용되지 않은 파일 카테고리입니다.');
        }
    }

    /**
     * 파일 카테고리 결정
     */
    private function determineFileCategory($mimeType, $suggestedCategory = null)
    {
        // 사용자 제안 카테고리가 유효하면 사용
        if ($suggestedCategory && in_array($suggestedCategory, self::ALLOWED_CATEGORIES)) {
            return $suggestedCategory;
        }

        // MIME 타입 기반 자동 결정
        if (strpos($mimeType, 'image/') === 0) return 'image';
        if (strpos($mimeType, 'video/') === 0) return 'video';
        if (strpos($mimeType, 'audio/') === 0) return 'audio';
        if (in_array($mimeType, ['application/zip', 'application/x-rar', 'application/x-tar'])) return 'archive';
        if (strpos($mimeType, 'application/') === 0 || strpos($mimeType, 'text/') === 0) return 'document';
        
        return 'general';
    }

    /**
     * 파일 물리적 저장
     */
    private function saveFileToStorage($file, $options)
    {
        $timestamp = date('Y-m-d_H-i-s');
        $originalName = $file['name'];
        $storedName = $timestamp . '_' . uniqid() . '_' . $originalName;

        // downloads 폴더에 직접 저장
        $uploadDir = __DIR__ . '/../../400-Storage/downloads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fullPath = $uploadDir . $storedName;
        $downloadPath = '/downloads/' . $storedName;

        // 파일 이동
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new \Exception('파일 저장에 실패했습니다.');
        }

        return [
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'file_path' => $fullPath,
            'download_path' => $downloadPath,
            'file_size' => $file['size'],
            'mime_type' => $file['type'] ?? 'application/octet-stream'
        ];
    }

    /**
     * 파일 데이터에 추가 정보 제공
     */
    private function enrichFileData($fileData)
    {
        $fileData['category_display'] = $this->getCategoryDisplay($fileData['file_category']);
        $fileData['file_size_mb'] = round($fileData['file_size'] / 1024 / 1024, 2);
        $fileData['is_accessible'] = file_exists($fileData['file_path']);
        $fileData['download_url'] = $this->generateDownloadUrl($fileData['stored_name']);
        $fileData['last_accessed_display'] = $fileData['last_accessed_at'] ? 
            date('Y-m-d H:i', strtotime($fileData['last_accessed_at'])) : '접근 기록 없음';
        
        return $fileData;
    }

    /**
     * 다운로드 URL 생성
     */
    private function generateDownloadUrl($storedName)
    {
        // 실제 환경에서는 적절한 다운로드 URL 생성
        return '/downloads/' . $storedName;
    }

    private function getCategoryDisplay($category)
    {
        $categoryMap = [
            'document' => '문서',
            'image' => '이미지',
            'video' => '비디오',
            'audio' => '오디오',
            'archive' => '압축파일',
            'general' => '일반'
        ];
        
        return $categoryMap[$category] ?? '기타';
    }
}