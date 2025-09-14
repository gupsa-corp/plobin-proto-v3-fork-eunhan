<?php

namespace App\Services\DynamicPermission\CanPerformAction;

use App\Models\DynamicPermissionRule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Service
{
    protected int $cacheMinutes = 60;

    /**
     * 사용자가 특정 리소스에 대한 액션을 수행할 수 있는지 확인
     */
    public function __invoke($user, string $resourceType, string $action, array $context = []): bool
    {
        // 1. 캐시에서 규칙 조회
        $cacheKey = "permission_rule_{$resourceType}_{$action}";
        $rule = Cache::remember($cacheKey, $this->cacheMinutes * 60, function () use ($resourceType, $action) {
            return DynamicPermissionRule::active()
                ->forResource($resourceType)
                ->forAction($action)
                ->first();
        });

        if (!$rule) {
            // 규칙이 없으면 기본적으로 거부
            Log::warning('No permission rule found', [
                'resource_type' => $resourceType,
                'action' => $action,
                'user_id' => $user?->id ?? 'guest'
            ]);
            return false;
        }

        // 2. 권한 규칙 실행
        return $rule->checkPermission($user, $context);
    }
}