<?php

namespace App\Services\SandboxContext\DetectAndSetFromUrl;

use App\Services\SandboxContext\ValidateSandboxExists\Service as ValidateSandboxExistsService;
use App\Services\SandboxContext\SetCurrentSandbox\Service as SetCurrentSandboxService;

class Service
{
    public function __construct(
        private ValidateSandboxExistsService $validateSandboxExistsService,
        private SetCurrentSandboxService $setCurrentSandboxService
    ) {}

    public function __invoke(?string $url = null): bool
    {
        if (!$url) {
            $url = request()->getRequestUri();
        }

        // /sandbox/{sandbox_name}/... 패턴에서 샌드박스 추출
        if (preg_match('#/sandbox/([^/]+)#', $url, $matches)) {
            $detectedSandbox = $matches[1];
            
            if (($this->validateSandboxExistsService)($detectedSandbox)) {
                ($this->setCurrentSandboxService)($detectedSandbox);
                return true;
            }
        }

        return false;
    }
}