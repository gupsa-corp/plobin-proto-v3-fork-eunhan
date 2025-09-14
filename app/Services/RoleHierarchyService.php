<?php

namespace App\Services;

use App\Models\User;
use App\Services\RoleHierarchy\GetRoleLevel\Service as GetRoleLevelService;
use App\Services\RoleHierarchy\HasHigherAuthority\Service as HasHigherAuthorityService;
use App\Services\RoleHierarchy\GetSubordinateRoles\Service as GetSubordinateRolesService;
use App\Services\RoleHierarchy\GetSuperiorRoles\Service as GetSuperiorRolesService;
use App\Services\RoleHierarchy\CanManageUser\Service as CanManageUserService;
use App\Services\RoleHierarchy\GetHighestRoleLevel\Service as GetHighestRoleLevelService;
use App\Services\RoleHierarchy\GetAssignableRoles\Service as GetAssignableRolesService;
use App\Services\RoleHierarchy\ValidateRoleAssignment\Service as ValidateRoleAssignmentService;
use App\Services\RoleHierarchy\GetHierarchyVisualization\Service as GetHierarchyVisualizationService;
use App\Services\RoleHierarchy\SuggestRoleForPermissions\Service as SuggestRoleForPermissionsService;
use App\Services\RoleHierarchy\GetRoleInheritanceChain\Service as GetRoleInheritanceChainService;
use App\Services\RoleHierarchy\HasCircularDependency\Service as HasCircularDependencyService;
use App\Services\RoleHierarchy\GetEffectivePermissions\Service as GetEffectivePermissionsService;
use App\Services\RoleHierarchy\GenerateAuditTrail\Service as GenerateAuditTrailService;

class RoleHierarchyService
{
    public function __construct(
        private GetRoleLevelService $getRoleLevelService,
        private HasHigherAuthorityService $hasHigherAuthorityService,
        private GetSubordinateRolesService $getSubordinateRolesService,
        private GetSuperiorRolesService $getSuperiorRolesService,
        private CanManageUserService $canManageUserService,
        private GetHighestRoleLevelService $getHighestRoleLevelService,
        private GetAssignableRolesService $getAssignableRolesService,
        private ValidateRoleAssignmentService $validateRoleAssignmentService,
        private GetHierarchyVisualizationService $getHierarchyVisualizationService,
        private SuggestRoleForPermissionsService $suggestRoleForPermissionsService,
        private GetRoleInheritanceChainService $getRoleInheritanceChainService,
        private HasCircularDependencyService $hasCircularDependencyService,
        private GetEffectivePermissionsService $getEffectivePermissionsService,
        private GenerateAuditTrailService $generateAuditTrailService
    ) {}

    /**
     * Get role hierarchy level
     */
    public function getRoleLevel(string $roleName): int
    {
        return ($this->getRoleLevelService)($roleName);
    }

    /**
     * Check if role A has higher authority than role B
     */
    public function hasHigherAuthority(string $roleA, string $roleB): bool
    {
        return ($this->hasHigherAuthorityService)($roleA, $roleB);
    }

    /**
     * Get all roles that are subordinate to given role
     */
    public function getSubordinateRoles(string $roleName): array
    {
        return ($this->getSubordinateRolesService)($roleName);
    }

    /**
     * Get all roles that have authority over given role
     */
    public function getSuperiorRoles(string $roleName): array
    {
        return ($this->getSuperiorRolesService)($roleName);
    }

    /**
     * Check if user can manage another user based on role hierarchy
     */
    public function canManageUser(User $manager, User $target): bool
    {
        return ($this->canManageUserService)($manager, $target);
    }

    /**
     * Get highest role level from array of role names
     */
    protected function getHighestRoleLevel(array $roleNames): int
    {
        return ($this->getHighestRoleLevelService)($roleNames);
    }

    /**
     * Get roles that a user can assign to others
     */
    public function getAssignableRoles(User $user): array
    {
        return ($this->getAssignableRolesService)($user);
    }

    /**
     * Validate role assignment
     */
    public function validateRoleAssignment(User $assigner, string $targetRole, ?User $target = null): array
    {
        return ($this->validateRoleAssignmentService)($assigner, $targetRole, $target);
    }

    /**
     * Get role hierarchy visualization data
     */
    public function getHierarchyVisualization(): array
    {
        return ($this->getHierarchyVisualizationService)();
    }

    /**
     * Suggest role based on permissions
     */
    public function suggestRoleForPermissions(array $permissionNames): ?string
    {
        return ($this->suggestRoleForPermissionsService)($permissionNames);
    }

    /**
     * Get inheritance chain for role
     */
    public function getRoleInheritanceChain(string $roleName): array
    {
        return ($this->getRoleInheritanceChainService)($roleName);
    }

    /**
     * Check for circular dependencies in role assignments
     */
    public function hasCircularDependency(string $role, array $inheritedRoles): bool
    {
        return ($this->hasCircularDependencyService)($role, $inheritedRoles);
    }

    /**
     * Get effective permissions for role considering hierarchy
     */
    public function getEffectivePermissions(string $roleName): array
    {
        return ($this->getEffectivePermissionsService)($roleName);
    }

    /**
     * Generate role assignment audit trail
     */
    public function generateAuditTrail(User $assigner, User $target, string $oldRole, string $newRole): array
    {
        return ($this->generateAuditTrailService)($assigner, $target, $oldRole, $newRole);
    }
}