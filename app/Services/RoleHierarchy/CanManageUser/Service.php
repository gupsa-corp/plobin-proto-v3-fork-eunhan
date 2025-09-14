<?php

namespace App\Services\RoleHierarchy\CanManageUser;

use App\Models\User;
use App\Services\RoleHierarchy\GetHighestRoleLevel\Service as GetHighestRoleLevelService;

class Service
{
    public function __construct(
        private GetHighestRoleLevelService $getHighestRoleLevelService
    ) {}

    public function __invoke(User $manager, User $target): bool
    {
        $managerRoles = $manager->getRoleNames()->toArray();
        $targetRoles = $target->getRoleNames()->toArray();
        
        // Platform admin can manage everyone
        if (in_array('platform_admin', $managerRoles)) {
            return true;
        }
        
        // Get highest role level for each user
        $managerLevel = ($this->getHighestRoleLevelService)($managerRoles);
        $targetLevel = ($this->getHighestRoleLevelService)($targetRoles);
        
        return $managerLevel > $targetLevel;
    }
}