<?php

namespace App\Services\SandboxContext\GetSandboxApiUrl;

use App\Services\SandboxContext\GetSandboxUrl\Service as GetSandboxUrlService;

class Service
{
    public function __construct(
        private GetSandboxUrlService $getSandboxUrlService
    ) {}

    public function __invoke(string $endpoint = ''): string
    {
        $baseUrl = ($this->getSandboxUrlService)();
        return $endpoint ? "{$baseUrl}/api/{$endpoint}" : "{$baseUrl}/api";
    }
}