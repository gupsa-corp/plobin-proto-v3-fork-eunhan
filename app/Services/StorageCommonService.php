<?php

namespace App\Services;

use Exception;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * 샌드박스 템플릿 공통 기능 서비스
 * 각 화면에서 현재 위치와 경로 정보를 제공하는 공통 기능들
 */
class StorageCommonService
{
    /**
     * 현재 화면 정보 반환
     */
    public static function getCurrentScreenInfo(): array
    {
        $currentScriptPath = $_SERVER['SCRIPT_FILENAME'] ?? __FILE__;
        $templateRoot = self::getTemplateRoot();
        
        $relativePath = str_replace($templateRoot, '', $currentScriptPath);
        $relativePath = trim($relativePath, '/\\');
        
        $pathParts = explode('/', $relativePath);
        $screenType = $pathParts[0] ?? '';
        $screenName = $pathParts[1] ?? '';
        
        $currentScreenUrl = self::buildCurrentScreenUrl($screenType, $screenName);
        
        return [
            'type' => $screenType,
            'name' => $screenName,
            'url' => $currentScreenUrl,
            'relative_path' => $relativePath,
            'full_path' => $currentScriptPath
        ];
    }

    /**
     * 업로드 경로 정보 반환
     */
    public static function getUploadPaths(): array
    {
        $templateRoot = self::getTemplateRoot();
        
        $uploadPaths = [
            'template_root' => $templateRoot,
            'uploads_dir' => $templateRoot . '/uploads',
            'temp_dir' => $templateRoot . '/temp',
            'downloads_dir' => $templateRoot . '/downloads'
        ];
        
        // 업로드 디렉토리가 없으면 생성
        foreach ($uploadPaths as $key => $path) {
            if ($key !== 'template_root' && !is_dir($path)) {
                @mkdir($path, 0755, true);
            }
        }
        
        return $uploadPaths;
    }

    /**
     * API 엔드포인트 URL 생성
     */
    public static function getApiUrl(string $endpoint = ''): string
    {
        $baseUrl = self::getBaseUrl();
        $sandboxBasePath = self::getSandboxBasePath();
        
        return $baseUrl . $sandboxBasePath . '/backend/' . ltrim($endpoint, '/');
    }

    /**
     * 다른 화면으로의 URL 생성
     */
    public static function getScreenUrl(string $screenType, string $screenName): string
    {
        $baseUrl = self::getBaseUrl();
        $sandboxBasePath = self::getSandboxBasePath();
        
        return $baseUrl . $sandboxBasePath . '/' . $screenType . '/' . $screenName;
    }

