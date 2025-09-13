<?php
/**
 * 샌드박스 파일 목록 핸들러
 * downloads 디렉토리의 파일 목록을 JSON으로 반환
 */

// 에러 보고 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS 헤더 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN');
header('Content-Type: application/json');

// OPTIONS 요청 처리 (프리플라이트)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // functions/files/list.php 파일 포함
    require_once __DIR__ . '/functions/files/list.php';
    
} catch (Exception $e) {
    error_log('Files list error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '파일 목록을 가져오는 중 오류가 발생했습니다: ' . $e->getMessage()]);
}