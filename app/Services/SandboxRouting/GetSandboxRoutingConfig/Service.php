<?php

namespace App\Services\SandboxRouting\GetSandboxRoutingConfig;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(): array
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

    private function getSandboxBasePath(): string
    {
        return base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container'));
    }

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
}