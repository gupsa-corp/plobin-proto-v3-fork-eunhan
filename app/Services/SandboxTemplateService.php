<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SandboxTemplateService
{
    /**
     * 샌드박스별 템플릿 경로 반환
     */
    public function getTemplatePath(string $sandboxName): string
    {
        return app(\App\Services\SandboxTemplate\GetTemplatePath\Service::class)($sandboxName);
    }

    /**
     * 샌드박스 존재 여부 확인
     */
    public function validateSandboxExists(string $sandboxName): bool
    {
        return app(\App\Services\SandboxTemplate\ValidateSandboxExists\Service::class)($sandboxName);
    }

    /**
     * 커스텀 화면 목록 반환
     */
    public function getCustomScreens(string $sandboxName): array
    {
        return app(\App\Services\SandboxTemplate\GetCustomScreens\Service::class)($sandboxName);
    }

    /**
     * 샌드박스의 도메인 목록 반환
     */
    public function getDomains(string $sandboxName): array
    {
        return app(\App\Services\SandboxTemplate\GetDomains\Service::class)($sandboxName);
    }
}