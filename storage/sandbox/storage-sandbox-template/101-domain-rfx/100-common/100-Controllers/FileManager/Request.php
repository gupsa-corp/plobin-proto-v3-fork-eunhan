<?php

namespace App\Controllers\FileManager;

/**
 * RFX 도메인 파일 관리 요청 검증 클래스
 */
class Request
{
    /**
     * API 요청 기본 검증
     */
    public static function validateApiRequest($method, $segments)
    {
        $errors = [];

        if (empty($method)) {
            $errors[] = 'HTTP method is required';
        }

        if (empty($segments) || !is_array($segments)) {
            $errors[] = 'API segments are required';
        }

        // API 구조 검증
        if (!empty($segments) && $segments[0] !== 'api') {
            $errors[] = 'Invalid API path structure';
        }

        return $errors;
    }

    /**
     * 문서 분석 API 요청 검증
     */
    public static function validateDocumentAnalysisRequest($data)
    {
        $errors = [];

        if (empty($data['file_id'])) {
            $errors[] = 'file_id is required';
        }

        if (!empty($data['file_id']) && !is_numeric($data['file_id'])) {
            $errors[] = 'file_id must be numeric';
        }

        return $errors;
    }

    /**
     * GET 요청 검증
     */
    public static function validateGetRequest($segments)
    {
        $errors = [];

        // 세그먼트 구조 검증
        if (count($segments) < 2) {
            $errors[] = 'Invalid API endpoint structure';
        }

        if (!empty($segments[1]) && $segments[1] !== 'document') {
            $errors[] = 'Invalid resource type';
        }

        // ID가 있는 경우 검증
        if (count($segments) >= 3 && !is_numeric($segments[2])) {
            $errors[] = 'Invalid document ID';
        }

        return $errors;
    }
}