<?php
/**
 * Sandbox Bootstrap File
 * Laravel autoloader를 로드하여 App\Services\StorageCommonService를 사용할 수 있게 합니다.
 */

// Laravel autoloader 로드 (한 번만 실행)
if (!class_exists('Illuminate\Support\Facades\Facade')) {
    require_once dirname(__DIR__) . '/plobin-proto-v3/vendor/autoload.php';
}

// Laravel 환경 설정 (필요한 경우)
if (!function_exists('app_path')) {
    // Laravel 부트스트랩
    $app = require_once dirname(__DIR__) . '/plobin-proto-v3/bootstrap/app.php';
}

// StorageCommonService 사용 준비
use App\Services\StorageCommonService;

// 샌드박스 환경 설정
define('SANDBOX_ROOT', __DIR__);
define('TEMPLATE_PATH', SANDBOX_ROOT . '/container/' . config('sandbox-routing.default_template'));

// 템플릿 복사 및 초기화 함수
function initializeSandboxFromTemplate($targetPath, $templatePath = null) {
    $templatePath = $templatePath ?: TEMPLATE_PATH;
    
    if (!is_dir($templatePath)) {
        throw new Exception("Template path not found: {$templatePath}");
    }
    
    if (!is_dir($targetPath)) {
        mkdir($targetPath, 0755, true);
        copyDirectory($templatePath, $targetPath);
        return "Sandbox initialized from template at: {$targetPath}";
    }
    
    return "Target directory already exists: {$targetPath}";
}

// 디렉토리 복사 함수
function copyDirectory($src, $dest) {
    if (!is_dir($src)) return false;
    
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    
    $files = scandir($src);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $srcPath = $src . '/' . $file;
        $destPath = $dest . '/' . $file;
        
        if (is_dir($srcPath)) {
            copyDirectory($srcPath, $destPath);
        } else {
            copy($srcPath, $destPath);
        }
    }
    
    return true;
}

// 이제 어디서든 App\Services\StorageCommonService를 사용할 수 있습니다.