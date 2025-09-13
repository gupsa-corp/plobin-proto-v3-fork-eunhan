<?php

/**
 * 샌드박스 템플릿 공통 설정 파일
 * 각 화면에서 현재 위치와 경로 정보를 제공
 */

// 현재 실행중인 스크립트의 절대 경로
$currentScriptPath = $_SERVER['SCRIPT_FILENAME'] ?? __FILE__;

// 템플릿 루트 디렉토리 (이 파일이 있는 위치)
$templateRoot = dirname(__DIR__) . '/sandbox/storage-sandbox-template';

// 현재 스크립트의 상대 경로 (템플릿 루트 기준)
$relativePath = str_replace($templateRoot, '', $currentScriptPath);
$relativePath = trim($relativePath, '/\\');

// URL 경로 구성 요소 분석
$pathParts = explode('/', $relativePath);
$screenType = $pathParts[0] ?? ''; // frontend/backend
$screenName = $pathParts[1] ?? ''; // 화면명

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

// 파일 업로드를 위한 절대 경로 정보
$uploadPaths = [
    'template_root' => $templateRoot,
    'uploads_dir' => $templateRoot . '/uploads',
    'temp_dir' => $templateRoot . '/temp',
    'downloads_dir' => $templateRoot . '/downloads'
];

