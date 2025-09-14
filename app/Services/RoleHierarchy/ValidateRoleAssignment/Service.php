<?php

namespace App\Services\RoleHierarchy\ValidateRoleAssignment;

use App\Models\User;
use App\Services\RoleHierarchy\GetHighestRoleLevel\Service as GetHighestRoleLevelService;
use App\Services\RoleHierarchy\GetRoleLevel\Service as GetRoleLevelService;

class Service
{
    public function __construct(
        private GetHighestRoleLevelService $getHighestRoleLevelService,
        private GetRoleLevelService $getRoleLevelService
    ) {}

    public function __invoke(User $assigner, string $targetRole, ?User $target = null): array
    {
        $errors = [];
        $assignerRoles = $assigner->getRoleNames()->toArray();
        $assignerLevel = ($this->getHighestRoleLevelService)($assignerRoles);
        $targetRoleLevel = ($this->getRoleLevelService)($targetRole);
        
        // Check if assigner has authority to assign this role
        if ($targetRoleLevel >= $assignerLevel && !in_array('platform_admin', $assignerRoles)) {
            $errors[] = 'You do not have authority to assign this role';
        }
        
        // Additional validation if target user is specified
        if ($target) {
            $targetCurrentRoles = $target->getRoleNames()->toArray();
            $targetCurrentLevel = ($this->getHighestRoleLevelService)($targetCurrentRoles);
            
            // Check if assigner can manage target user
            if ($targetCurrentLevel >= $assignerLevel && !in_array('platform_admin', $assignerRoles)) {
                $errors[] = 'You do not have authority to modify this user\'s roles';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'assigner_level' => $assignerLevel,
            'target_role_level' => $targetRoleLevel
        ];
    }
}