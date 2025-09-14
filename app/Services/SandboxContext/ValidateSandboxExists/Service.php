<?php

namespace App\Services\SandboxContext\ValidateSandboxExists;

use Illuminate\Support\Facades\File;

class Service
{
    public function __invoke(string $sandbox): bool
    {
        $sandboxPath = base_path(env('SANDBOX_CONTAINER_PATH', 'sandbox/container') . "/{$sandbox}");
        return File::exists($sandboxPath) && File::isDirectory($sandboxPath);
    }
}