<?php

/**
 * PMS 요약 요청 생성 API 엔드포인트
 * POST /api/sandbox/pms-summary-request
 */

// CORS 헤더 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN');

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // PMS 요약 요청 매니저 로드
    require_once __DIR__ . '/../200-rfx-common/pms-summary-requests.php';
    
    // 요청 데이터 파싱
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('잘못된 요청 데이터입니다.');
    }
    
    // 데이터 검증
    $errors = validatePMSSummaryRequestData($data);
    if (!empty($errors)) {
        sendJsonResponse([
            'success' => false,
            'message' => '데이터 검증 실패: ' . implode(', ', $errors)
        ], 400);
    }
    
    // PMS 요약 요청 생성
    $manager = getPMSSummaryRequestManager();
    $result = $manager->createSummaryRequest($data);
    
    if ($result['success']) {
        sendJsonResponse($result, 201);
    } else {
        sendJsonResponse($result, 400);
    }
    
} catch (Exception $e) {
    error_log("PMS summary request API error: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'PMS 요약 요청 처리 중 오류가 발생했습니다: ' . $e->getMessage()
    ], 500);
}

/**
 * API 응답 헬퍼 함수
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}