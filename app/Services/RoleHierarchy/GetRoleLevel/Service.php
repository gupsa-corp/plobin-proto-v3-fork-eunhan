<?php

namespace App\Services\RoleHierarchy\GetRoleLevel;

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

    public function __invoke(string $roleName): int
    {
        return $this->roleHierarchy[$roleName] ?? 0;
    }
}