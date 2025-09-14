<?php

namespace App\Services\RoleHierarchy\GetAssignableRoles;

use App\Models\User;
use App\Services\RoleHierarchy\GetHighestRoleLevel\Service as GetHighestRoleLevelService;

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
        private GetHighestRoleLevelService $getHighestRoleLevelService
    ) {}

    public function __invoke(User $user): array
    {
        $userRoles = $user->getRoleNames()->toArray();
        
        // Platform admin can assign any role
        if (in_array('platform_admin', $userRoles)) {
            return array_keys($this->roleHierarchy);
        }
        
        $userLevel = ($this->getHighestRoleLevelService)($userRoles);
        
        // Can only assign roles with lower authority
        return array_keys(array_filter($this->roleHierarchy, function($level) use ($userLevel) {
            return $level < $userLevel;
        }));
    }
}