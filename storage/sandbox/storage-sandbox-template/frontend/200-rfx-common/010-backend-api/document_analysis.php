<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// OPTIONS 요청 처리 (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../controllers/DocumentAnalysisController.php';

try {
    $controller = new DocumentAnalysisController();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $requestUri = $_SERVER['REQUEST_URI'];
    
    // URL에서 쿼리 파라미터 제거
    $path = parse_url($requestUri, PHP_URL_PATH);
    $pathSegments = explode('/', trim($path, '/'));
    
    // API 경로 찾기 (sandbox 경로 고려)
    $apiIndex = array_search('api', $pathSegments);
    if ($apiIndex === false) {
        throw new Exception('Invalid API path');
    }
    
    // API 세그먼트 추출
    $apiSegments = array_slice($pathSegments, $apiIndex);
    
    // 라우팅
    switch ($method) {
        case 'POST':
            if ($apiSegments[1] === 'document' && $apiSegments[2] === 'analyze') {
                $input = json_decode(file_get_contents('php://input'), true);
                if (!isset($input['file_id'])) {
                    throw new Exception('file_id is required');
                }
                echo $controller->analyzeDocument($input['file_id']);
            } else {
                throw new Exception('Invalid POST endpoint');
            }
            break;
            
        case 'GET':
            if ($apiSegments[1] === 'document') {
                if ($apiSegments[2] === 'status' && isset($apiSegments[3])) {
                    echo $controller->getAnalysisStatus($apiSegments[3]);
                } elseif ($apiSegments[2] === 'assets' && isset($apiSegments[3])) {
                    echo $controller->getDocumentAssets($apiSegments[3]);
                } elseif ($apiSegments[2] === 'files') {
                    echo $controller->getAnalyzedFiles();
                } elseif ($apiSegments[2] === 'versions' && isset($apiSegments[3])) {
                    echo $controller->getVersionHistory($apiSegments[3]);
                } else {
                    throw new Exception('Invalid GET endpoint');
                }
            } else {
                throw new Exception('Invalid API path');
            }
            break;
            
        case 'PUT':
            if ($apiSegments[1] === 'document') {
                if ($apiSegments[2] === 'summary' && isset($apiSegments[3])) {
                    $input = json_decode(file_get_contents('php://input'), true);
                    echo $controller->updateAssetSummary($apiSegments[3], $input);
                } elseif ($apiSegments[2] === 'version' && isset($apiSegments[3]) && isset($apiSegments[4])) {
                    echo $controller->switchToVersion($apiSegments[3], $apiSegments[4]);
                } else {
                    throw new Exception('Invalid PUT endpoint');
                }
            } else {
                throw new Exception('Invalid API path');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'path' => $path ?? null,
            'segments' => $pathSegments ?? null
        ]
    ], JSON_UNESCAPED_UNICODE);
}