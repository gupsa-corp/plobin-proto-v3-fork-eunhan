<?php

namespace App\Services\DynamicPermission\DeleteRule;

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
     * 권한 규칙 삭제 도우미
     */
    public function __invoke(DynamicPermissionRule $rule): bool
    {
        $deleted = $rule->delete();

        if ($deleted) {
            // 캐시 클리어
            $this->clearCacheService->__invoke();

            Log::info('Permission rule deleted', [
                'rule_id' => $rule->id,
                'resource_type' => $rule->resource_type,
                'action' => $rule->action
            ]);
        }

        return $deleted;
    }
}