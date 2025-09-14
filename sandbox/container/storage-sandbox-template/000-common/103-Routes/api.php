<?php

/**
 * 샌드박스 백엔드 API 엔드포인트
 */

// Bootstrap으로 autoload 로드
require_once __DIR__ . '/../../../bootstrap.php';
use App\Services\TemplateCommonService;

// CORS 헤더 설정
StorageCommonService::setApiHeaders();

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 요청 경로 파싱
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// API 경로에서 실제 엔드포인트 추출
$endpoint = '';
if (count($pathParts) >= 3 && $pathParts[0] === 'sandbox' && $pathParts[1] === 'storage-sandbox-template') {
    $endpoint = implode('/', array_slice($pathParts, 3));
}

try {
    switch ($endpoint) {
        case 'projects':
            handleProjectsRequest();
            break;

        case 'projects/stats':
            handleProjectsStatsRequest();
            break;

        case 'columns':
            handleColumnsRequest();
            break;

        case 'custom-data':
            handleCustomDataRequest();
            break;

        default:
            StorageCommonService::errorResponse('Endpoint not found', 404);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    StorageCommonService::errorResponse('Internal server error', 500);
}

// SQLite 데이터베이스 연결 함수
function getSandboxDatabase() {
    $dbPath = __DIR__ . '/../../100-domain-pms/100-common/200-Database/release.sqlite';
    if (!file_exists($dbPath)) {
        throw new Exception("Database file not found: " . $dbPath);
    }
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

/**
 * 프로젝트 관련 요청 처리
 */
function handleProjectsRequest() {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            $filters = [
                'search' => $_GET['search'] ?? '',
                'status' => $_GET['status'] ?? '',
                'priority' => $_GET['priority'] ?? ''
            ];

            $projects = getProjects($filters);
            StorageCommonService::successResponse($projects, 'Projects retrieved successfully');
            break;

        default:
            StorageCommonService::errorResponse('Method not allowed', 405);
    }
}

/**
 * 프로젝트 통계 요청 처리
 */
function handleProjectsStatsRequest() {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method !== 'GET') {
        StorageCommonService::errorResponse('Method not allowed', 405);
    }

    $stats = getProjectStats();
    StorageCommonService::successResponse($stats, 'Project statistics retrieved successfully');
}

/**
 * 컬럼 정보 요청 처리
 */
function handleColumnsRequest() {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method !== 'GET') {
        StorageCommonService::errorResponse('Method not allowed', 405);
    }

    $columns = getDynamicColumns();
    StorageCommonService::successResponse($columns, 'Columns retrieved successfully');
}

/**
 * 커스텀 데이터 요청 처리
 */
function handleCustomDataRequest() {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method !== 'GET') {
        StorageCommonService::errorResponse('Method not allowed', 405);
    }

    $projectIds = $_GET['project_ids'] ?? '';
    if (empty($projectIds)) {
        StorageCommonService::errorResponse('project_ids parameter is required', 400);
    }

    $projectIdsArray = explode(',', $projectIds);
    $customData = getCustomData($projectIdsArray);
    StorageCommonService::successResponse($customData, 'Custom data retrieved successfully');
}