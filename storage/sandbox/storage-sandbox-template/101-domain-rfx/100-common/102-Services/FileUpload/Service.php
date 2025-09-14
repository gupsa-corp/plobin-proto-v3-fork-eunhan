<?php

namespace App\Services\FileUpload;

/**
 * RFX 도메인 파일 업로드 서비스
 */
class Service
{
    private $repository;
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB per file
    private const MAX_TOTAL_SIZE = 50 * 1024 * 1024; // 50MB total
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf', 'text/plain', 'text/csv',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    public function __construct()
    {
        $this->repository = new Repository();
    }

    /**
     * 파일 업로드 처리
     */
    public function uploadFile($file, $options = [])
    {
        try {
            // 비즈니스 로직 검증
            $this->validateFileUpload($file);

            // 보안 검증
            $this->validateFileSecurity($file);

            // 전체 파일 크기 검증
            $uploadedBy = $options['uploaded_by'] ?? 'system';
            $totalSize = $this->repository->getTotalFileSize(['uploaded_by' => $uploadedBy]);
            
            if ($totalSize + $file['size'] > self::MAX_TOTAL_SIZE) {
                throw new \Exception('총 파일 크기가 50MB를 초과합니다.');
            }

            // 파일 저장
            $fileData = $this->storeFile($file, $options);

            // Repository를 통한 데이터 저장
            $fileId = $this->repository->create($fileData);

            return [
                'id' => $fileId,
                'original_name' => $fileData['original_name'],
                'stored_name' => $fileData['stored_name'],
                'file_size' => $fileData['file_size'],
                'file_path' => $fileData['file_path'],
                'status' => 'uploaded'
            ];

        } catch (\Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            // 실패 시 임시 파일 정리
            if (isset($fileData['file_path']) && file_exists($fileData['file_path'])) {
                unlink($fileData['file_path']);
            }
            throw $e;
        }
    }

