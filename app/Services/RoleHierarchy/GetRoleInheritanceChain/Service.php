<?php

namespace App\Services\RoleHierarchy\GetRoleInheritanceChain;

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
        $chain = [$roleName];
        $currentLevel = ($this->getRoleLevelService)($roleName);
        
        // Add all superior roles in hierarchy order
        $superiorRoles = array_filter($this->roleHierarchy, function($level) use ($currentLevel) {
            return $level > $currentLevel;
        });
        
        arsort($superiorRoles);
        $chain = array_merge($chain, array_keys($superiorRoles));
        
        return $chain;
    }
}