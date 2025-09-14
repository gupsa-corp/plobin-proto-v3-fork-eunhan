<?php

namespace App\Services\SandboxContext\GetSandboxStoragePath;

use App\Services\SandboxContext\GetCurrentSandbox\Service as GetCurrentSandboxService;

class Service
{
    public function __construct(
        private GetCurrentSandboxService $getCurrentSandboxService
    ) {}

    public function __invoke(): string
    {
        $currentSandbox = ($this->getCurrentSandboxService)();
        return storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . "/{$currentSandbox}");
    }
}