    /**
     * 업로드된 파일 목록 조회
     */
    public function getUploadedFiles($options = [])
    {
        try {
            $limit = $options['limit'] ?? 20;
            $offset = $options['offset'] ?? 0;
            $conditions = [
                'mime_type' => $options['file_type'] ?? null,
                'uploaded_by' => $options['uploaded_by'] ?? null,
                'upload_session_id' => $options['session_id'] ?? null
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
            error_log("Get uploaded files error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 정보 조회
     */
    public function getFileInfo($id)
    {
        try {
            if (empty($id)) {
                throw new \Exception('파일 ID는 필수입니다.');
            }

            $file = $this->repository->findById($id);
            
            if (!$file) {
                throw new \Exception('파일을 찾을 수 없습니다.');
            }
            
            return $this->enrichFileData($file);

        } catch (\Exception $e) {
            error_log("Get file info error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 삭제 처리
     */
    public function deleteFile($id, $permanent = false)
    {
        try {
            if (empty($id)) {
                throw new \Exception('파일 ID는 필수입니다.');
            }

            // 파일 정보 조회
            $fileInfo = $this->repository->findById($id);

            if (!$fileInfo) {
                throw new \Exception('파일을 찾을 수 없습니다.');
            }

            if ($permanent) {
                // 영구 삭제: 실제 파일과 DB 레코드 모두 삭제
                if (file_exists($fileInfo['file_path'])) {
                    unlink($fileInfo['file_path']);
                }
                $deletedRows = $this->repository->delete($id);
                $message = '파일이 영구 삭제되었습니다.';
            } else {
                // 소프트 삭제: 상태만 변경
                $deletedRows = $this->repository->updateStatus($id, 'deleted');
                $message = '파일이 삭제되었습니다.';
            }

            return [
                'success' => true,
                'message' => $message,
                'affected_rows' => $deletedRows
            ];

        } catch (\Exception $e) {
            error_log("File delete error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 업로드 통계
     */
    public function getUploadStatistics()
    {
        try {
            $typeStats = $this->repository->getFileTypeStatistics();
            $totalSize = $this->repository->getTotalFileSize();
            $totalCount = $this->repository->countFiles();
            
            return [
                'total_files' => $totalCount,
                'total_size' => $totalSize,
                'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                'by_type' => $typeStats,
                'storage_usage_percent' => round(($totalSize / self::MAX_TOTAL_SIZE) * 100, 2)
            ];

        } catch (\Exception $e) {
            error_log("Get upload statistics error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 세션별 파일 관리
     */
    public function getSessionFiles($sessionId)
    {
        try {
            if (empty($sessionId)) {
                throw new \Exception('세션 ID는 필수입니다.');
            }

            $files = $this->repository->findBySessionId($sessionId);
            
            foreach ($files as &$file) {
                $file = $this->enrichFileData($file);
            }
            
            return $files;

        } catch (\Exception $e) {
            error_log("Get session files error: " . $e->getMessage());
            throw $e;
        }
    }

    // === 비즈니스 로직 헬퍼 메서드들 ===

    /**
     * 파일 업로드 기본 검증
     */
    private function validateFileUpload($file)
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
    }

    /**
     * 파일 보안 검증
     */
    private function validateFileSecurity($file)
    {
        // MIME 타입 검증
        if (!in_array($file['type'], self::ALLOWED_MIME_TYPES)) {
            throw new \Exception('허용되지 않은 파일 형식입니다.');
        }

        // 파일 확장자 검증
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $dangerousExtensions = ['php', 'js', 'html', 'exe', 'bat', 'sh'];
        
        if (in_array($extension, $dangerousExtensions)) {
            throw new \Exception('보안상 허용되지 않은 파일 확장자입니다.');
        }

        // 파일명 보안 검증
        if (preg_match('/[<>:"|?*]/', $file['name'])) {
            throw new \Exception('파일명에 허용되지 않은 문자가 포함되어 있습니다.');
        }
    }

    /**
     * 파일 물리적 저장
     */
    private function storeFile($file, $options)
    {
        $timestamp = date('Y-m-d_H-i-s');
        $originalName = $file['name'];
        $storedName = $timestamp . '_' . uniqid() . '_' . $originalName;

        // 업로드 디렉토리 설정
        $uploadDir = __DIR__ . '/../../400-Storage/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fullPath = $uploadDir . $storedName;

        // 파일 이동
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new \Exception('파일 저장에 실패했습니다.');
        }

        return [
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'file_path' => $fullPath,
            'file_size' => $file['size'],
            'mime_type' => $file['type'],
            'uploaded_by' => $options['uploaded_by'] ?? 'system',
            'upload_session_id' => $options['session_id'] ?? null
        ];
    }

    /**
     * 파일 데이터에 추가 정보 제공
     */
    private function enrichFileData($fileData)
    {
        $fileData['file_type_display'] = $this->getFileTypeDisplay($fileData['mime_type']);
        $fileData['file_size_mb'] = round($fileData['file_size'] / 1024 / 1024, 2);
        $fileData['is_image'] = strpos($fileData['mime_type'], 'image/') === 0;
        $fileData['file_exists'] = file_exists($fileData['file_path']);
        
        return $fileData;
    }

    private function getFileTypeDisplay($mimeType)
    {
        if (strpos($mimeType, 'image/') === 0) return '이미지';
        if (strpos($mimeType, 'video/') === 0) return '비디오';
        if (strpos($mimeType, 'audio/') === 0) return '오디오';
        if ($mimeType === 'application/pdf') return 'PDF';
        if (strpos($mimeType, 'application/msword') === 0 || 
            strpos($mimeType, 'application/vnd.openxmlformats-officedocument.wordprocessingml') === 0) {
            return 'Word 문서';
        }
        if (strpos($mimeType, 'application/vnd.ms-excel') === 0 || 
            strpos($mimeType, 'application/vnd.openxmlformats-officedocument.spreadsheetml') === 0) {
            return 'Excel 문서';
        }
        if (strpos($mimeType, 'text/') === 0) return '텍스트';
        
        return '기타';
    }
}