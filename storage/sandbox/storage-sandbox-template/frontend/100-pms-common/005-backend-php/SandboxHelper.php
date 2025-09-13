<?php

/**
 * 샌드박스 백엔드 헬퍼 함수들
 * 백엔드 파일들에서 샌드박스 경로 정보를 얻기 위한 함수들
 */

class SandboxHelper
{
    /**
     * 샌드박스 템플릿 루트 디렉토리 경로
     */
    public static function getTemplateRoot(): string
    {
        return dirname(__FILE__);
    }

    /**
     * 샌드박스 업로드 관련 경로들
     */
    public static function getUploadPaths(): array
    {
        $templateRoot = self::getTemplateRoot();
        
        return [
            'template_root' => $templateRoot,
            'uploads_dir' => $templateRoot . '/uploads',
            'temp_dir' => $templateRoot . '/temp',
            'downloads_dir' => $templateRoot . '/downloads'
        ];
    }

    /**
     * HTTP 요청에서 현재 샌드박스 위치 정보 추출
     */
    public static function getCurrentSandboxInfo(): array
    {
        $request = request();
        $currentUrl = $request->getRequestUri();
        
        // 샌드박스 기본 경로 추출
        $sandboxBasePath = '';
        if (preg_match('#/sandbox/storage-sandbox-template#', $currentUrl)) {
            $parts = explode('/sandbox/storage-sandbox-template', $currentUrl);
            $sandboxBasePath = '/sandbox/storage-sandbox-template';
        }
        
        $baseUrl = $request->getSchemeAndHttpHost();
        
        return [
            'base_url' => $baseUrl,
            'sandbox_base_path' => $sandboxBasePath,
            'current_url' => $currentUrl,
            'full_base_url' => $baseUrl . $sandboxBasePath
        ];
    }

    /**
     * 프론트엔드 화면 URL 생성
     */
    public static function getScreenUrl(string $screenType, string $screenName): string
    {
        $info = self::getCurrentSandboxInfo();
        return $info['full_base_url'] . '/' . $screenType . '/' . $screenName;
    }

    /**
     * API 엔드포인트 URL 생성
     */
    public static function getApiUrl(string $endpoint = ''): string
    {
        $info = self::getCurrentSandboxInfo();
        return $info['full_base_url'] . '/backend/' . ltrim($endpoint, '/');
    }

    /**
     * 업로드 디렉토리 초기화
     */
    public static function initializeUploadDirectories(): void
    {
        $paths = self::getUploadPaths();
        
        foreach ($paths as $key => $path) {
            if ($key !== 'template_root' && !is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * 파일 저장 경로 생성 (날짜 기반)
     */
    public static function generateStoragePath(string $originalName): array
    {
        $info = pathinfo($originalName);
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';
        $basename = $info['filename'] ?? 'file';
        
        $date = now();
        $dateFolder = $date->format('Y/m/d');
        $timestamp = $date->format('Y-m-d_H-i-s');
        
        $storedName = $timestamp . '_' . $basename . $extension;
        $relativePath = $dateFolder . '/' . $storedName;
        
        $uploadPaths = self::getUploadPaths();
        $fullPath = $uploadPaths['uploads_dir'] . '/' . $relativePath;
        
        // 디렉토리 생성
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        return [
            'stored_name' => $storedName,
            'relative_path' => $relativePath,
            'full_path' => $fullPath,
            'date_folder' => $dateFolder
        ];
    }

    /**
     * MIME 타입으로부터 파일 카테고리 추출
     */
    public static function getFileCategory(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) return 'image';
        if (str_starts_with($mimeType, 'video/')) return 'video';
        if (str_starts_with($mimeType, 'audio/')) return 'audio';
        if ($mimeType === 'application/pdf') return 'pdf';
        if (str_contains($mimeType, 'document') || str_contains($mimeType, 'text')) return 'document';
        if (str_contains($mimeType, 'zip') || str_contains($mimeType, 'rar')) return 'archive';
        
        return 'other';
    }

    /**
     * 파일 크기 형식화
     */
    public static function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        
        return round(($bytes / pow($k, $i)), 2) . ' ' . $sizes[$i];
    }

    /**
     * 디버그 정보 출력 (개발용)
     */
    public static function debugInfo(): array
    {
        return [
            'template_root' => self::getTemplateRoot(),
            'upload_paths' => self::getUploadPaths(),
            'sandbox_info' => self::getCurrentSandboxInfo(),
            'php_version' => phpversion(),
            'server_time' => date('Y-m-d H:i:s')
        ];
    }
}