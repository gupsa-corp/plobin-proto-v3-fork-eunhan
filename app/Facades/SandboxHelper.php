<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Sandbox Helper Facade
 * 
 * 샌드박스 환경에서 StorageCommonService의 기능을 쉽게 사용할 수 있도록 하는 Facade입니다.
 * 
 * @method static array getCurrentScreenInfo()
 * @method static array getUploadPaths()
 * @method static string getApiUrl(string $endpoint = '')
 * @method static string getScreenUrl(string $screenType, string $screenName)
 * @method static array getLocalFilesList()
 * @method static string getMimeType(string $filePath)
 * @method static string getDownloadUrl(string $relativePath)
 * @method static array getAvailableScreens()
 * @method static string getScreenTitle(string $filePath, string $defaultName)
 * @method static void debugCurrentLocation()
 * @method static array getSandboxConfig()
 * @method static void initializeDatabase()
 * @method static \PDO getDatabaseConnection()
 * @method static void setApiHeaders()
 * @method static void jsonResponse($data, int $status = 200, array $headers = [])
 * @method static void errorResponse(string $message, int $status = 400, array $errors = [])
 * @method static void successResponse($data = [], string $message = '성공적으로 처리되었습니다.')
 * 
 * @see \App\Services\StorageCommonService
 */
class SandboxHelper extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'sandbox.helper';
    }
}