<?php

namespace App\Services\DynamicPermission\UpdateRule;

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
     * 권한 규칙 업데이트 도우미
     */
    public function __invoke(DynamicPermissionRule $rule, array $data): bool
    {
        $updated = $rule->update($data);

        if ($updated) {
            // 캐시 클리어
            $this->clearCacheService->__invoke();

            Log::info('Permission rule updated', [
                'rule_id' => $rule->id,
                'resource_type' => $rule->resource_type,
                'action' => $rule->action
            ]);
        }

        return $updated;
    }
}