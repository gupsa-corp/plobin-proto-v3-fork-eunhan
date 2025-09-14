<?php

namespace App\Services\SandboxContext\GetDebugInfo;

use Illuminate\Support\Facades\Session;
use App\Services\SandboxContext\GetCurrentSandbox\Service as GetCurrentSandboxService;
use App\Services\SandboxContext\GetCurrentContext\Service as GetCurrentContextService;
use App\Services\SandboxContext\GetAvailableSandboxes\Service as GetAvailableSandboxesService;

class Service
{
    const SESSION_KEY = 'current_sandbox';

    public function __construct(
        private GetCurrentSandboxService $getCurrentSandboxService,
        private GetCurrentContextService $getCurrentContextService,
        private GetAvailableSandboxesService $getAvailableSandboxesService
    ) {}

    public function __invoke(): array
    {
        return [
            'current_sandbox' => ($this->getCurrentSandboxService)(),
            'session_key' => self::SESSION_KEY,
            'session_has_sandbox' => Session::has(self::SESSION_KEY),
            'context' => ($this->getCurrentContextService)(),
            'available_sandboxes' => ($this->getAvailableSandboxesService)(),
            'session_data' => Session::all()
        ];
    }
}