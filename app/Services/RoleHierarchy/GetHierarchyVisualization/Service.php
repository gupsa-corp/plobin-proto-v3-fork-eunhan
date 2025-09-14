<?php

namespace App\Services\RoleHierarchy\GetHierarchyVisualization;

use Spatie\Permission\Models\Role;

class Service
{
    /**
     * Define role hierarchy levels
     * Higher number = higher authority
     */
    protected array $roleHierarchy = [
        'platform_admin' => 1000,
        'organization_admin' => 800,
        'project_manager' => 600,
        'editor' => 400,
        'organization_member' => 200,
        'viewer' => 100,
    ];

    public function __invoke(): array
    {
        $roles = collect($this->roleHierarchy)
            ->sortByDesc(function($level) { return $level; })
            ->map(function($level, $name) {
                $role = Role::where('name', $name)->with('permissions')->first();
                return [
                    'name' => $name,
                    'display_name' => ucwords(str_replace('_', ' ', $name)),
                    'level' => $level,
                    'description' => $role?->description,
                    'permissions_count' => $role?->permissions->count() ?? 0,
                    'users_count' => $role?->users->count() ?? 0
                ];
            });
        
        return $roles->values()->toArray();
    }
}