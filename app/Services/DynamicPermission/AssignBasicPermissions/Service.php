<?php

namespace App\Services\DynamicPermission\AssignBasicPermissions;

class Service
{
    /**
     * 사용자에게 기본 역할과 권한을 할당
     * int나 string 타입 모두 지원
     */
    public function __invoke($user, $permissionLevelOrRole)
    {
        if (is_int($permissionLevelOrRole)) {
            return $this->assignByLevel($user, $permissionLevelOrRole);
        }

        if (is_string($permissionLevelOrRole)) {
            return $this->assignByRole($user, $permissionLevelOrRole);
        }

        throw new \InvalidArgumentException('Permission level must be int or string');
    }

    /**
     * 기존 enum 시스템과의 호환성을 위한 레벨 기반 할당
     */
    private function assignByLevel($user, int $permissionLevel)
    {
        // 기존 enum 값을 새로운 역할 시스템으로 매핑
        $roleMapping = [
            0 => [], // INVITED - 권한 없음
            100 => ['user'], // USER
            150 => ['user', 'advanced_user'], // USER_ADVANCED
            200 => ['user', 'service_manager'], // SERVICE_MANAGER
            250 => ['user', 'service_manager', 'senior_service_manager'], // SERVICE_MANAGER_SENIOR
            300 => ['user', 'service_manager', 'organization_admin'], // ORGANIZATION_ADMIN
            350 => ['user', 'service_manager', 'organization_admin', 'senior_organization_admin'], // ORGANIZATION_ADMIN_SENIOR
            400 => ['user', 'service_manager', 'organization_admin', 'organization_owner'], // ORGANIZATION_OWNER
            450 => ['user', 'service_manager', 'organization_admin', 'organization_owner', 'organization_founder'], // ORGANIZATION_OWNER_FOUNDER
            500 => ['platform_admin'], // PLATFORM_ADMIN
            550 => ['platform_admin', 'super_admin'], // PLATFORM_ADMIN_SUPER
        ];

        $roles = $roleMapping[$permissionLevel] ?? [];

        if (!empty($roles)) {
            $user->syncRoles($roles);
        }

        return $roles;
    }

    /**
     * role_name을 이용한 기본 권한 할당 (역할 기반 시스템)
     */
    private function assignByRole($user, string $roleName)
    {
        // 역할 할당
        $user->assignRole($roleName);

        // 역할에 따른 기본 권한 할당
        $defaultPermissions = $this->getDefaultPermissionsForRole($roleName);

        foreach ($defaultPermissions as $permission) {
            $user->givePermissionTo($permission);
        }

        return $user->getRoleNames()->toArray();
    }

    /**
     * 역할별 기본 권한 반환
     */
    private function getDefaultPermissionsForRole(string $roleName): array
    {
        return match($roleName) {
            'user' => [
                'view dashboard',
                'view projects'
            ],
            'service_manager' => [
                'view dashboard',
                'view projects',
                'manage projects',
                'view members'
            ],
            'organization_admin' => [
                'view dashboard',
                'view projects',
                'manage projects',
                'view members',
                'manage members',
                'manage settings'
            ],
            'organization_owner' => [
                'view dashboard',
                'view projects',
                'manage projects',
                'view members',
                'manage members',
                'manage settings',
                'manage billing',
                'manage organization'
            ],
            'platform_admin' => [
                'view dashboard',
                'manage platform',
                'manage all organizations',
                'manage all users'
            ],
            default => []
        };
    }
}