// 업로드 디렉토리가 없으면 생성
foreach ($uploadPaths as $key => $path) {
    if ($key !== 'template_root' && !is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

/**
 * 현재 화면 정보 반환
 */
function getCurrentScreenInfo() {
    global $screenType, $screenName, $currentScreenUrl, $relativePath;
    
    return [
        'type' => $screenType,
        'name' => $screenName,
        'url' => $currentScreenUrl,
        'relative_path' => $relativePath,
        'full_path' => $_SERVER['SCRIPT_FILENAME'] ?? __FILE__
    ];
}

/**
 * 업로드 경로 정보 반환
 */
function getUploadPaths() {
    $templateRoot = dirname(__DIR__) . '/sandbox/storage-sandbox-template';
    
    $uploadPaths = [
        'template_root' => $templateRoot,
        'uploads_dir' => $templateRoot . '/uploads',
        'temp_dir' => $templateRoot . '/temp',
        'downloads_dir' => $templateRoot . '/downloads'
    ];
    
    // 업로드 디렉토리가 없으면 생성
    foreach ($uploadPaths as $key => $path) {
        if ($key !== 'template_root' && !is_dir($path)) {
            @mkdir($path, 0755, true);
        }
    }
    
    return $uploadPaths;
}

/**
 * API 엔드포인트 URL 생성
 */
function getApiUrl($endpoint = '') {
    // 현재 요청에서 URL 정보 추출
    $currentUrlPath = $_SERVER['REQUEST_URI'] ?? '';
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    
    $sandboxBasePath = '';
    if (preg_match('#/sandbox/storage-sandbox-template#', $currentUrlPath)) {
        $sandboxBasePath = '/sandbox/storage-sandbox-template';
    }
    
    return $baseUrl . $sandboxBasePath . '/backend/' . ltrim($endpoint, '/');
}

/**
 * 다른 화면으로의 URL 생성
 */
function getScreenUrl($screenType, $screenName) {
    // 현재 요청에서 URL 정보 추출
    $currentUrlPath = $_SERVER['REQUEST_URI'] ?? '';
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    
    $sandboxBasePath = '';
    if (preg_match('#/sandbox/storage-sandbox-template#', $currentUrlPath)) {
        $sandboxBasePath = '/sandbox/storage-sandbox-template';
    }
    
    return $baseUrl . $sandboxBasePath . '/' . $screenType . '/' . $screenName;
}

/**
 * downloads 디렉토리의 파일 목록 반환
 */
function getLocalFilesList() {
    try {
        $uploadPaths = getUploadPaths();
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
            error_log("Directory iterator error: " . $e->getMessage());
            return $files;
        }
        
        $id = 1;
        foreach ($iterator as $file) {
            try {
                if ($file && $file->isFile()) {
                    $filePath = $file->getPathname();
                    $relativePath = str_replace($downloadsDir . '/', '', $filePath);
                    $mimeType = getMimeType($filePath);
                    
                    $files[] = [
                        'id' => $id++,
                        'original_name' => $file->getFilename(),
                        'stored_name' => $file->getFilename(),
                        'file_path' => $relativePath,
                        'file_size' => $file->getSize(),
                        'mime_type' => $mimeType,
                        'uploaded_at' => date('Y-m-d H:i:s', $file->getMTime()),
                        'user_id' => null,
                        'download_url' => getDownloadUrl($relativePath)
                    ];
                }
            } catch (Exception $e) {
                // 개별 파일 처리 오류는 건너뛰기
                error_log("File processing error: " . $e->getMessage());
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
        error_log("getLocalFilesList error: " . $e->getMessage());
        return [];
    }
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
 * 다운로드 URL 생성
 */
function getDownloadUrl($relativePath) {
    // 현재 요청에서 URL 정보 추출
    $currentUrlPath = $_SERVER['REQUEST_URI'] ?? '';
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    
    $sandboxBasePath = '';
    if (preg_match('#/sandbox/storage-sandbox-template#', $currentUrlPath)) {
        $sandboxBasePath = '/sandbox/storage-sandbox-template';
    }
    
    return $baseUrl . $sandboxBasePath . '/downloads/' . $relativePath;
}

/**
 * 동적으로 사용 가능한 화면 목록을 스캔
 */
function getAvailableScreens() {
    $templateRoot = dirname(__DIR__) . '/sandbox/storage-sandbox-template';
    $frontendDir = $templateRoot . '/frontend';
    $screens = [];
    
    try {
        if (!is_dir($frontendDir)) {
            return $screens;
        }
        
        // frontend 디렉토리의 모든 하위 디렉토리 스캔
        $directories = glob($frontendDir . '/*', GLOB_ONLYDIR);
        
        foreach ($directories as $dir) {
            $screenName = basename($dir);
            
            // 숫자로 시작하는 screen 디렉토리만 포함 (예: 001-screen-dashboard)
            // 또는 100-pms-common 같은 공통 폴더도 포함
            if (preg_match('/^\d{3}-/', $screenName)) {
                // 실제 content 파일이 있는지 확인
                $contentFile = $dir . '/000-content.blade.php';
                if (file_exists($contentFile)) {
                    // 화면 제목 추출 시도 (blade 파일에서 댓글 추출)
                    $title = getScreenTitle($contentFile, $screenName);
                    
                    $screens[] = [
                        'value' => $screenName,
                        'title' => $title,
                        'path' => $dir,
                        'url' => "/sandbox/storage-sandbox-template/{$screenName}"
                    ];
                }
            }
        }
        
        // 화면 이름으로 정렬
        usort($screens, function($a, $b) {
            return strcmp($a['value'], $b['value']);
        });
        
    } catch (Exception $e) {
        error_log("getAvailableScreens error: " . $e->getMessage());
    }
    
    return $screens;
}

/**
 * blade 파일에서 화면 제목 추출
 */
function getScreenTitle($filePath, $defaultName) {
    try {
        $content = file_get_contents($filePath);
        
        // 첫 번째 줄의 댓글에서 제목 추출 시도
        if (preg_match('/^{{--\s*(.+?)\s*--}}/m', $content, $matches)) {
            $title = trim($matches[1]);
            // "템플릿", "화면" 같은 단어 제거하고 정리
            $title = preg_replace('/(샌드박스|템플릿|화면)\s*/u', '', $title);
            if (!empty($title)) {
                return $title;
            }
        }
        
        // h1 태그에서 제목 추출 시도
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/s', $content, $matches)) {
            $title = strip_tags($matches[1]);
            $title = trim($title);
            if (!empty($title)) {
                return $title;
            }
        }
        
        // Alpine.js 데이터에서 제목 추출 시도 (x-text 등)
        if (preg_match('/text-2xl[^>]*>([^<]+)</s', $content, $matches)) {
            $title = trim($matches[1]);
            if (!empty($title)) {
                return $title;
            }
        }
        
    } catch (Exception $e) {
        error_log("getScreenTitle error: " . $e->getMessage());
    }
    
    // 기본 이름으로 폴백 (숫자 제거하고 하이픈을 공백으로)
    $cleanName = preg_replace('/^\d{3}-/', '', $defaultName);
    $cleanName = str_replace(['-', '_'], ' ', $cleanName);
    $cleanName = ucwords($cleanName);
    
    return $cleanName;
}

/**
 * 현재 화면에 대한 디버그 정보 출력
 */
function debugCurrentLocation() {
    $info = getCurrentScreenInfo();
    $paths = getUploadPaths();
    
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;'>";
    echo "<h4>현재 위치 정보</h4>";
    echo "<ul>";
    echo "<li><strong>화면 타입:</strong> {$info['type']}</li>";
    echo "<li><strong>화면 이름:</strong> {$info['name']}</li>";
    echo "<li><strong>현재 URL:</strong> {$info['url']}</li>";
    echo "<li><strong>상대 경로:</strong> {$info['relative_path']}</li>";
    echo "<li><strong>절대 경로:</strong> {$info['full_path']}</li>";
    echo "</ul>";
    
    echo "<h4>업로드 경로</h4>";
    echo "<ul>";
    foreach ($paths as $key => $path) {
        echo "<li><strong>{$key}:</strong> {$path}</li>";
    }
    echo "</ul>";
    echo "</div>";
}