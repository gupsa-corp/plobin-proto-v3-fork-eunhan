<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SandboxContextService
{
    const SESSION_KEY = 'current_sandbox';

    /**
     * 현재 선택된 샌드박스 이름을 가져옵니다.
     */
    public function getCurrentSandbox(): string
    {
        $sandbox = Session::get(self::SESSION_KEY);

        // 새로운 세션 키에 값이 없으면 레거시 키도 확인
        if (!$sandbox) {
            $sandbox = Session::get('sandbox_storage');
        }

        // 여전히 없으면 사용 가능한 첫 번째 샌드박스를 자동으로 설정
        if (!$sandbox) {
            $firstSandbox = $this->findFirstAvailableSandbox();
            if ($firstSandbox) {
                $this->setCurrentSandbox($firstSandbox);
                return $firstSandbox;
            }
            throw new \Exception('샌드박스가 선택되지 않았습니다. setCurrentSandbox()로 샌드박스를 선택해주세요.');
        }

        return $sandbox;
    }

    /**
     * 현재 샌드박스를 설정합니다.
     */
    public function setCurrentSandbox(string $sandbox): void
    {
        if ($this->validateSandboxExists($sandbox)) {
            // 새 세션 키와 레거시 키 모두 설정
            Session::put(self::SESSION_KEY, $sandbox);
            Session::put('sandbox_storage', $sandbox);
            Log::info('Sandbox context changed', ['from' => Session::get(self::SESSION_KEY), 'to' => $sandbox]);
        } else {
            throw new \InvalidArgumentException("Sandbox '{$sandbox}' does not exist");
        }
    }

    /**
     * 현재 샌드박스의 컨테이너 경로를 가져옵니다.
     */
    public function getSandboxPath(): string
    {
        $currentSandbox = $this->getCurrentSandbox();
        return base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container') . "/{$currentSandbox}");
    }

    /**
     * 현재 샌드박스의 기본 URL 경로를 가져옵니다.
     */
    public function getSandboxUrl(): string
    {
        $currentSandbox = $this->getCurrentSandbox();
        return "/sandbox/{$currentSandbox}";
    }

    /**
     * 현재 샌드박스의 스토리지 경로를 가져옵니다.
     */
    public function getSandboxStoragePath(): string
    {
        $currentSandbox = $this->getCurrentSandbox();
        return storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . "/{$currentSandbox}");
    }

    /**
     * 샌드박스 API URL을 생성합니다.
     */
    public function getSandboxApiUrl(string $endpoint = ''): string
    {
        $baseUrl = $this->getSandboxUrl();
        return $endpoint ? "{$baseUrl}/api/{$endpoint}" : "{$baseUrl}/api";
    }

    /**
     * 특정 도메인/스크린으로의 URL을 생성합니다.
     */
    public function getScreenUrl(string $domain, string $screen): string
    {
        $baseUrl = $this->getSandboxUrl();
        return "{$baseUrl}/{$domain}/{$screen}";
    }

    /**
     * 샌드박스가 존재하는지 확인합니다.
     */
    public function validateSandboxExists(string $sandbox): bool
    {
        $sandboxPath = base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container') . "/{$sandbox}");
        return File::exists($sandboxPath) && File::isDirectory($sandboxPath);
    }

    /**
     * 사용 가능한 모든 샌드박스 목록을 가져옵니다.
     */
    public function getAvailableSandboxes(): array
    {
        $containerPath = base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container'));
        $sandboxes = [];

        if (!File::exists($containerPath)) {
            return [];
        }

        try {
            $directories = File::directories($containerPath);
            
            foreach ($directories as $directory) {
                $sandboxName = basename($directory);
                
                // 기본 구조 검증 (000-common 폴더 존재 여부)
                $commonPath = $directory . '/000-common';
                if (File::exists($commonPath)) {
                    $sandboxes[] = [
                        'name' => $sandboxName,
                        'path' => $directory,
                        'display_name' => $this->generateDisplayName($sandboxName),
                        'is_active' => $sandboxName === (Session::get(self::SESSION_KEY) ?: Session::get('sandbox_storage')),
                        'domains_count' => $this->countDomains($directory),
                        'screens_count' => $this->countScreens($directory)
                    ];
                }
            }

            // 이름순으로 정렬
            usort($sandboxes, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            return $sandboxes;

        } catch (\Exception $e) {
            Log::error('Error getting available sandboxes', ['error' => $e->getMessage()]);
            return [
[]
            ];
        }
    }

    /**
     * 샌드박스 표시 이름을 생성합니다.
     */
    private function generateDisplayName(string $sandboxName): string
    {
        // Example: my-custom-template -> My Custom Template
        $displayName = str_replace(['-', '_'], ' ', $sandboxName);
        return ucwords($displayName);
    }

    /**
     * 샌드박스의 도메인 개수를 셉니다.
     */
    private function countDomains(string $sandboxPath): int
    {
        try {
            $directories = File::directories($sandboxPath);
            $domainCount = 0;

            foreach ($directories as $directory) {
                $dirName = basename($directory);
                // xxx-domain-xxx 패턴의 디렉토리만 카운트
                if (preg_match('/^\d+-domain-/', $dirName)) {
                    $domainCount++;
                }
            }

            return $domainCount;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 샌드박스의 스크린 개수를 셉니다.
     */
    private function countScreens(string $sandboxPath): int
    {
        try {
            $screenCount = 0;
            $directories = File::directories($sandboxPath);

            foreach ($directories as $domainDirectory) {
                $dirName = basename($domainDirectory);
                // 도메인 디렉토리만 검사
                if (preg_match('/^\d+-domain-/', $dirName)) {
                    $screenDirectories = File::directories($domainDirectory);
                    foreach ($screenDirectories as $screenDirectory) {
                        $screenName = basename($screenDirectory);
                        // xxx-screen-xxx 패턴의 디렉토리만 카운트
                        if (preg_match('/^\d+-screen-/', $screenName)) {
                            $contentFile = $screenDirectory . '/000-content.blade.php';
                            if (File::exists($contentFile)) {
                                $screenCount++;
                            }
                        }
                    }
                }
            }

            return $screenCount;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 현재 샌드박스 컨텍스트 정보를 반환합니다.
     */
    public function getCurrentContext(): array
    {
        $currentSandbox = $this->getCurrentSandbox();
        
        return [
            'sandbox_name' => $currentSandbox,
            'sandbox_path' => $this->getSandboxPath(),
            'sandbox_url' => $this->getSandboxUrl(),
            'sandbox_storage_path' => $this->getSandboxStoragePath(),
            'sandbox_api_url' => $this->getSandboxApiUrl(),
            'display_name' => $this->generateDisplayName($currentSandbox),
            'exists' => $this->validateSandboxExists($currentSandbox)
        ];
    }

    /**
     * URL에서 샌드박스를 자동 감지하고 세션에 설정합니다.
     */
    public function detectAndSetFromUrl(?string $url = null): bool
    {
        if (!$url) {
            $url = request()->getRequestUri();
        }

        // /sandbox/{sandbox_name}/... 패턴에서 샌드박스 추출
        if (preg_match('#/sandbox/([^/]+)#', $url, $matches)) {
            $detectedSandbox = $matches[1];
            
            if ($this->validateSandboxExists($detectedSandbox)) {
                $this->setCurrentSandbox($detectedSandbox);
                return true;
            }
        }

        return false;
    }

    /**
     * 샌드박스 컨텍스트를 초기화합니다.
     */
    public function reset(): void
    {
        Session::forget(self::SESSION_KEY);
        Log::info('Sandbox context reset to default');
    }

    /**
     * 사용 가능한 첫 번째 샌드박스를 찾습니다 (순환 참조 방지용)
     */
    private function findFirstAvailableSandbox(): ?string
    {
        $containerPath = base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container'));

        if (!File::exists($containerPath)) {
            return null;
        }

        try {
            $directories = File::directories($containerPath);

            foreach ($directories as $directory) {
                $sandboxName = basename($directory);

                // 기본 구조 검증 (000-common 폴더 존재 여부)
                $commonPath = $directory . '/000-common';
                if (File::exists($commonPath)) {
                    return $sandboxName;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error finding first available sandbox', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * 디버그 정보를 반환합니다.
     */
    public function getDebugInfo(): array
    {
        return [
            'current_sandbox' => $this->getCurrentSandbox(),
            'session_key' => self::SESSION_KEY,
            'session_has_sandbox' => Session::has(self::SESSION_KEY),
            'context' => $this->getCurrentContext(),
            'available_sandboxes' => $this->getAvailableSandboxes(),
            'session_data' => Session::all()
        ];
    }
}