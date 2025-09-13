<?php
/**
 * 폼 제출 API 엔드포인트
 * URL: /sandbox/storage-sandbox-template/backend/form-creator.php
 */

// CORS 헤더 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With');

// OPTIONS 요청 처리 (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 세션 시작 (에러 방지)
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} catch (Exception $e) {
    // 세션 시작 실패해도 계속 진행
}

// 데이터베이스 헬퍼 포함
require_once __DIR__ . '/../frontend/100-pms-common/001-database/form-db-helper.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetRequest();
            break;
            
        case 'POST':
            handlePostRequest();
            break;
            
        default:
            errorResponse('지원하지 않는 HTTP 메소드입니다.');
    }
    
} catch (Exception $e) {
    error_log('Form Creator API 오류: ' . $e->getMessage());
    errorResponse('서버 오류가 발생했습니다: ' . $e->getMessage());
}

/**
 * GET 요청 처리 - 연결 상태 확인 및 통계 조회
 */
function handleGetRequest() {
    $action = $_GET['action'] ?? 'status';
    
    switch ($action) {
        case 'status':
            // 연결 상태 확인
            $dbTest = testFormDBConnection();
            successResponse([
                'status' => 'connected',
                'database' => $dbTest,
                'timestamp' => date('Y-m-d H:i:s'),
                'server_info' => [
                    'php_version' => PHP_VERSION,
                    'sqlite_version' => SQLite3::version()['versionString']
                ]
            ], '폼 생성기 API가 정상 작동 중입니다.');
            break;
            
        case 'stats':
            // 폼 제출 통계
            $stats = getFormSubmissionStats();
            successResponse($stats, '폼 제출 통계 조회 완료');
            break;
            
        case 'submissions':
            // 폼 제출 목록
            $formName = $_GET['form_name'] ?? null;
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);
            
            $submissions = getFormSubmissions($formName, $limit, $offset);
            successResponse($submissions, '폼 제출 목록 조회 완료');
            break;
            
        default:
            errorResponse('알 수 없는 액션입니다.');
    }
}

/**
 * POST 요청 처리 - 폼 데이터 저장
 */
function handlePostRequest() {
    // JSON 데이터 읽기
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        errorResponse('잘못된 JSON 형식입니다.');
    }
    
    // 필수 데이터 검증
    if (!isset($data['formName']) || !isset($data['formData'])) {
        errorResponse('폼 이름과 폼 데이터가 필요합니다.');
    }
    
    $formName = trim($data['formName']);
    $formData = $data['formData'];
    
    if (empty($formName)) {
        errorResponse('폼 이름은 필수입니다.');
    }
    
    if (!is_array($formData) || empty($formData)) {
        errorResponse('유효한 폼 데이터가 필요합니다.');
    }
    
    // 메타데이터 준비
    $metadata = [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'session_id' => session_id(),
        'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
        'timestamp' => $data['timestamp'] ?? date('Y-m-d H:i:s')
    ];
    
    try {
        // 데이터베이스에 저장
        $submissionId = saveFormSubmission($formName, $formData, $metadata);
        
        if ($submissionId) {
            // 저장된 데이터 조회하여 응답
            $formDB = new FormSubmissionDB();
            $savedSubmission = $formDB->getFormSubmission($submissionId);
            
            successResponse([
                'submission_id' => $submissionId,
                'form_name' => $formName,
                'submitted_data' => $formData,
                'submitted_at' => $savedSubmission['submitted_at'],
                'metadata' => $metadata
            ], '폼이 성공적으로 제출되었습니다.');
            
        } else {
            errorResponse('폼 제출 저장에 실패했습니다.');
        }
        
    } catch (Exception $e) {
        error_log('폼 제출 처리 오류: ' . $e->getMessage());
        errorResponse('폼 제출 처리 중 오류가 발생했습니다.');
    }
}

/**
 * 입력 데이터 정제
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    if (is_string($data)) {
        // HTML 태그 제거 및 특수문자 정리
        $data = strip_tags($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return trim($data);
    }
    
    return $data;
}

/**
 * 폼 데이터 유효성 검증
 */
function validateFormData($formData) {
    $errors = [];
    
    foreach ($formData as $key => $value) {
        // 키 이름 검증
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $key)) {
            $errors[] = "잘못된 필드명: $key";
        }
        
        // 값 검증 (기본적인 길이 제한)
        if (is_string($value) && strlen($value) > 10000) {
            $errors[] = "필드 '$key'의 값이 너무 깁니다.";
        }
    }
    
    return $errors;
}

/**
 * 디버그 정보 (개발 환경에서만)
 */
function debugInfo() {
    return [
        'method' => $_SERVER['REQUEST_METHOD'],
        'headers' => getallheaders(),
        'get_params' => $_GET,
        'post_params' => $_POST,
        'raw_input' => file_get_contents('php://input'),
        'session_id' => session_id(),
        'time' => date('Y-m-d H:i:s')
    ];
}
?>