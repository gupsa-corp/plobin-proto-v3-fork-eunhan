<?php

namespace App\Services\RoleHierarchy\GenerateAuditTrail;

use App\Models\User;
use App\Services\RoleHierarchy\GetRoleLevel\Service as GetRoleLevelService;
use App\Services\RoleHierarchy\ValidateRoleAssignment\Service as ValidateRoleAssignmentService;

class Service
{
    public function __construct(
        private GetRoleLevelService $getRoleLevelService,
        private ValidateRoleAssignmentService $validateRoleAssignmentService
    ) {}

    public function __invoke(User $assigner, User $target, string $oldRole, string $newRole): array
    {
        return [
            'action' => 'role_assignment',
            'assigner_id' => $assigner->id,
            'assigner_name' => $assigner->name,
            'assigner_roles' => $assigner->getRoleNames()->toArray(),
            'target_id' => $target->id,
            'target_name' => $target->name,
            'old_role' => $oldRole,
            'new_role' => $newRole,
            'old_role_level' => ($this->getRoleLevelService)($oldRole),
            'new_role_level' => ($this->getRoleLevelService)($newRole),
            'authority_check' => ($this->validateRoleAssignmentService)($assigner, $newRole, $target),
            'timestamp' => now()->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];
    }
}