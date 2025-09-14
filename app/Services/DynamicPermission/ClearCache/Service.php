<?php

namespace App\Services\DynamicPermission\ClearCache;

use Illuminate\Support\Facades\Cache;

class Service
{
    /**
     * 권한 규칙 캐시 클리어
     */
    public function __invoke(): void
    {
        Cache::tags(['permission_rules'])->flush();
    }
}