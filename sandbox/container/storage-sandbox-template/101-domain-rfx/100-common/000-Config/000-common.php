<?php

/**
 * RFX 도메인 공통 설정 파일
 * 200-rfx-common 스크린에서 사용하는 공통 기능들
 */

// 현재 실행중인 스크립트의 절대 경로
$currentScriptPath = $_SERVER['SCRIPT_FILENAME'] ?? __FILE__;

// RFX 도메인 루트 디렉토리 (이 파일이 있는 위치)
$rfxRoot = __DIR__;

// 템플릿 루트 디렉토리 (sandbox-template 루트)
$templateRoot = dirname(dirname($rfxRoot));

// 현재 스크립트의 상대 경로 (템플릿 루트 기준)
$relativePath = str_replace($templateRoot, '', $currentScriptPath);
$relativePath = trim($relativePath, '/\\');

// URL 경로 구성 요소 분석
$pathParts = explode('/', $relativePath);
$screenType = $pathParts[0] ?? ''; // frontend
$screenName = $pathParts[1] ?? ''; // 200-rfx-common 또는 구체적인 screen

// HTTP 요청 기반 현재 URL 경로
$currentUrlPath = $_SERVER['REQUEST_URI'] ?? '';
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];

// 샌드박스 기본 경로 추출
$sandboxBasePath = '';
if (preg_match('#/sandbox/([^/]+)#', $currentUrlPath, $matches)) {
    $currentSandbox = $matches[1];
    $parts = explode("/sandbox/{$currentSandbox}", $currentUrlPath);
    $sandboxBasePath = "/sandbox/{$currentSandbox}";
}

// 현재 화면의 전체 URL
$currentScreenUrl = $baseUrl . $sandboxBasePath . '/' . $screenType . '/' . $screenName;

// RFX 도메인 파일 업로드를 위한 절대 경로 정보
$uploadPaths = [
    'template_root' => $templateRoot,
    'rfx_root' => $rfxRoot,
    'uploads_dir' => $rfxRoot . '/100-common/400-Storage/uploads',
    'temp_dir' => $rfxRoot . '/100-common/400-Storage/temp',
    'downloads_dir' => $rfxRoot . '/100-common/400-Storage/downloads',
    'database_dir' => $rfxRoot . '/100-common/200-Database',
    'backend_php_dir' => $rfxRoot . '/100-common/102-Services'
];

// 업로드 디렉토리가 없으면 생성
foreach ($uploadPaths as $key => $path) {
    if (!in_array($key, ['template_root', 'rfx_root']) && !is_dir($path)) {
        @mkdir($path, 0755, true);
    }
}

/**
 * 현재 RFX 화면 정보 반환
 */
function getRFXCurrentScreenInfo() {
    global $screenType, $screenName, $currentScreenUrl, $relativePath;
    
    return [
        'domain' => 'rfx',
        'type' => $screenType,
        'name' => $screenName,
        'url' => $currentScreenUrl,
        'relative_path' => $relativePath,
        'full_path' => $_SERVER['SCRIPT_FILENAME'] ?? __FILE__
    ];
}

/**
 * RFX 도메인 업로드 경로 정보 반환
 */
function getRFXUploadPaths() {
    global $rfxRoot, $templateRoot;
    
    $uploadPaths = [
        'template_root' => $templateRoot,
        'rfx_root' => $rfxRoot,
        'uploads_dir' => $rfxRoot . '/100-common/400-Storage/uploads',
        'temp_dir' => $rfxRoot . '/100-common/400-Storage/temp',
        'downloads_dir' => $rfxRoot . '/100-common/400-Storage/downloads',
        'database_dir' => $rfxRoot . '/100-common/200-Database',
        'backend_php_dir' => $rfxRoot . '/100-common/102-Services'
    ];
    
    // 업로드 디렉토리가 없으면 생성
    foreach ($uploadPaths as $key => $path) {
        if (!in_array($key, ['template_root', 'rfx_root']) && !is_dir($path)) {
            @mkdir($path, 0755, true);
        }
    }
    
    return $uploadPaths;
}

/**
 * RFX API 엔드포인트 URL 생성
 */
function getRFXApiUrl($endpoint = '') {
    global $baseUrl, $sandboxBasePath;
    
    return $baseUrl . $sandboxBasePath . '/101-domain-rfx/100-common/102-Services/' . ltrim($endpoint, '/');
}

/**
 * RFX 화면으로의 URL 생성
 */
function getRFXScreenUrl($screenName) {
    global $baseUrl, $sandboxBasePath;
    
    return $baseUrl . $sandboxBasePath . '/101-domain-rfx/' . $screenName;
}

/**
 * RFX downloads 디렉토리의 파일 목록 반환
 */
function getRFXLocalFilesList() {
    try {
        $uploadPaths = getRFXUploadPaths();
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
            error_log("RFX Directory iterator error: " . $e->getMessage());
            return $files;
        }
        
        $id = 1;
        foreach ($iterator as $file) {
            try {
                if ($file && $file->isFile()) {
                    $filePath = $file->getPathname();
                    $relativePath = str_replace($downloadsDir . '/', '', $filePath);
                    $mimeType = getRFXMimeType($filePath);
                    
                    $files[] = [
                        'id' => $id++,
                        'original_name' => $file->getFilename(),
                        'stored_name' => $file->getFilename(),
                        'file_path' => $relativePath,
                        'file_size' => $file->getSize(),
                        'mime_type' => $mimeType,
                        'uploaded_at' => date('Y-m-d H:i:s', $file->getMTime()),
                        'user_id' => null,
                        'download_url' => getRFXDownloadUrl($relativePath)
                    ];
                }
            } catch (Exception $e) {
                // 개별 파일 처리 오류는 건너뛰기
                error_log("RFX File processing error: " . $e->getMessage());
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
        error_log("getRFXLocalFilesList error: " . $e->getMessage());
        return [];
    }
}

/**
 * MIME 타입 추출 (RFX용)
 */
function getRFXMimeType($filePath) {
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
 * RFX 다운로드 URL 생성
 */
function getRFXDownloadUrl($relativePath) {
    global $baseUrl, $sandboxBasePath;
    
    return $baseUrl . $sandboxBasePath . '/101-domain-rfx/100-common/400-Storage/downloads/' . $relativePath;
}

/**
 * RFX 도메인 화면 정보에 대한 디버그 정보 출력
 */
function debugRFXCurrentLocation() {
    $info = getRFXCurrentScreenInfo();
    $paths = getRFXUploadPaths();
    
    echo "<div style='background: #f8e8e8; padding: 10px; margin: 10px 0; border: 1px solid #FF5722;'>";
    echo "<h4>RFX 도메인 위치 정보</h4>";
    echo "<ul>";
    echo "<li><strong>도메인:</strong> {$info['domain']}</li>";
    echo "<li><strong>화면 타입:</strong> {$info['type']}</li>";
    echo "<li><strong>화면 이름:</strong> {$info['name']}</li>";
    echo "<li><strong>현재 URL:</strong> {$info['url']}</li>";
    echo "<li><strong>상대 경로:</strong> {$info['relative_path']}</li>";
    echo "<li><strong>절대 경로:</strong> {$info['full_path']}</li>";
    echo "</ul>";
    
    echo "<h4>RFX 업로드 경로</h4>";
    echo "<ul>";
    foreach ($paths as $key => $path) {
        echo "<li><strong>{$key}:</strong> {$path}</li>";
    }
    echo "</ul>";
    echo "</div>";
}