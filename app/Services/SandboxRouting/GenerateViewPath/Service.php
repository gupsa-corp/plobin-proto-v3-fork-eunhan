<?php

namespace App\Services\SandboxRouting\GenerateViewPath;

class Service
{
    public function __invoke(string $domainName, string $screenName, string $sandboxName = null): string
    {
        if (!$sandboxName) {
            $sandboxContextService = app(\App\Services\SandboxContextService::class);
            $sandboxName = $sandboxContextService->getCurrentSandbox();
        }

        return "sandbox.container.{$sandboxName}.{$domainName}.{$screenName}";
    }
}