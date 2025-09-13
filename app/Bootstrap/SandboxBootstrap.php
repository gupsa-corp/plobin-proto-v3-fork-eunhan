<?php
/**
 * Sandbox Simple Bootstrap File
 * 
 * Laravel autoloader만 로드하는 최소한의 부트스트랩 파일입니다.
 * 이 파일을 include하면 전역 헬퍼 함수들을 사용할 수 있습니다.
 * 
 * Usage:
 * require_once __DIR__ . '/path/to/app/Bootstrap/SandboxBootstrap.php';
 * $config = getSandboxConfig();  // 전역 함수로 바로 사용 가능
 */

// Laravel autoloader 로드 (전역 함수들도 함께 로드됨)
$autoloadPath = realpath(__DIR__ . '/../../vendor/autoload.php');
if (!$autoloadPath || !file_exists($autoloadPath)) {
    throw new Exception('Laravel autoloader not found. Please run "composer install" in the project root.');
}

require_once $autoloadPath;

// 중복 로드 방지
if (!defined('SANDBOX_BOOTSTRAP_LOADED')) {
    define('SANDBOX_BOOTSTRAP_LOADED', true);
    
    // 이제 다음과 같이 전역 함수로 바로 사용할 수 있습니다:
    // $config = getSandboxConfig();
    // $pdo = getSandboxDatabaseConnection();
    // setSandboxApiHeaders();
}