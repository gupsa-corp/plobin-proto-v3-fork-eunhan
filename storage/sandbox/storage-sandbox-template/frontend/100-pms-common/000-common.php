<?php

/**
 * PMS 도메인 공통 설정 파일
 * 100-pms-common 스크린에서 사용하는 공통 기능들
 */

// 현재 실행중인 스크립트의 절대 경로
$currentScriptPath = $_SERVER['SCRIPT_FILENAME'] ?? __FILE__;

// PMS 도메인 루트 디렉토리 (이 파일이 있는 위치)
$pmsRoot = __DIR__;

// 템플릿 루트 디렉토리 (sandbox-template 루트)
$templateRoot = dirname(dirname($pmsRoot));

// 현재 스크립트의 상대 경로 (템플릿 루트 기준)
$relativePath = str_replace($templateRoot, '', $currentScriptPath);
$relativePath = trim($relativePath, '/\\');

// URL 경로 구성 요소 분석
$pathParts = explode('/', $relativePath);
$screenType = $pathParts[0] ?? ''; // frontend
$screenName = $pathParts[1] ?? ''; // 100-pms-common 또는 구체적인 screen

// HTTP 요청 기반 현재 URL 경로
$currentUrlPath = $_SERVER['REQUEST_URI'] ?? '';
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];

// 샌드박스 기본 경로 추출
$sandboxBasePath = '';
if (preg_match('#/sandbox/storage-sandbox-template#', $currentUrlPath)) {
    $parts = explode('/sandbox/storage-sandbox-template', $currentUrlPath);
    $sandboxBasePath = '/sandbox/storage-sandbox-template';
}

// 현재 화면의 전체 URL
$currentScreenUrl = $baseUrl . $sandboxBasePath . '/' . $screenType . '/' . $screenName;

// PMS 도메인 파일 업로드를 위한 절대 경로 정보
$uploadPaths = [
    'template_root' => $templateRoot,
    'pms_root' => $pmsRoot,
    'uploads_dir' => $pmsRoot . '/uploads',
    'temp_dir' => $pmsRoot . '/temp',
    'downloads_dir' => $pmsRoot . '/014-downloads',
    'database_dir' => $pmsRoot . '/001-database',
    'backend_php_dir' => $pmsRoot . '/005-backend-php'
];

// 업로드 디렉토리가 없으면 생성
foreach ($uploadPaths as $key => $path) {
    if (!in_array($key, ['template_root', 'pms_root']) && !is_dir($path)) {
        @mkdir($path, 0755, true);
    }
}

/**
 * 현재 PMS 화면 정보 반환
 */
function getPMSCurrentScreenInfo() {
    global $screenType, $screenName, $currentScreenUrl, $relativePath;
    
    return [
        'domain' => 'pms',
        'type' => $screenType,
        'name' => $screenName,
        'url' => $currentScreenUrl,
        'relative_path' => $relativePath,
        'full_path' => $_SERVER['SCRIPT_FILENAME'] ?? __FILE__
    ];
}

/**
 * PMS 도메인 업로드 경로 정보 반환
 */
function getPMSUploadPaths() {
    global $pmsRoot, $templateRoot;
    
    $uploadPaths = [
        'template_root' => $templateRoot,
        'pms_root' => $pmsRoot,
        'uploads_dir' => $pmsRoot . '/uploads',
        'temp_dir' => $pmsRoot . '/temp',
        'downloads_dir' => $pmsRoot . '/014-downloads',
        'database_dir' => $pmsRoot . '/001-database',
        'backend_php_dir' => $pmsRoot . '/005-backend-php'
    ];
    
    // 업로드 디렉토리가 없으면 생성
    foreach ($uploadPaths as $key => $path) {
        if (!in_array($key, ['template_root', 'pms_root']) && !is_dir($path)) {
            @mkdir($path, 0755, true);
        }
    }
    
    return $uploadPaths;
}

/**
 * PMS API 엔드포인트 URL 생성
 */
function getPMSApiUrl($endpoint = '') {
    global $baseUrl, $sandboxBasePath;
    
    return $baseUrl . $sandboxBasePath . '/frontend/100-pms-common/005-backend-php/' . ltrim($endpoint, '/');
}

