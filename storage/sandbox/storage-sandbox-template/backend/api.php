<?php
/**
 * 샌드박스 템플릿 API 엔드포인트
 * 독립적인 PHP API 시스템
 */

// 에러 보고 설정
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS 헤더 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN, Authorization');
header('Content-Type: application/json; charset=utf-8');

// OPTIONS 요청 처리 (프리플라이트)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 공통 설정 로드
require_once __DIR__ . '/../common.php';

try {
    // URL 파싱
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // API 경로 추출 (backend/api.php 이후의 경로)
    $apiPath = '';
    if (preg_match('#/backend/api\.php(/.*)?$#', $requestUri, $matches)) {
        $apiPath = $matches[1] ?? '';
    }
    
    // 경로를 / 로 분할
    $pathSegments = array_filter(explode('/', trim($apiPath, '/')));
    
    // 라우팅
    $response = routeRequest($method, $pathSegments);
    
    if ($response === null) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'API 엔드포인트를 찾을 수 없습니다.']);
    } else {
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '서버 오류가 발생했습니다: ' . $e->getMessage()]);
}

/**
 * 요청을 적절한 함수로 라우팅
 */
function routeRequest($method, $pathSegments) {
    // 빈 경로인 경우 API 정보 반환
    if (empty($pathSegments)) {
        return [
            'success' => true,
            'message' => 'Sandbox Template API',
            'version' => '1.0.0',
            'endpoints' => [
                'GET /files' => '파일 목록 조회',
                'POST /upload' => '파일 업로드',
                'GET /files/{id}' => '파일 상세 조회',
                'DELETE /files/{id}' => '파일 삭제',
                'GET /files/{id}/download' => '파일 다운로드',
                'PUT /projects/{id}' => '프로젝트 정보 업데이트'
            ]
        ];
    }
    
    $endpoint = $pathSegments[0];
    
    switch ($endpoint) {
        case 'files':
            return handleFilesEndpoint($method, array_slice($pathSegments, 1));
            
        case 'upload':
            return handleUploadEndpoint($method, array_slice($pathSegments, 1));
            
        case 'projects':
            return handleProjectsEndpoint($method, array_slice($pathSegments, 1));
            
        default:
            return null;
    }
}

/**
 * 파일 관련 엔드포인트 처리
 */
function handleFilesEndpoint($method, $pathSegments) {
    $functionPath = __DIR__ . '/functions/files';
    
    switch ($method) {
        case 'GET':
            if (empty($pathSegments)) {
                // GET /files - 파일 목록 조회
                return requireFunction($functionPath . '/list.php');
            } elseif (count($pathSegments) === 1) {
                // GET /files/{id} - 파일 상세 조회
                $fileId = $pathSegments[0];
                return requireFunction($functionPath . '/show.php', ['id' => $fileId]);
            } elseif (count($pathSegments) === 2 && $pathSegments[1] === 'download') {
                // GET /files/{id}/download - 파일 다운로드
                $fileId = $pathSegments[0];
                return requireFunction($functionPath . '/download.php', ['id' => $fileId]);
            }
            break;
            
        case 'DELETE':
            if (count($pathSegments) === 1) {
                // DELETE /files/{id} - 파일 삭제
                $fileId = $pathSegments[0];
                return requireFunction($functionPath . '/delete.php', ['id' => $fileId]);
            }
            break;
            
        case 'PUT':
            if (count($pathSegments) === 1) {
                // PUT /files/{id} - 파일 정보 수정
                $fileId = $pathSegments[0];
                return requireFunction($functionPath . '/update.php', ['id' => $fileId]);
            }
            break;
    }
    
    return null;
}

/**
 * 업로드 엔드포인트 처리
 */
function handleUploadEndpoint($method, $pathSegments) {
    $functionPath = __DIR__ . '/functions/upload';
    
    if ($method === 'POST' && empty($pathSegments)) {
        // POST /upload - 파일 업로드
        return requireFunction($functionPath . '/upload.php');
    }
    
    return null;
}

/**
 * 프로젝트 관련 엔드포인트 처리
 */
function handleProjectsEndpoint($method, $pathSegments) {
    $functionPath = __DIR__ . '/functions/projects';
    
    switch ($method) {
        case 'GET':
            if (count($pathSegments) === 1) {
                // GET /projects/{id} - 프로젝트 정보 조회
                $projectId = $pathSegments[0];
                return requireFunction($functionPath . '/show.php', ['id' => $projectId]);
            }
            break;
            
        case 'PUT':
            if (count($pathSegments) === 1) {
                // PUT /projects/{id} - 프로젝트 정보 업데이트
                $projectId = $pathSegments[0];
                return requireFunction($functionPath . '/update.php', ['id' => $projectId]);
            }
            break;
    }
    
    return null;
}

/**
 * 함수 파일을 require 하고 결과 반환
 */
function requireFunction($filePath, $params = []) {
    if (!file_exists($filePath)) {
        throw new Exception("함수 파일을 찾을 수 없습니다: {$filePath}");
    }
    
    // 파라미터를 글로벌 스코프에 설정
    foreach ($params as $key => $value) {
        $GLOBALS[$key] = $value;
    }
    
    // 함수 파일 실행 및 결과 반환
    ob_start();
    $result = require $filePath;
    $output = ob_get_clean();
    
    // require 파일에서 배열을 반환했다면 그것을 사용, 아니면 출력 사용
    if (is_array($result)) {
        return $result;
    } elseif (!empty($output)) {
        return json_decode($output, true) ?: ['success' => false, 'message' => 'Invalid JSON response'];
    } else {
        throw new Exception("함수에서 응답을 반환하지 않았습니다: {$filePath}");
    }
}