<?php

use App\Services\ViewService;
use App\Services\ComponentService;

// ========================================
// VIEW SERVICE WRAPPER FUNCTIONS
// ========================================

if (!function_exists('findViewDirectory')) {
    function findViewDirectory()
    {
        return app(ViewService::class)->findViewDirectory();
    }
}

if (!function_exists('getCommonPath')) {
    function getCommonPath()
    {
        return app(ViewService::class)->getCommonPath();
    }
}

if (!function_exists('getCurrentViewPath')) {
    function getCurrentViewPath()
    {
        return app(ViewService::class)->getCurrentViewPath();
    }
}

// ========================================
// LOCALE HELPER FUNCTIONS
// ========================================

if (!function_exists('getHtmlLang')) {
    function getHtmlLang()
    {
        return app()->getLocale();
    }
}

if (!function_exists('getHtmlLangAttribute')) {
    function getHtmlLangAttribute()
    {
        return 'lang="' . getHtmlLang() . '"';
    }
}

// ========================================
// SANDBOX HELPER FUNCTIONS
// ========================================

use App\Services\StorageCommonService;

if (!function_exists('getSandboxScreenInfo')) {
    function getSandboxScreenInfo()
    {
        return StorageCommonService::getCurrentScreenInfo();
    }
}

if (!function_exists('getSandboxUploadPaths')) {
    function getSandboxUploadPaths()
    {
        return StorageCommonService::getUploadPaths();
    }
}

if (!function_exists('getSandboxApiUrl')) {
    function getSandboxApiUrl($endpoint = '')
    {
        return StorageCommonService::getApiUrl($endpoint);
    }
}

if (!function_exists('getSandboxScreenUrl')) {
    function getSandboxScreenUrl($screenType, $screenName)
    {
        return StorageCommonService::getScreenUrl($screenType, $screenName);
    }
}

if (!function_exists('getSandboxConfig')) {
    function getSandboxConfig()
    {
        return StorageCommonService::getSandboxConfig();
    }
}

if (!function_exists('getSandboxDatabaseConnection')) {
    function getSandboxDatabaseConnection()
    {
        return StorageCommonService::getDatabaseConnection();
    }
}

if (!function_exists('initializeSandboxDatabase')) {
    function initializeSandboxDatabase()
    {
        return StorageCommonService::initializeDatabase();
    }
}

if (!function_exists('getSandboxLocalFilesList')) {
    function getSandboxLocalFilesList()
    {
        return StorageCommonService::getLocalFilesList();
    }
}

if (!function_exists('getSandboxMimeType')) {
    function getSandboxMimeType($filePath)
    {
        return StorageCommonService::getMimeType($filePath);
    }
}

if (!function_exists('getSandboxDownloadUrl')) {
    function getSandboxDownloadUrl($relativePath)
    {
        return StorageCommonService::getDownloadUrl($relativePath);
    }
}

if (!function_exists('setSandboxApiHeaders')) {
    function setSandboxApiHeaders()
    {
        return StorageCommonService::setApiHeaders();
    }
}

if (!function_exists('sandboxJsonResponse')) {
    function sandboxJsonResponse($data, $status = 200, $headers = [])
    {
        return StorageCommonService::jsonResponse($data, $status, $headers);
    }
}

if (!function_exists('sandboxErrorResponse')) {
    function sandboxErrorResponse($message, $status = 400, $errors = [])
    {
        return StorageCommonService::errorResponse($message, $status, $errors);
    }
}

if (!function_exists('sandboxSuccessResponse')) {
    function sandboxSuccessResponse($data = [], $message = '성공적으로 처리되었습니다.')
    {
        return StorageCommonService::successResponse($data, $message);
    }
}
