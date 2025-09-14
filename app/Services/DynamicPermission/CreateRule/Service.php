<?php

namespace App\Services\DynamicPermission\CreateRule;

use App\Models\DynamicPermissionRule;
use App\Services\DynamicPermission\ClearCache\Service as ClearCacheService;
use Illuminate\Support\Facades\Log;

class Service
{
    protected $clearCacheService;

    public function __construct(ClearCacheService $clearCacheService)
    {
        $this->clearCacheService = $clearCacheService;
    }

    /**
     * 권한 규칙 생성 도우미
     */
    public function __invoke(array $data): DynamicPermissionRule
    {
        $rule = DynamicPermissionRule::create($data);

        // 캐시 클리어
        $this->clearCacheService->__invoke();

        Log::info('Permission rule created', [
            'rule_id' => $rule->id,
            'resource_type' => $rule->resource_type,
            'action' => $rule->action
        ]);

        return $rule;
    }
}