<?php
/**
 * PMS 공통 PHP 함수들
 */

/**
 * 페이지네이션 계산
 */
function calculatePagination($totalItems, $currentPage = 1, $perPage = 10) {
    $totalPages = (int) ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total_items' => $totalItems,
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * 정렬 파라미터 유효성 검사
 */
function validateSortParams($sortBy, $sortOrder, $allowedColumns = []) {
    // 허용된 컬럼 체크
    if (!empty($allowedColumns) && !in_array($sortBy, $allowedColumns)) {
        $sortBy = $allowedColumns[0] ?? 'id';
    }
    
    // 정렬 순서 체크
    $sortOrder = strtolower($sortOrder);
    if (!in_array($sortOrder, ['asc', 'desc'])) {
        $sortOrder = 'asc';
    }
    
    return [$sortBy, $sortOrder];
}

/**
 * 필터 파라미터 정리
 */
function sanitizeFilters($filters, $allowedFilters = []) {
    $cleaned = [];
    
    foreach ($filters as $key => $value) {
        // 허용된 필터만 처리
        if (!empty($allowedFilters) && !in_array($key, $allowedFilters)) {
            continue;
        }
        
        // 빈 값 제거
        if ($value === '' || $value === null) {
            continue;
        }
        
        // 기본 sanitization
        if (is_string($value)) {
            $cleaned[$key] = trim(strip_tags($value));
        } else {
            $cleaned[$key] = $value;
        }
    }
    
    return $cleaned;
}

/**
 * URL 쿼리 파라미터 생성
 */
function buildQueryString($params, $excludeKeys = []) {
    $filtered = array_filter($params, function($value, $key) use ($excludeKeys) {
        return !in_array($key, $excludeKeys) && $value !== '' && $value !== null;
    }, ARRAY_FILTER_USE_BOTH);
    
    return http_build_query($filtered);
}

/**
 * 상태별 색상 클래스 반환
 */
function getStatusColorClass($status) {
    $colors = [
        'planned' => 'bg-gray-100 text-gray-800',
        'in_progress' => 'bg-blue-100 text-blue-800',
        'completed' => 'bg-green-100 text-green-800',
        'on_hold' => 'bg-yellow-100 text-yellow-800'
    ];
    
    return $colors[$status] ?? 'bg-gray-100 text-gray-800';
}

/**
 * 우선순위별 색상 클래스 반환
 */
function getPriorityColorClass($priority) {
    $colors = [
        'high' => 'bg-red-100 text-red-800',
        'medium' => 'bg-yellow-100 text-yellow-800',
        'low' => 'bg-green-100 text-green-800'
    ];
    
    return $colors[$priority] ?? 'bg-gray-100 text-gray-800';
}

/**
 * 진행률 색상 클래스 반환
 */
function getProgressColorClass($progress) {
    if ($progress >= 80) return 'bg-green-500';
    if ($progress >= 50) return 'bg-yellow-500';
    if ($progress >= 20) return 'bg-orange-500';
    return 'bg-red-500';
}

/**
 * 날짜 포맷팅
 */
function formatDate($date, $format = 'Y-m-d') {
    if (empty($date)) return '';
    
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    
    return $date->format($format);
}

/**
 * 숫자 포맷팅
 */
function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals, '.', ',');
}

/**
 * 바이트 크기 포맷팅
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * HTML 안전한 문자열 반환
 */
function e($value, $doubleEncode = true) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8', $doubleEncode);
}

/**
 * JSON 응답 생성
 */
function jsonResponse($data, $status = 200, $headers = []) {
    http_response_code($status);
    
    $defaultHeaders = [
        'Content-Type' => 'application/json',
        'Cache-Control' => 'no-cache, no-store, must-revalidate'
    ];
    
    foreach (array_merge($defaultHeaders, $headers) as $name => $value) {
        header("$name: $value");
    }
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 에러 응답 생성
 */
function errorResponse($message, $status = 400, $errors = []) {
    jsonResponse([
        'success' => false,
        'message' => $message,
        'errors' => $errors
    ], $status);
}

/**
 * 성공 응답 생성
 */
function successResponse($data = [], $message = '성공적으로 처리되었습니다.') {
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * 유효성 검사 규칙
 */
class ValidationRules {
    public static function required($value, $message = '필수 입력입니다.') {
        return !empty(trim($value)) ? true : $message;
    }
    
    public static function minLength($value, $min, $message = null) {
        $message = $message ?: "최소 {$min}자 이상 입력해주세요.";
        return strlen($value) >= $min ? true : $message;
    }
    
    public static function maxLength($value, $max, $message = null) {
        $message = $message ?: "최대 {$max}자까지 입력 가능합니다.";
        return strlen($value) <= $max ? true : $message;
    }
    
    public static function email($value, $message = '올바른 이메일 주소를 입력해주세요.') {
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? true : $message;
    }
    
    public static function numeric($value, $message = '숫자만 입력해주세요.') {
        return is_numeric($value) ? true : $message;
    }
    
    public static function in($value, $options, $message = null) {
        $message = $message ?: '올바른 값을 선택해주세요.';
        return in_array($value, $options) ? true : $message;
    }
}

/**
 * 간단한 유효성 검사기
 */
function validate($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $fieldRules) {
        $value = $data[$field] ?? '';
        
        foreach ($fieldRules as $rule) {
            $result = call_user_func($rule, $value);
            if ($result !== true) {
                $errors[$field] = $result;
                break; // 첫 번째 오류에서 중단
            }
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
?>