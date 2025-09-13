<?php

/**
 * PMS 요약 요청 목록 조회 및 관리 API 엔드포인트
 * GET /api/sandbox/pms-summary-requests - 목록 조회
 * GET /api/sandbox/pms-summary-requests/{id} - 개별 조회
 * DELETE /api/sandbox/pms-summary-requests/{id} - 개별 삭제
 * PUT /api/sandbox/pms-summary-requests/{id} - 개별 수정
 */

// CORS 헤더 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, DELETE, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN');

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // PMS 요약 요청 매니저 로드
    require_once __DIR__ . '/../200-rfx-common/pms-summary-requests.php';
    
    $manager = getPMSSummaryRequestManager();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // URL에서 ID 추출
    $requestUri = $_SERVER['REQUEST_URI'];
    $pathInfo = parse_url($requestUri, PHP_URL_PATH);
    $pathParts = explode('/', trim($pathInfo, '/'));
    
    // pms-summary-requests 이후의 경로 찾기
    $summaryRequestsIndex = array_search('pms-summary-requests', $pathParts);
    $id = null;
    if ($summaryRequestsIndex !== false && isset($pathParts[$summaryRequestsIndex + 1])) {
        $id = $pathParts[$summaryRequestsIndex + 1];
    }
    
    switch ($method) {
        case 'GET':
            if ($id) {
                // 개별 PMS 요약 요청 조회
                $result = $manager->getSummaryRequest($id);
                if ($result['success']) {
                    sendJsonResponse($result);
                } else {
                    sendJsonResponse($result, 404);
                }
            } else {
                // PMS 요약 요청 목록 조회
                $filters = [
                    'status' => $_GET['status'] ?? '',
                    'priority' => $_GET['priority'] ?? '',
                    'request_type' => $_GET['request_type'] ?? '',
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? 'requested_at_desc',
                    'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 50,
                    'offset' => isset($_GET['offset']) ? (int)$_GET['offset'] : 0
                ];
                
                $result = $manager->getSummaryRequests($filters);
                sendJsonResponse($result);
            }
            break;
            
        case 'PUT':
            if (!$id) {
                sendJsonResponse([
                    'success' => false,
                    'message' => '수정할 요청 ID가 필요합니다.'
                ], 400);
            }
            
            // 요청 데이터 파싱
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                sendJsonResponse([
                    'success' => false,
                    'message' => '잘못된 요청 데이터입니다.'
                ], 400);
            }
            
            // 데이터 검증
            $errors = validatePMSSummaryRequestData($data);
            if (!empty($errors)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => '데이터 검증 실패: ' . implode(', ', $errors)
                ], 400);
            }
            
            $result = $manager->updateSummaryRequest($id, $data);
            if ($result['success']) {
                sendJsonResponse($result);
            } else {
                sendJsonResponse($result, 400);
            }
            break;
            
        case 'DELETE':
            if (!$id) {
                sendJsonResponse([
                    'success' => false,
                    'message' => '삭제할 요청 ID가 필요합니다.'
                ], 400);
            }
            
            $result = $manager->deleteSummaryRequest($id);
            if ($result['success']) {
                sendJsonResponse($result);
            } else {
                sendJsonResponse($result, 404);
            }
            break;
            
        default:
            sendJsonResponse([
                'success' => false,
                'message' => 'Method not allowed'
            ], 405);
            break;
    }
    
} catch (Exception $e) {
    error_log("PMS summary requests API error: " . $e->getMessage());
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