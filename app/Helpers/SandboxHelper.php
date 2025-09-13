<?php

namespace App\Helpers;

use App\Services\StorageCommonService;

/**
 * Sandbox Static Helper
 * 
 * 샌드박스 환경에서 StorageCommonService의 기능을 정적 메서드로 쉽게 사용할 수 있도록 하는 헬퍼 클래스입니다.
 * Laravel autoloader만 있으면 어디서든 사용 가능합니다.
 * 
 * Usage:
 * use App\Helpers\SandboxHelper;
 * $screenInfo = SandboxHelper::getCurrentScreenInfo();
 */
class SandboxHelper
{
    /**
     * 현재 화면 정보 반환
     */
    public static function getCurrentScreenInfo(): array
    {
        return StorageCommonService::getCurrentScreenInfo();
    }

    /**
     * 업로드 경로 정보 반환
     */
    public static function getUploadPaths(): array
    {
        return StorageCommonService::getUploadPaths();
    }

    /**
     * API 엔드포인트 URL 생성
     */
    public static function getApiUrl(string $endpoint = ''): string
    {
        return StorageCommonService::getApiUrl($endpoint);
    }

    /**
     * 다른 화면으로의 URL 생성
     */
    public static function getScreenUrl(string $screenType, string $screenName): string
    {
        return StorageCommonService::getScreenUrl($screenType, $screenName);
    }

    /**
     * downloads 디렉토리의 파일 목록 반환
     */
    public static function getLocalFilesList(): array
    {
        return StorageCommonService::getLocalFilesList();
    }

    /**
     * MIME 타입 추출
     */
    public static function getMimeType(string $filePath): string
    {
        return StorageCommonService::getMimeType($filePath);
    }

    /**
     * 다운로드 URL 생성
     */
    public static function getDownloadUrl(string $relativePath): string
    {
        return StorageCommonService::getDownloadUrl($relativePath);
    }

    /**
     * 동적으로 사용 가능한 화면 목록을 스캔
     */
    public static function getAvailableScreens(): array
    {
        return StorageCommonService::getAvailableScreens();
    }

    /**
     * blade 파일에서 화면 제목 추출
     */
    public static function getScreenTitle(string $filePath, string $defaultName): string
    {
        return StorageCommonService::getScreenTitle($filePath, $defaultName);
    }

    /**
     * 현재 화면에 대한 디버그 정보 출력
     */
    public static function debugCurrentLocation(): void
    {
        StorageCommonService::debugCurrentLocation();
    }

    /**
     * 샌드박스 설정 반환
     */
    public static function getSandboxConfig(): array
    {
        return StorageCommonService::getSandboxConfig();
    }

    /**
     * 데이터베이스 초기화
     */
    public static function initializeDatabase(): void
    {
        StorageCommonService::initializeDatabase();
    }

    /**
     * 데이터베이스 PDO 연결 반환
     */
    public static function getDatabaseConnection(): \PDO
    {
        return StorageCommonService::getDatabaseConnection();
    }

    /**
     * API 헤더 설정
     */
    public static function setApiHeaders(): void
    {
        StorageCommonService::setApiHeaders();
    }

    /**
     * JSON 응답 생성
     */
    public static function jsonResponse($data, int $status = 200, array $headers = []): void
    {
        StorageCommonService::jsonResponse($data, $status, $headers);
    }

    /**
     * 에러 응답 생성
     */
    public static function errorResponse(string $message, int $status = 400, array $errors = []): void
    {
        StorageCommonService::errorResponse($message, $status, $errors);
    }

    /**
     * 성공 응답 생성
     */
    public static function successResponse($data = [], string $message = '성공적으로 처리되었습니다.'): void
    {
        StorageCommonService::successResponse($data, $message);
    }
}