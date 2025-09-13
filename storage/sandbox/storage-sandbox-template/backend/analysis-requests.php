<?php

/**
 * 분석 요청 목록 조회 및 관리 API 엔드포인트
 * GET /api/sandbox/analysis-requests - 목록 조회
 * GET /api/sandbox/analysis-requests/{id} - 개별 조회
 * DELETE /api/sandbox/analysis-requests/{id} - 개별 삭제
 */

// CORS 헤더 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN');

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // 분석 요청 매니저 로드
    require_once __DIR__ . '/../200-rfx-common/analysis-requests.php';
    
    $manager = getAnalysisRequestManager();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // URL에서 ID 추출
    $requestUri = $_SERVER['REQUEST_URI'];
    $pathInfo = parse_url($requestUri, PHP_URL_PATH);
    $pathParts = explode('/', trim($pathInfo, '/'));
    
    // analysis-requests 이후의 경로 찾기
    $analysisRequestsIndex = array_search('analysis-requests', $pathParts);
    $id = null;
    if ($analysisRequestsIndex !== false && isset($pathParts[$analysisRequestsIndex + 1])) {
        $id = $pathParts[$analysisRequestsIndex + 1];
    }
    
    switch ($method) {
        case 'GET':
            if ($id) {
                // 개별 분석 요청 조회
                $result = $manager->getAnalysisRequest($id);
                if ($result['success']) {
                    sendJsonResponse($result);
                } else {
                    sendJsonResponse($result, 404);
                }
            } else {
                // 분석 요청 목록 조회
                $filters = [
                    'status' => $_GET['status'] ?? '',
                    'search' => $_GET['search'] ?? '',
                    'sort' => $_GET['sort'] ?? 'requested_at_desc',
                    'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 50,
                    'offset' => isset($_GET['offset']) ? (int)$_GET['offset'] : 0
                ];
                
                $result = $manager->getAnalysisRequests($filters);
                sendJsonResponse($result);
            }
            break;
            
        case 'DELETE':
            if (!$id) {
                sendJsonResponse([
                    'success' => false,
                    'message' => '삭제할 요청 ID가 필요합니다.'
                ], 400);
            }
            
            $result = $manager->deleteAnalysisRequest($id);
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
    error_log("Analysis requests API error: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => '분석 요청 처리 중 오류가 발생했습니다: ' . $e->getMessage()
    ], 500);
}