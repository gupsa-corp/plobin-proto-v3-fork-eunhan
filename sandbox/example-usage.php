<?php
/**
 * Sandbox Template 사용 예제
 */

// Bootstrap 로드 (autoload 포함)
require_once __DIR__ . '/bootstrap.php';

// StorageCommonService는 이미 use 되어있음

try {
    // 1. 새로운 샌드박스 프로젝트 생성
    $projectName = 'my-new-project';
    $projectPath = SANDBOX_ROOT . '/' . $projectName;
    
    echo "=== 샌드박스 템플릿 초기화 ===\n";
    $result = initializeSandboxFromTemplate($projectPath);
    echo $result . "\n\n";
    
    // 2. StorageCommonService 사용 예제
    echo "=== StorageCommonService 사용 ===\n";
    
    // 현재 화면 정보
    $screenInfo = StorageCommonService::getCurrentScreenInfo();
    echo "Current Screen: " . json_encode($screenInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // 업로드 경로
    $uploadPaths = StorageCommonService::getUploadPaths();
    echo "Upload Paths: " . json_encode($uploadPaths, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // API URL 생성
    $apiUrl = StorageCommonService::getApiUrl('test-endpoint');
    echo "API URL: {$apiUrl}\n\n";
    
    // 샌드박스 설정
    $config = StorageCommonService::getSandboxConfig();
    echo "Sandbox Config: " . json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // 디버그 정보
    echo "=== 디버그 정보 ===\n";
    StorageCommonService::debugCurrentLocation();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}