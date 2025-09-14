<?php

namespace App\Services\SandboxContext\GetSandboxPath;

use App\Services\SandboxContext\GetCurrentSandbox\Service as GetCurrentSandboxService;

class Service
{
    public function __construct(
        private GetCurrentSandboxService $getCurrentSandboxService
    ) {}

    public function __invoke(): string
    {
        $currentSandbox = ($this->getCurrentSandboxService)();
        return base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container') . "/{$currentSandbox}");
    }
}