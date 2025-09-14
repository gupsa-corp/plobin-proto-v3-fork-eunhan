<?php

namespace App\Services\DynamicPermission\GetPermissionMatrix;

class Service
{
    /**
     * 권한 매트릭스 반환
     */
    public function __invoke(): array
    {
        $roles = \Spatie\Permission\Models\Role::with('permissions')->get();
        $permissions = \Spatie\Permission\Models\Permission::all();

        return $roles->map(function ($role) use ($permissions) {
            $rolePermissions = $role->permissions->pluck('name')->toArray();

            return [
                'role' => $role,
                'permissions' => $permissions->map(function ($permission) use ($rolePermissions) {
                    return [
                        'permission' => $permission,
                        'has_permission' => in_array($permission->name, $rolePermissions)
                    ];
                })->toArray()
            ];
        })->toArray();
    }
}