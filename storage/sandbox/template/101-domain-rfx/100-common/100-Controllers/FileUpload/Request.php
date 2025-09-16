<?php

namespace App\Controllers\FileUpload;

/**
 * RFX 도메인 파일 업로드 요청 검증 클래스
 */
class Request
{
    /**
     * 파일 업로드 요청 검증
     */
    public static function validateUploadRequest($files, $data = [])
    {
        $errors = [];

        // 파일 존재 여부 확인
        if (empty($files['file'])) {
            $errors[] = '업로드할 파일이 필요합니다.';
            return $errors;
        }

        $file = $files['file'];

        // 파일 업로드 오류 확인
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = '파일 업로드 중 오류가 발생했습니다.';
            return $errors;
        }

        // 파일 크기 확인 (예: 50MB 제한)
        $maxSize = 50 * 1024 * 1024; // 50MB
        if ($file['size'] > $maxSize) {
            $errors[] = '파일 크기는 50MB를 초과할 수 없습니다.';
        }

        // 허용된 파일 타입 확인
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
            'image/jpeg',
            'image/png',
            'image/gif'
        ];

        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = '허용되지 않은 파일 타입입니다.';
        }

        // 파일명 길이 확인
        if (strlen($file['name']) > 255) {
            $errors[] = '파일명이 너무 깁니다. (최대 255자)';
        }

        return $errors;
    }

    /**
     * 파일 목록 조회 요청 검증
     */
    public static function validateListRequest($data)
    {
        $errors = [];

        // 페이지네이션 검증
        if (!empty($data['page']) && !is_numeric($data['page'])) {
            $errors[] = 'page는 숫자여야 합니다.';
        }

        if (!empty($data['limit']) && !is_numeric($data['limit'])) {
            $errors[] = 'limit는 숫자여야 합니다.';
        }

        // 정렬 옵션 검증
        if (!empty($data['sort'])) {
            $allowedSorts = ['name', 'size', 'created_at', 'type'];
            if (!in_array($data['sort'], $allowedSorts)) {
                $errors[] = '유효하지 않은 정렬 옵션입니다.';
            }
        }

        return $errors;
    }

    /**
     * 파일 삭제 요청 검증
     */
    public static function validateDeleteRequest($data)
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
}