<?php

namespace App\Services\RoleHierarchy\SuggestRoleForPermissions;

use Spatie\Permission\Models\Role;

class Service
{
    public function __invoke(array $permissionNames): ?string
    {
        $roles = Role::with('permissions')->get();
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($roles as $role) {
            $rolePermissions = $role->permissions->pluck('name')->toArray();
            $intersection = array_intersect($permissionNames, $rolePermissions);
            $score = count($intersection) / count($permissionNames);
            
            if ($score > $bestScore && $score >= 0.7) { // 70% match threshold
                $bestScore = $score;
                $bestMatch = $role->name;
            }
        }
        
        return $bestMatch;
    }
}