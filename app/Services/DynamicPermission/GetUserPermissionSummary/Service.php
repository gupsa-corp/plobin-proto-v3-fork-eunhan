<?php

namespace App\Services\DynamicPermission\GetUserPermissionSummary;

class Service
{
    /**
     * 사용자별 권한 요약 정보 반환
     */
    public function __invoke($user): array
    {
        return [
            'roles' => $user->getRoleNames()->toArray(),
            'permissions' => $user->getPermissionNames()->toArray(),
            'all_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            'legacy_level' => $this->getUserLegacyLevel($user),
        ];
    }

    /**
     * 기존 시스템과의 호환성을 위한 레벨 계산
     */
    private function getUserLegacyLevel($user): int
    {
        // 조직별 권한 레벨 계산
        $organizationMember = $user->organizationMemberships()
            ->where('organization_id', request()->route('organization'))
            ->first();

        if ($organizationMember && $organizationMember->role_name) {
            return match($organizationMember->role_name) {
                'user' => 100,
                'service_manager' => 200,
                'organization_admin' => 300,
                'organization_owner' => 400,
                'platform_admin' => 500,
                default => 0
            };
        }

        // 역할 기반 레벨 계산 (대략적)
        if ($user->hasRole('super_admin')) return 550;
        if ($user->hasRole('platform_admin')) return 500;
        if ($user->hasRole('organization_founder')) return 450;
        if ($user->hasRole('organization_owner')) return 400;
        if ($user->hasRole('senior_organization_admin')) return 350;
        if ($user->hasRole('organization_admin')) return 300;
        if ($user->hasRole('senior_service_manager')) return 250;
        if ($user->hasRole('service_manager')) return 200;
        if ($user->hasRole('advanced_user')) return 150;
        if ($user->hasRole('user')) return 100;

        return 0;
    }
}