<?php

namespace App\Services\SandboxRouting\GetDomainList;

use Illuminate\Support\Facades\File;

class Service
{
    public function __invoke(string $sandboxName = null): array
    {
        if (!$sandboxName) {
            $sandboxContextService = app(\App\Services\SandboxContextService::class);
            $sandboxName = $sandboxContextService->getCurrentSandbox();
        }

        $sandboxPath = $this->getSandboxPath($sandboxName);

        if (!File::exists($sandboxPath)) {
            return [];
        }

        return $this->scanDomainDirectories($sandboxPath);
    }

    private function getSandboxBasePath(): string
    {
        return base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container'));
    }

    private function getSandboxPath(string $sandboxName): string
    {
        return $this->getSandboxBasePath() . '/' . $sandboxName;
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