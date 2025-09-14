<?php

namespace App\Services\SandboxRouting\GetScreenList;

use Illuminate\Support\Facades\File;

class Service
{
    public function __invoke(string $domainName, string $sandboxName = null): array
    {
        if (!$sandboxName) {
            $sandboxContextService = app(\App\Services\SandboxContextService::class);
            $sandboxName = $sandboxContextService->getCurrentSandbox();
        }

        $domainPath = $this->getDomainPath($sandboxName, $domainName);

        if (!File::exists($domainPath)) {
            return [];
        }

        return $this->scanScreenDirectories($domainPath);
    }

    private function getSandboxBasePath(): string
    {
        return base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container'));
    }

    private function getSandboxPath(string $sandboxName): string
    {
        return $this->getSandboxBasePath() . '/' . $sandboxName;
    }

    private function getDomainPath(string $sandboxName, string $domainName): string
    {
        return $this->getSandboxPath($sandboxName) . '/' . $domainName;
    }

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
}