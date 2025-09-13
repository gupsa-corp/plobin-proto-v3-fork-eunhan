<?php
/**
 * 샌드박스 파일 업로드 핸들러
 * 실제 파일 업로드를 처리하고 downloads 디렉토리에 저장
 */

// 에러 보고 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS 헤더 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN');
header('Content-Type: application/json');

// OPTIONS 요청 처리 (프리플라이트)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST 요청만 허용됩니다.']);
    exit;
}

// 파일 업로드 확인
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '파일이 업로드되지 않았습니다.']);
    exit;
}

try {
    $uploadedFile = $_FILES['file'];
    $originalName = $uploadedFile['name'];
    $tmpName = $uploadedFile['tmp_name'];
    $size = $uploadedFile['size'];
    $mimeType = $uploadedFile['type'];
    
    // 파일 크기 제한 (10MB)
    $maxSize = 10 * 1024 * 1024;
    if ($size > $maxSize) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '파일 크기는 10MB를 초과할 수 없습니다.']);
        exit;
    }
    
    // 위험한 파일 확장자 검사
    $dangerousExtensions = ['php', 'exe', 'bat', 'sh', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (in_array($extension, $dangerousExtensions)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '허용되지 않는 파일 형식입니다.']);
        exit;
    }
    
    // 저장 경로 설정
    $templateRoot = dirname(__DIR__);
    $downloadsDir = $templateRoot . '/downloads';
    
    // 디렉토리 생성
    if (!is_dir($downloadsDir)) {
        mkdir($downloadsDir, 0755, true);
    }
    
    // 날짜별 서브 디렉토리
    $date = date('Y/m/d');
    $dateDir = $downloadsDir . '/' . $date;
    if (!is_dir($dateDir)) {
        mkdir($dateDir, 0755, true);
    }
    
    // 파일명 생성 (타임스탬프 + 원본명)
    $timestamp = date('Y-m-d_H-i-s');
    $filename = $timestamp . '_' . $originalName;
    $filePath = $dateDir . '/' . $filename;
    
    // 파일 이동
    if (!move_uploaded_file($tmpName, $filePath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '파일 저장에 실패했습니다.']);
        exit;
    }
    
    // 성공 응답
    $relativePath = $date . '/' . $filename;
    echo json_encode([
        'success' => true,
        'message' => '파일이 성공적으로 업로드되었습니다.',
        'data' => [
            'id' => time(), // 임시 ID
            'original_name' => $originalName,
            'stored_name' => $filename,
            'file_path' => $relativePath,
            'file_size' => $size,
            'mime_type' => $mimeType,
            'uploaded_at' => date('Y-m-d H:i:s'),
            'full_path' => $filePath
        ]
    ]);
    
} catch (Exception $e) {
    error_log('File upload error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '업로드 중 오류가 발생했습니다: ' . $e->getMessage()]);
}