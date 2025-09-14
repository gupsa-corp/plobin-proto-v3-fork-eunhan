<?php

namespace App\Services\DynamicPermission\GetRoleFeatures;

class Service
{
    /**
     * 역할의 사용 가능한 기능 목록 반환
     */
    public function __invoke(string $roleName): array
    {
        $role = \Spatie\Permission\Models\Role::findByName($roleName);
        if (!$role) {
            return [];
        }

        return $role->permissions->map(function ($permission) {
            return [
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
                'category' => $this->getPermissionCategory($permission->name)
            ];
        })->groupBy('category')->toArray();
    }

    /**
     * 권한 카테고리 분류
     */
    private function getPermissionCategory(string $permissionName): string
    {
        if (str_contains($permissionName, 'member')) return '멤버 관리';
        if (str_contains($permissionName, 'project')) return '프로젝트 관리';
        if (str_contains($permissionName, 'billing')) return '결제 관리';
        if (str_contains($permissionName, 'organization')) return '조직 설정';
        if (str_contains($permissionName, 'permission')) return '권한 관리';
        return '기타';
    }
}