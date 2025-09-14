<?php

namespace App\Services\SandboxContext\GetAvailableSandboxes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Services\SandboxContext\GenerateDisplayName\Service as GenerateDisplayNameService;
use App\Services\SandboxContext\CountDomains\Service as CountDomainsService;
use App\Services\SandboxContext\CountScreens\Service as CountScreensService;

class Service
{
    const SESSION_KEY = 'current_sandbox';

    public function __construct(
        private GenerateDisplayNameService $generateDisplayNameService,
        private CountDomainsService $countDomainsService,
        private CountScreensService $countScreensService
    ) {}

    public function __invoke(): array
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
                        'display_name' => ($this->generateDisplayNameService)($sandboxName),
                        'is_active' => $sandboxName === (Session::get(self::SESSION_KEY) ?: Session::get('sandbox_storage')),
                        'domains_count' => ($this->countDomainsService)($directory),
                        'screens_count' => ($this->countScreensService)($directory)
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
            return [];
        }
    }
}