<?php

namespace App\Services\SandboxContext\FindFirstAvailableSandbox;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(): ?string
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
}