<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SandboxRoutingService
{
    /**
     * 샌드박스 기본 설정
     */
    private const SANDBOX_BASE_PATH = 'sandbox/container';
    private const SANDBOX_TEMPLATE_FOLDER = 'storage-sandbox-template';
    
    /**
     * 샌드박스 라우팅 구성을 가져옵니다.
     */
    public function getSandboxRoutingConfig(): array
    {
        return Cache::remember('sandbox_routing_config', 300, function () {
            $basePath = $this->getSandboxBasePath();
            
            if (!File::exists($basePath)) {
                Log::warning("Sandbox base path does not exist: {$basePath}");
                return [];
            }
            
            return $this->scanDomainDirectories($basePath);
        });
    }
    
    /**
     * 특정 샌드박스의 도메인 목록을 가져옵니다.
     */
    public function getDomainList(string $sandboxName = null): array
    {
        $sandboxName = $sandboxName ?: self::SANDBOX_TEMPLATE_FOLDER;
        $sandboxPath = $this->getSandboxPath($sandboxName);
        
        if (!File::exists($sandboxPath)) {
            return [];
        }
        
        return $this->scanDomainDirectories($sandboxPath);
    }
    
    /**
     * 특정 도메인의 화면 목록을 가져옵니다.
     */
    public function getScreenList(string $domainName, string $sandboxName = null): array
    {
        $sandboxName = $sandboxName ?: self::SANDBOX_TEMPLATE_FOLDER;
        $domainPath = $this->getDomainPath($sandboxName, $domainName);
        
        if (!File::exists($domainPath)) {
            return [];
        }
        
        return $this->scanScreenDirectories($domainPath);
    }
    
    /**
     * 샌드박스 뷰 경로를 생성합니다.
     */
    public function generateViewPath(string $domainName, string $screenName, string $sandboxName = null): string
    {
        $sandboxName = $sandboxName ?: self::SANDBOX_TEMPLATE_FOLDER;
        return "sandbox.container.{$sandboxName}.{$domainName}.{$screenName}";
    }
    
    /**
     * 샌드박스 라우트를 생성합니다.
     */
    public function generateRoute(string $domainName, string $screenName, string $sandboxName = null): string
    {
        $sandboxName = $sandboxName ?: self::SANDBOX_TEMPLATE_FOLDER;
        return "/sandbox/{$sandboxName}/{$domainName}/{$screenName}";
    }
    
    /**
     * 라우트 이름을 생성합니다.
     */
    public function generateRouteName(string $domainName, string $screenName, string $sandboxName = null): string
    {
        $sandboxName = $sandboxName ?: self::SANDBOX_TEMPLATE_FOLDER;
        return "sandbox.{$sandboxName}.{$domainName}.{$screenName}";
    }
    
    /**
     * 동적 라우트 등록을 위한 라우트 배열을 생성합니다.
     */
    public function getDynamicRoutes(string $sandboxName = null): array
    {
        $sandboxName = $sandboxName ?: self::SANDBOX_TEMPLATE_FOLDER;
        $routes = [];
        
        $domains = $this->getDomainList($sandboxName);
        
        foreach ($domains as $domainInfo) {
            $domainName = $domainInfo['name'];
            $screens = $this->getScreenList($domainName, $sandboxName);
            
            foreach ($screens as $screenInfo) {
                $screenName = $screenInfo['name'];
                $route = $this->generateRoute($domainName, $screenName, $sandboxName);
                $viewPath = $this->generateViewPath($domainName, $screenName, $sandboxName);
                $routeName = $this->generateRouteName($domainName, $screenName, $sandboxName);
                
                $routes[] = [
                    'path' => $route,
                    'view' => $viewPath,
                    'name' => $routeName,
                    'sandbox' => $sandboxName,
                    'domain' => $domainName,
                    'screen' => $screenName,
                    'metadata' => [
                        'domain_path' => $domainInfo['path'],
                        'screen_path' => $screenInfo['path'],
                        'has_blade_file' => $screenInfo['has_blade_file'] ?? false,
                        'created_at' => $screenInfo['created_at'] ?? null,
                        'modified_at' => $screenInfo['modified_at'] ?? null,
                    ]
                ];
            }
        }
        
        return $routes;
    }
    
    /**
     * 특정 샌드박스의 모든 사용 가능한 라우트를 가져옵니다.
     */
    public function getAllSandboxRoutes(): array
    {
        $allRoutes = [];
        $sandboxes = $this->getAvailableSandboxes();
        
        foreach ($sandboxes as $sandboxInfo) {
            $sandboxRoutes = $this->getDynamicRoutes($sandboxInfo['name']);
            $allRoutes = array_merge($allRoutes, $sandboxRoutes);
        }
        
        return $allRoutes;
    }
    
    /**
     * 샌드박스 캐시를 새로고침합니다.
     */
    public function refreshCache(): void
    {
        Cache::forget('sandbox_routing_config');
        Cache::forget('available_sandboxes');
        
        // 새로운 캐시 생성
        $this->getSandboxRoutingConfig();
        $this->getAvailableSandboxes();
        
        Log::info('Sandbox routing cache refreshed');
    }
    
    /**
     * 샌드박스 베이스 경로를 가져옵니다.
     */
    private function getSandboxBasePath(): string
    {
        return base_path(self::SANDBOX_BASE_PATH);
    }
    
    /**
     * 특정 샌드박스 경로를 가져옵니다.
     */
    private function getSandboxPath(string $sandboxName): string
    {
        return $this->getSandboxBasePath() . '/' . $sandboxName;
    }
    
    /**
     * 특정 도메인 경로를 가져옵니다.
     */
    private function getDomainPath(string $sandboxName, string $domainName): string
    {
        return $this->getSandboxPath($sandboxName) . '/' . $domainName;
    }
    
    /**
     * 디렉토리에서 도메인 폴더들을 스캔합니다.
     */
    private function scanDomainDirectories(string $path): array
    {
        $domains = [];
        
        if (!File::exists($path) || !File::isDirectory($path)) {
            return $domains;
        }
        
        $directories = File::directories($path);
        
        foreach ($directories as $directory) {
            $dirName = basename($directory);
            
            // 도메인 폴더 패턴 (1xx-domain-xxx)
            if (preg_match('/^(\d+)-domain-(.+)$/', $dirName, $matches)) {
                $domains[] = [
                    'name' => $dirName,
                    'code' => $matches[1],
                    'domain' => $matches[2],
                    'path' => $directory,
                    'created_at' => File::lastModified($directory),
                ];
            }
        }
        
        // 코드 순서대로 정렬
        usort($domains, function ($a, $b) {
            return intval($a['code']) <=> intval($b['code']);
        });
        
        return $domains;
    }
    
    /**
     * 도메인 디렉토리에서 화면 폴더들을 스캔합니다.
     */
    private function scanScreenDirectories(string $domainPath): array
    {
        $screens = [];
        
        if (!File::exists($domainPath) || !File::isDirectory($domainPath)) {
            return $screens;
        }
        
        $directories = File::directories($domainPath);
        
        foreach ($directories as $directory) {
            $dirName = basename($directory);
            
            // 화면 폴더 패턴 (1xx-screen-xxx)
            if (preg_match('/^(\d+)-screen-(.+)$/', $dirName, $matches)) {
                $bladeFile = $directory . '/index.blade.php';
                
                $screens[] = [
                    'name' => $dirName,
                    'code' => $matches[1],
                    'screen' => $matches[2],
                    'path' => $directory,
                    'has_blade_file' => File::exists($bladeFile),
                    'created_at' => File::lastModified($directory),
                    'modified_at' => File::exists($bladeFile) ? File::lastModified($bladeFile) : null,
                ];
            }
        }
        
        // 코드 순서대로 정렬
        usort($screens, function ($a, $b) {
            return intval($a['code']) <=> intval($b['code']);
        });
        
        return $screens;
    }
    
    /**
     * 사용 가능한 모든 샌드박스 목록을 가져옵니다.
     */
    private function getAvailableSandboxes(): array
    {
        return Cache::remember('available_sandboxes', 300, function () {
            $sandboxes = [];
            $basePath = $this->getSandboxBasePath();
            
            if (!File::exists($basePath)) {
                return $sandboxes;
            }
            
            $directories = File::directories($basePath);
            
            foreach ($directories as $directory) {
                $sandboxName = basename($directory);
                
                $sandboxes[] = [
                    'name' => $sandboxName,
                    'path' => $directory,
                    'created_at' => File::lastModified($directory),
                ];
            }
            
            return $sandboxes;
        });
    }
}