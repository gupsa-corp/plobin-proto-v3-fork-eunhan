<?php

namespace App\Services\SandboxRouting\RefreshCache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(): void
    {
        Cache::forget('sandbox_routing_config');
        Cache::forget('available_sandboxes');

        // 새로운 캐시 생성
        $getSandboxRoutingConfigService = app(\App\Services\SandboxRouting\GetSandboxRoutingConfig\Service::class);
        $getSandboxRoutingConfigService();

        $getAllSandboxRoutesService = app(\App\Services\SandboxRouting\GetAllSandboxRoutes\Service::class);
        $getAllSandboxRoutesService();

        Log::info('Sandbox routing cache refreshed');
    }
}