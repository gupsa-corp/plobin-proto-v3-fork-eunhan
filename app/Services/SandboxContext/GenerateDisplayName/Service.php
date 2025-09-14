<?php

namespace App\Services\SandboxContext\GenerateDisplayName;

class Service
{
    public function __invoke(string $sandboxName): string
    {
        // Example: my-custom-template -> My Custom Template
        $displayName = str_replace(['-', '_'], ' ', $sandboxName);
        return ucwords($displayName);
    }
}