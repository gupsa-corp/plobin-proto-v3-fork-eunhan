<?php

namespace App\Services\SandboxContext\GetCurrentContext;

use App\Services\SandboxContext\GetCurrentSandbox\Service as GetCurrentSandboxService;
use App\Services\SandboxContext\GetSandboxPath\Service as GetSandboxPathService;
use App\Services\SandboxContext\GetSandboxUrl\Service as GetSandboxUrlService;
use App\Services\SandboxContext\GetSandboxStoragePath\Service as GetSandboxStoragePathService;
use App\Services\SandboxContext\GetSandboxApiUrl\Service as GetSandboxApiUrlService;
use App\Services\SandboxContext\GenerateDisplayName\Service as GenerateDisplayNameService;
use App\Services\SandboxContext\ValidateSandboxExists\Service as ValidateSandboxExistsService;

class Service
{
    public function __construct(
        private GetCurrentSandboxService $getCurrentSandboxService,
        private GetSandboxPathService $getSandboxPathService,
        private GetSandboxUrlService $getSandboxUrlService,
        private GetSandboxStoragePathService $getSandboxStoragePathService,
        private GetSandboxApiUrlService $getSandboxApiUrlService,
        private GenerateDisplayNameService $generateDisplayNameService,
        private ValidateSandboxExistsService $validateSandboxExistsService
    ) {}

    public function __invoke(): array
    {
        $currentSandbox = ($this->getCurrentSandboxService)();
        
        return [
            'sandbox_name' => $currentSandbox,
            'sandbox_path' => ($this->getSandboxPathService)(),
            'sandbox_url' => ($this->getSandboxUrlService)(),
            'sandbox_storage_path' => ($this->getSandboxStoragePathService)(),
            'sandbox_api_url' => ($this->getSandboxApiUrlService)(),
            'display_name' => ($this->generateDisplayNameService)($currentSandbox),
            'exists' => ($this->validateSandboxExistsService)($currentSandbox)
        ];
    }
}