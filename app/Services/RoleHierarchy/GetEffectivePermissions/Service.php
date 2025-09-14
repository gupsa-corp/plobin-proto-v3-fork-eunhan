<?php

namespace App\Services\RoleHierarchy\GetEffectivePermissions;

use Spatie\Permission\Models\Role;

class Service
{
    public function __invoke(string $roleName): array
    {
        $role = Role::where('name', $roleName)->with('permissions')->first();
        if (!$role) {
            return [];
        }
        
        $permissions = $role->permissions->pluck('name')->toArray();
        
        // Add inherited permissions from superior roles if needed
        // This is a basic implementation - you might want more complex inheritance rules
        
        return array_unique($permissions);
    }
}