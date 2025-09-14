<?php

namespace App\Services\SandboxContext\GetSandboxUrl;

use App\Services\SandboxContext\GetCurrentSandbox\Service as GetCurrentSandboxService;

class Service
{
    public function __construct(
        private GetCurrentSandboxService $getCurrentSandboxService
    ) {}

    public function __invoke(): string
    {
        $currentSandbox = ($this->getCurrentSandboxService)();
        return "/sandbox/{$currentSandbox}";
    }
}