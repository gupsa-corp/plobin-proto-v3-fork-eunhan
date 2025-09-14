<?php

namespace App\Services\SandboxRouting\GetAllSandboxRoutes;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class Service
{
    public function __invoke(): array
    {
        $allRoutes = [];
        $sandboxes = $this->getAvailableSandboxes();

        $getDynamicRoutesService = app(\App\Services\SandboxRouting\GetDynamicRoutes\Service::class);

        foreach ($sandboxes as $sandboxInfo) {
            $sandboxRoutes = $getDynamicRoutesService($sandboxInfo['name']);
            $allRoutes = array_merge($allRoutes, $sandboxRoutes);
        }

        return $allRoutes;
    }

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

    private function getSandboxBasePath(): string
    {
        return base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container'));
    }
}