<?php

namespace App\Services\SandboxContext\GetScreenUrl;

use App\Services\SandboxContext\GetSandboxUrl\Service as GetSandboxUrlService;

class Service
{
    public function __construct(
        private GetSandboxUrlService $getSandboxUrlService
    ) {}

    public function __invoke(string $domain, string $screen): string
    {
        $baseUrl = ($this->getSandboxUrlService)();
        return "{$baseUrl}/{$domain}/{$screen}";
    }
}