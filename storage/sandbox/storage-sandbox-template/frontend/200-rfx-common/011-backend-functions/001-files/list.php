<?php
/**
 * 파일 목록 조회 함수
 * downloads 디렉토리의 파일 목록을 JSON으로 반환
 */

try {
    // 경로 설정
    $templateRoot = dirname(dirname(dirname(__FILE__)));
    $downloadsDir = $templateRoot . '/downloads';
    
    $files = [];
    
    if (is_dir($downloadsDir)) {
        // 재귀적으로 파일 검색
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($downloadsDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        $id = 1;
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($downloadsDir . '/', '', $file->getPathname());
                $mimeType = getMimeType($file->getPathname());
                
                // 현재 URL에서 downloads 경로 생성
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $requestUri = $_SERVER['REQUEST_URI'];
                
                // backend/api.php 경로에서 downloads 경로로 변경
                $downloadUrl = $protocol . '://' . $host . str_replace('/backend/api.php', '/downloads/' . $relativePath, $requestUri);
                
                $files[] = [
                    'id' => $id++,
                    'original_name' => $file->getFilename(),
                    'stored_name' => $file->getFilename(),
                    'file_path' => $relativePath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $mimeType,
                    'uploaded_at' => date('Y-m-d H:i:s', $file->getMTime()),
                    'user_id' => null,
                    'download_url' => $downloadUrl
                ];
            }
        }
    }
    
    // 최신순으로 정렬
    usort($files, function($a, $b) {
        return strtotime($b['uploaded_at']) - strtotime($a['uploaded_at']);
    });
    
    // 검색 및 필터링 처리
    $search = $_GET['search'] ?? '';
    $type = $_GET['type'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = min(100, max(1, intval($_GET['per_page'] ?? 10)));
    
    $filteredFiles = $files;
    
    // 검색 필터
    if (!empty($search)) {
        $filteredFiles = array_filter($filteredFiles, function($file) use ($search) {
            return stripos($file['original_name'], $search) !== false;
        });
    }
    
    // 타입 필터
    if (!empty($type)) {
        $filteredFiles = array_filter($filteredFiles, function($file) use ($type) {
            $category = getFileCategory($file['mime_type']);
            return $category === $type;
        });
    }
    
    // 페이지네이션
    $totalFiles = count($filteredFiles);
    $totalPages = ceil($totalFiles / $perPage);
    $offset = ($page - 1) * $perPage;
    $pagedFiles = array_slice($filteredFiles, $offset, $perPage);
    
    // 성공 응답 반환
    return [
        'success' => true,
        'data' => $pagedFiles,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $totalFiles,
            'total_pages' => $totalPages
        ]
    ];
    
} catch (Exception $e) {
    error_log('Files list error: ' . $e->getMessage());
    return ['success' => false, 'message' => '파일 목록을 가져오는 중 오류가 발생했습니다: ' . $e->getMessage()];
}

/**
 * MIME 타입 추출
 */
function getMimeType($filePath) {
    if (function_exists('mime_content_type')) {
        return mime_content_type($filePath);
    }
    
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls' => 'application/vnd.ms-excel',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'doc' => 'application/msword',
        'zip' => 'application/zip',
        'rar' => 'application/rar',
        'mp4' => 'video/mp4',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav'
    ];
    
    return $mimeTypes[$extension] ?? 'application/octet-stream';
}

/**
 * 파일 카테고리 추출
 */
function getFileCategory($mimeType) {
    if (strpos($mimeType, 'image/') === 0) return 'image';
    if (strpos($mimeType, 'video/') === 0) return 'video';
    if (strpos($mimeType, 'audio/') === 0) return 'audio';
    if ($mimeType === 'application/pdf') return 'pdf';
    if (strpos($mimeType, 'document') !== false || strpos($mimeType, 'text') !== false) return 'document';
    if (strpos($mimeType, 'zip') !== false || strpos($mimeType, 'rar') !== false) return 'archive';
    
    return 'other';
}