    /**
     * downloads 디렉토리의 파일 목록 반환
     */
    public static function getLocalFilesList(): array
    {
        try {
            $uploadPaths = self::getUploadPaths();
            $downloadsDir = $uploadPaths['downloads_dir'] ?? '';
            $files = [];
            
            if (empty($downloadsDir) || !is_dir($downloadsDir)) {
                return $files;
            }
            
            try {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($downloadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
            } catch (Exception $e) {
                error_log("Directory iterator error: " . $e->getMessage());
                return $files;
            }
            
            $id = 1;
            foreach ($iterator as $file) {
                try {
                    if ($file && $file->isFile()) {
                        $filePath = $file->getPathname();
                        $relativePath = str_replace($downloadsDir . '/', '', $filePath);
                        $mimeType = self::getMimeType($filePath);
                        
                        $files[] = [
                            'id' => $id++,
                            'original_name' => $file->getFilename(),
                            'stored_name' => $file->getFilename(),
                            'file_path' => $relativePath,
                            'file_size' => $file->getSize(),
                            'mime_type' => $mimeType,
                            'uploaded_at' => date('Y-m-d H:i:s', $file->getMTime()),
                            'user_id' => null,
                            'download_url' => self::getDownloadUrl($relativePath)
                        ];
                    }
                } catch (Exception $e) {
                    error_log("File processing error: " . $e->getMessage());
                    continue;
                }
            }
            
            // 최신순으로 정렬
            if (!empty($files)) {
                usort($files, function($a, $b) {
                    return strtotime($b['uploaded_at'] ?? '1970-01-01') - strtotime($a['uploaded_at'] ?? '1970-01-01');
                });
            }
            
            return $files;
        } catch (Exception $e) {
            error_log("getLocalFilesList error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * MIME 타입 추출
     */
    public static function getMimeType(string $filePath): string
    {
        if (function_exists('mime_content_type')) {
            return mime_content_type($filePath);
        }
        
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc' => 'application/msword',
            'zip' => 'application/zip',
            'rar' => 'application/rar',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * 다운로드 URL 생성
     */
    public static function getDownloadUrl(string $relativePath): string
    {
        $baseUrl = self::getBaseUrl();
        $sandboxBasePath = self::getSandboxBasePath();
        
        return $baseUrl . $sandboxBasePath . '/downloads/' . $relativePath;
    }

    /**
     * 동적으로 사용 가능한 화면 목록을 스캔
     */
    public static function getAvailableScreens(): array
    {
        $templateRoot = self::getTemplateRoot();
        $frontendDir = $templateRoot . '/frontend';
        $screens = [];
        
        try {
            if (!is_dir($frontendDir)) {
                return $screens;
            }
            
            $directories = glob($frontendDir . '/*', GLOB_ONLYDIR);
            
            foreach ($directories as $dir) {
                $screenName = basename($dir);
                
                if (preg_match('/^\d{3}-/', $screenName)) {
                    $contentFile = $dir . '/000-content.blade.php';
                    if (file_exists($contentFile)) {
                        $title = self::getScreenTitle($contentFile, $screenName);
                        
                        $screens[] = [
                            'value' => $screenName,
                            'title' => $title,
                            'path' => $dir,
                            'url' => app(\App\Services\SandboxContextService::class)->getSandboxUrl() . "/{$screenName}"
                        ];
                    }
                }
            }
            
            usort($screens, function($a, $b) {
                return strcmp($a['value'], $b['value']);
            });
            
        } catch (Exception $e) {
            error_log("getAvailableScreens error: " . $e->getMessage());
        }
        
        return $screens;
    }

    /**
     * blade 파일에서 화면 제목 추출
     */
    public static function getScreenTitle(string $filePath, string $defaultName): string
    {
        try {
            $content = file_get_contents($filePath);
            
            if (preg_match('/^{{--\s*(.+?)\s*--}}/m', $content, $matches)) {
                $title = trim($matches[1]);
                $title = preg_replace('/(샌드박스|템플릿|화면)\s*/u', '', $title);
                if (!empty($title)) {
                    return $title;
                }
            }
            
            if (preg_match('/<h1[^>]*>(.*?)<\/h1>/s', $content, $matches)) {
                $title = strip_tags($matches[1]);
                $title = trim($title);
                if (!empty($title)) {
                    return $title;
                }
            }
            
            if (preg_match('/text-2xl[^>]*>([^<]+)</s', $content, $matches)) {
                $title = trim($matches[1]);
                if (!empty($title)) {
                    return $title;
                }
            }
            
        } catch (Exception $e) {
            error_log("getScreenTitle error: " . $e->getMessage());
        }
        
        $cleanName = preg_replace('/^\d{3}-/', '', $defaultName);
        $cleanName = str_replace(['-', '_'], ' ', $cleanName);
        $cleanName = ucwords($cleanName);
        
        return $cleanName;
    }

    /**
     * 현재 화면에 대한 디버그 정보 출력
     */
    public static function debugCurrentLocation(): void
    {
        $info = self::getCurrentScreenInfo();
        $paths = self::getUploadPaths();
        
        echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;'>";
        echo "<h4>현재 위치 정보</h4>";
        echo "<ul>";
        echo "<li><strong>화면 타입:</strong> {$info['type']}</li>";
        echo "<li><strong>화면 이름:</strong> {$info['name']}</li>";
        echo "<li><strong>현재 URL:</strong> {$info['url']}</li>";
        echo "<li><strong>상대 경로:</strong> {$info['relative_path']}</li>";
        echo "<li><strong>절대 경로:</strong> {$info['full_path']}</li>";
        echo "</ul>";
        
        echo "<h4>업로드 경로</h4>";
        echo "<ul>";
        foreach ($paths as $key => $path) {
            echo "<li><strong>{$key}:</strong> {$path}</li>";
        }
        echo "</ul>";
        echo "</div>";
    }

    /**
     * 템플릿 루트 디렉토리 경로 반환
     */
    private static function getTemplateRoot(): string
    {
        return app(\App\Services\SandboxContextService::class)->getSandboxPath();
    }

    /**
     * 베이스 URL 반환
     */
    private static function getBaseUrl(): string
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    /**
     * 샌드박스 베이스 경로 반환
     */
    private static function getSandboxBasePath(): string
    {
        $currentUrlPath = $_SERVER['REQUEST_URI'] ?? '';
        $sandboxBasePath = '';
        
        if (preg_match('#/sandbox/([^/]+)#', $currentUrlPath, $matches)) {
            $sandboxBasePath = '/sandbox/' . $matches[1];
        }
        
        return $sandboxBasePath;
    }

    /**
     * 현재 화면 URL 구성
     */
    private static function buildCurrentScreenUrl(string $screenType, string $screenName): string
    {
        $baseUrl = self::getBaseUrl();
        $sandboxBasePath = self::getSandboxBasePath();
        
        return $baseUrl . $sandboxBasePath . '/' . $screenType . '/' . $screenName;
    }

    /**
     * 샌드박스 설정 반환
     */
    public static function getSandboxConfig(): array
    {
        $templateRoot = self::getTemplateRoot();
        
        return [
            'database' => [
                'path' => $templateRoot . '/database/release.sqlite'
            ],
            'upload' => [
                'max_file_size' => 50 * 1024 * 1024, // 50MB
                'allowed_extensions' => [
                    'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'
                ]
            ]
        ];
    }

    /**
     * 데이터베이스 초기화
     */
    public static function initializeDatabase(): void
    {
        $config = self::getSandboxConfig();
        $dbPath = $config['database']['path'];
        
        // 데이터베이스 디렉토리가 없으면 생성
        $dbDir = dirname($dbPath);
        if (!is_dir($dbDir)) {
            @mkdir($dbDir, 0755, true);
        }
        
        // 데이터베이스가 없으면 생성
        if (!file_exists($dbPath)) {
            $pdo = new \PDO("sqlite:" . $dbPath);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // 프로젝트 테이블 생성
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS projects (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    description TEXT,
                    status TEXT DEFAULT 'pending',
                    progress INTEGER DEFAULT 0,
                    team_members INTEGER DEFAULT 1,
                    priority TEXT DEFAULT 'medium',
                    client TEXT,
                    budget INTEGER DEFAULT 0,
                    category TEXT DEFAULT 'general',
                    start_date DATE,
                    end_date DATE,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // 샘플 데이터 추가
            $sampleProjects = [
                [19, 'IoT 센서 네트워크', '스마트 빌딩용 IoT 센서 및 제어 시스템', 'planning', 0, 6, 'medium', '스마트빌딩', 14000000, 'IoT', '2024-05-01', '2024-08-31'],
                [13, '앱 테스트 및 배포', '앱스토어 배포 및 품질 보증 테스트', 'planning', 0, 2, 'medium', '디지털솔루션', 2000000, 'Testing', '2024-05-15', '2024-06-15'],
                [15, 'ERP 시스템 구축', '전사 자원 관리 시스템 구축 프로젝트', 'planning', 5, 7, 'high', '엔터프라이즈', 35000000, 'Enterprise System', '2024-04-01', '2024-09-30']
            ];
            
            $stmt = $pdo->prepare("
                INSERT OR REPLACE INTO projects (id, name, description, status, progress, team_members, priority, client, budget, category, start_date, end_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($sampleProjects as $project) {
                $stmt->execute($project);
            }
        }
    }

    /**
     * API 헤더 설정
     */
    public static function setApiHeaders(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Cache-Control: no-cache, no-store, must-revalidate');
    }

    /**
     * JSON 응답 생성
     */
    public static function jsonResponse($data, int $status = 200, array $headers = []): void
    {
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
    public static function errorResponse(string $message, int $status = 400, array $errors = []): void
    {
        self::jsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    /**
     * 성공 응답 생성
     */
    public static function successResponse($data = [], string $message = '성공적으로 처리되었습니다.'): void
    {
        self::jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
}