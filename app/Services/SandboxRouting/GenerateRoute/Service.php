<?php

namespace App\Services\SandboxRouting\GenerateRoute;

class Service
{
    public function __invoke(string $domainName, string $screenName, string $sandboxName = null): string
    {
        if (!$sandboxName) {
            $sandboxContextService = app(\App\Services\SandboxContextService::class);
            $sandboxName = $sandboxContextService->getCurrentSandbox();
        }

        return "/sandbox/{$sandboxName}/{$domainName}/{$screenName}";
    }
}