/**
 * PMS 화면으로의 URL 생성
 */
function getPMSScreenUrl($screenName) {
    global $baseUrl, $sandboxBasePath;
    
    return $baseUrl . $sandboxBasePath . '/frontend/' . $screenName;
}

/**
 * PMS downloads 디렉토리의 파일 목록 반환
 */
function getPMSLocalFilesList() {
    try {
        $uploadPaths = getPMSUploadPaths();
        $downloadsDir = $uploadPaths['downloads_dir'] ?? '';
        $files = [];
        
        // 디렉토리가 존재하지 않거나 빈 경우 빈 배열 반환
        if (empty($downloadsDir) || !is_dir($downloadsDir)) {
            return $files;
        }
        
        // 디렉토리 반복자 생성 시 오류 처리
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($downloadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
        } catch (Exception $e) {
            error_log("PMS Directory iterator error: " . $e->getMessage());
            return $files;
        }
        
        $id = 1;
        foreach ($iterator as $file) {
            try {
                if ($file && $file->isFile()) {
                    $filePath = $file->getPathname();
                    $relativePath = str_replace($downloadsDir . '/', '', $filePath);
                    $mimeType = getPMSMimeType($filePath);
                    
                    $files[] = [
                        'id' => $id++,
                        'original_name' => $file->getFilename(),
                        'stored_name' => $file->getFilename(),
                        'file_path' => $relativePath,
                        'file_size' => $file->getSize(),
                        'mime_type' => $mimeType,
                        'uploaded_at' => date('Y-m-d H:i:s', $file->getMTime()),
                        'user_id' => null,
                        'download_url' => getPMSDownloadUrl($relativePath)
                    ];
                }
            } catch (Exception $e) {
                // 개별 파일 처리 오류는 건너뛰기
                error_log("PMS File processing error: " . $e->getMessage());
                continue;
            }
        }
        
        // 최신순으로 정렬
        if (!empty($files)) {
            usort($files, function($a, $b) {
                return strtotime($b['uploaded_at'] ?? '1970-01-01') - strtotime($a['uploaded_at'] ?? '1970-01-01');
            });
        }
        
        return $files;
    } catch (Exception $e) {
        error_log("getPMSLocalFilesList error: " . $e->getMessage());
        return [];
    }
}

/**
 * MIME 타입 추출 (PMS용)
 */
function getPMSMimeType($filePath) {
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
        'sqlite' => 'application/x-sqlite3'
    ];
    
    return $mimeTypes[$extension] ?? 'application/octet-stream';
}

/**
 * PMS 다운로드 URL 생성
 */
function getPMSDownloadUrl($relativePath) {
    global $baseUrl, $sandboxBasePath;
    
    return $baseUrl . $sandboxBasePath . '/frontend/100-pms-common/014-downloads/' . $relativePath;
}

/**
 * PMS 도메인 화면 정보에 대한 디버그 정보 출력
 */
function debugPMSCurrentLocation() {
    $info = getPMSCurrentScreenInfo();
    $paths = getPMSUploadPaths();
    
    echo "<div style='background: #e8f4f8; padding: 10px; margin: 10px 0; border: 1px solid #4CAF50;'>";
    echo "<h4>PMS 도메인 위치 정보</h4>";
    echo "<ul>";
    echo "<li><strong>도메인:</strong> {$info['domain']}</li>";
    echo "<li><strong>화면 타입:</strong> {$info['type']}</li>";
    echo "<li><strong>화면 이름:</strong> {$info['name']}</li>";
    echo "<li><strong>현재 URL:</strong> {$info['url']}</li>";
    echo "<li><strong>상대 경로:</strong> {$info['relative_path']}</li>";
    echo "<li><strong>절대 경로:</strong> {$info['full_path']}</li>";
    echo "</ul>";
    
    echo "<h4>PMS 업로드 경로</h4>";
    echo "<ul>";
    foreach ($paths as $key => $path) {
        echo "<li><strong>{$key}:</strong> {$path}</li>";
    }
    echo "</ul>";
    echo "</div>";
}