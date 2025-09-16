<?php

namespace App\Controllers\DocumentAnalysis;

/**
 * RFX 도메인 문서 분석 요청 검증 클래스
 */
class Request
{
    /**
     * 문서 분석 요청 검증
     */
    public static function validateAnalysisRequest($data)
    {
        $errors = [];

        if (empty($data['file_id'])) {
            $errors[] = 'file_id는 필수 항목입니다.';
        }

        if (!empty($data['file_id']) && !is_numeric($data['file_id'])) {
            $errors[] = 'file_id는 숫자여야 합니다.';
        }

        return $errors;
    }

    /**
     * 분석 결과 조회 요청 검증
     */
    public static function validateResultRequest($data)
    {
        $errors = [];

        if (empty($data['id'])) {
            $errors[] = 'id는 필수 항목입니다.';
        }

        if (!empty($data['id']) && !is_numeric($data['id'])) {
            $errors[] = 'id는 숫자여야 합니다.';
        }

        return $errors;
    }

    /**
     * 히스토리 조회 요청 검증
     */
    public static function validateHistoryRequest($data)
    {
        $errors = [];

        // 페이지네이션 검증
        if (!empty($data['page']) && !is_numeric($data['page'])) {
            $errors[] = 'page는 숫자여야 합니다.';
        }

        if (!empty($data['limit']) && !is_numeric($data['limit'])) {
            $errors[] = 'limit는 숫자여야 합니다.';
        }

        return $errors;
    }
}