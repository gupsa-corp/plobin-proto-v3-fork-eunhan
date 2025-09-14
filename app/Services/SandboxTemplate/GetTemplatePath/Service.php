<?php

namespace App\Services\SandboxTemplate\GetTemplatePath;

class Service
{
    public function __invoke(string $sandboxName): string
    {
        if (empty($sandboxName)) {
            return '';
        }

        return base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container') . "/{$sandboxName}");
    }
}