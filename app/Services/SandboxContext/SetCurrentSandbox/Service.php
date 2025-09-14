<?php

namespace App\Services\SandboxContext\SetCurrentSandbox;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Services\SandboxContext\ValidateSandboxExists\Service as ValidateSandboxExistsService;

class Service
{
    const SESSION_KEY = 'current_sandbox';

    public function __construct(
        private ValidateSandboxExistsService $validateSandboxExistsService
    ) {}

    public function __invoke(string $sandbox): void
    {
        if (($this->validateSandboxExistsService)($sandbox)) {
            // 새 세션 키와 레거시 키 모두 설정
            Session::put(self::SESSION_KEY, $sandbox);
            Session::put('sandbox_storage', $sandbox);
            Log::info('Sandbox context changed', ['from' => Session::get(self::SESSION_KEY), 'to' => $sandbox]);
        } else {
            throw new \InvalidArgumentException("Sandbox '{$sandbox}' does not exist");
        }
    }
}