<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SandboxRoutingService
{
    /**
     * 샌드박스 기본 설정
     */
    // SANDBOX_TEMPLATE_FOLDER constant removed - use dynamic sandbox context

    /**
     * 샌드박스 라우팅 구성을 가져옵니다.
     */
    public function getSandboxRoutingConfig(): array
    {
        return app(\App\Services\SandboxRouting\GetSandboxRoutingConfig\Service::class)();
    }

    /**
     * 특정 샌드박스의 도메인 목록을 가져옵니다.
     */
    public function getDomainList(string $sandboxName = null): array
    {
        return app(\App\Services\SandboxRouting\GetDomainList\Service::class)($sandboxName);
    }

    /**
     * 특정 도메인의 화면 목록을 가져옵니다.
     */
    public function getScreenList(string $domainName, string $sandboxName = null): array
    {
        return app(\App\Services\SandboxRouting\GetScreenList\Service::class)($domainName, $sandboxName);
    }

    /**
     * 샌드박스 뷰 경로를 생성합니다.
     */
    public function generateViewPath(string $domainName, string $screenName, string $sandboxName = null): string
    {
        return app(\App\Services\SandboxRouting\GenerateViewPath\Service::class)($domainName, $screenName, $sandboxName);
    }

    /**
     * 샌드박스 라우트를 생성합니다.
     */
    public function generateRoute(string $domainName, string $screenName, string $sandboxName = null): string
    {
        return app(\App\Services\SandboxRouting\GenerateRoute\Service::class)($domainName, $screenName, $sandboxName);
    }

    /**
     * 라우트 이름을 생성합니다.
     */
    public function generateRouteName(string $domainName, string $screenName, string $sandboxName = null): string
    {
        return app(\App\Services\SandboxRouting\GenerateRouteName\Service::class)($domainName, $screenName, $sandboxName);
    }

    /**
     * 동적 라우트 등록을 위한 라우트 배열을 생성합니다.
     */
    public function getDynamicRoutes(string $sandboxName = null): array
    {
        return app(\App\Services\SandboxRouting\GetDynamicRoutes\Service::class)($sandboxName);
    }

    /**
     * 특정 샌드박스의 모든 사용 가능한 라우트를 가져옵니다.
     */
    public function getAllSandboxRoutes(): array
    {
        return app(\App\Services\SandboxRouting\GetAllSandboxRoutes\Service::class)();
    }

    /**
     * 샌드박스 캐시를 새로고침합니다.
     */
    public function refreshCache(): void
    {
        app(\App\Services\SandboxRouting\RefreshCache\Service::class)();
    }
    
}