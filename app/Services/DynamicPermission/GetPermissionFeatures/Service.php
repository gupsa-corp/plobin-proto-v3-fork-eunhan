<?php

namespace App\Services\DynamicPermission\GetPermissionFeatures;

class Service
{
    /**
     * 권한의 사용 가능한 기능 목록 반환
     */
    public function __invoke(string $permissionName): array
    {
        $permission = \Spatie\Permission\Models\Permission::findByName($permissionName);
        if (!$permission) {
            return [];
        }

        return $permission->roles->map(function ($role) {
            return [
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'users_count' => $role->users()->count()
            ];
        })->toArray();
    }
}