<?php

namespace App\Services\RoleHierarchy\GetSuperiorRoles;

use App\Services\RoleHierarchy\GetRoleLevel\Service as GetRoleLevelService;

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

    public function __construct(
        private GetRoleLevelService $getRoleLevelService
    ) {}

    public function __invoke(string $roleName): array
    {
        $currentLevel = ($this->getRoleLevelService)($roleName);
        
        return array_keys(array_filter($this->roleHierarchy, function($level) use ($currentLevel) {
            return $level > $currentLevel;
        }));
    }
}