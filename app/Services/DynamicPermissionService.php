<?php

namespace App\Services;

use App\Models\DynamicPermissionRule;
use App\Services\DynamicPermission\CanPerformAction\Service as CanPerformActionService;
use App\Services\DynamicPermission\CanPerformPublicAction\Service as CanPerformPublicActionService;
use App\Services\DynamicPermission\AssignBasicPermissions\Service as AssignBasicPermissionsService;
use App\Services\DynamicPermission\ClearCache\Service as ClearCacheService;
use App\Services\DynamicPermission\GetUserPermissionSummary\Service as GetUserPermissionSummaryService;
use App\Services\DynamicPermission\CreateRule\Service as CreateRuleService;
use App\Services\DynamicPermission\UpdateRule\Service as UpdateRuleService;
use App\Services\DynamicPermission\DeleteRule\Service as DeleteRuleService;
use App\Services\DynamicPermission\GetPermissionMatrix\Service as GetPermissionMatrixService;
use App\Services\DynamicPermission\GetRoleFeatures\Service as GetRoleFeaturesService;
use App\Services\DynamicPermission\GetPermissionFeatures\Service as GetPermissionFeaturesService;

class DynamicPermissionService
{
    protected $canPerformActionService;
    protected $canPerformPublicActionService;
    protected $assignBasicPermissionsService;
    protected $clearCacheService;
    protected $getUserPermissionSummaryService;
    protected $createRuleService;
    protected $updateRuleService;
    protected $deleteRuleService;
    protected $getPermissionMatrixService;
    protected $getRoleFeaturesService;
    protected $getPermissionFeaturesService;

    public function __construct(
        CanPerformActionService $canPerformActionService,
        CanPerformPublicActionService $canPerformPublicActionService,
        AssignBasicPermissionsService $assignBasicPermissionsService,
        ClearCacheService $clearCacheService,
        GetUserPermissionSummaryService $getUserPermissionSummaryService,
        CreateRuleService $createRuleService,
        UpdateRuleService $updateRuleService,
        DeleteRuleService $deleteRuleService,
        GetPermissionMatrixService $getPermissionMatrixService,
        GetRoleFeaturesService $getRoleFeaturesService,
        GetPermissionFeaturesService $getPermissionFeaturesService
    ) {
        $this->canPerformActionService = $canPerformActionService;
        $this->canPerformPublicActionService = $canPerformPublicActionService;
        $this->assignBasicPermissionsService = $assignBasicPermissionsService;
        $this->clearCacheService = $clearCacheService;
        $this->getUserPermissionSummaryService = $getUserPermissionSummaryService;
        $this->createRuleService = $createRuleService;
        $this->updateRuleService = $updateRuleService;
        $this->deleteRuleService = $deleteRuleService;
        $this->getPermissionMatrixService = $getPermissionMatrixService;
        $this->getRoleFeaturesService = $getRoleFeaturesService;
        $this->getPermissionFeaturesService = $getPermissionFeaturesService;
    }

    /**
     * 사용자가 특정 리소스에 대한 액션을 수행할 수 있는지 확인
     */
    public function canPerformAction($user, string $resourceType, string $action, array $context = []): bool
    {
        return $this->canPerformActionService->__invoke($user, $resourceType, $action, $context);
    }

    /**
     * 게스트(비로그인) 사용자가 공개 액션을 수행할 수 있는지 확인
     */
    public function canPerformPublicAction(string $action, array $context = []): bool
    {
        return $this->canPerformPublicActionService->__invoke($action, $context);
    }

    /**
     * 사용자에게 기본 역할과 권한을 할당 (기존 enum 시스템과의 호환성)
     * role_name을 이용한 기본 권한 할당 (역할 기반 시스템)
     */
    public function assignBasicPermissionsByRole($user, string $roleName)
    {
        // 역할 할당
        $user->assignRole($roleName);

        // 역할에 따른 기본 권한 할당
        $defaultPermissions = $this->getDefaultPermissionsForRole($roleName);

        foreach ($defaultPermissions as $permission) {
            $user->givePermissionTo($permission);
        }

        return $user->getRoleNames()->toArray();
    }
    
    /**
     * 역할별 기본 권한 반환
     */
    private function getDefaultPermissionsForRole(string $roleName): array
    {
        return match($roleName) {
            'user' => [
                'view dashboard',
                'view projects'
            ],
            'service_manager' => [
                'view dashboard',
                'view projects',
                'manage projects',
                'view members'
            ],
            'organization_admin' => [
                'view dashboard',
                'view projects',
                'manage projects',
                'view members',
                'manage members',
                'manage settings'
            ],
            'organization_owner' => [
                'view dashboard',
                'view projects',
                'manage projects',
                'view members',
                'manage members',
                'manage settings',
                'manage billing',
                'manage organization'
            ],
            'platform_admin' => [
                'view dashboard',
                'manage platform',
                'manage all organizations',
                'manage all users'
            ],
            default => []
        };
    }

    /**
     * 권한 규칙 캐시 클리어
     */
    public function clearCache(): void
    {
        $this->clearCacheService->__invoke();
    }

    /**
     * 사용자별 권한 요약 정보 반환
     */
    public function getUserPermissionSummary($user): array
    {
        return $this->getUserPermissionSummaryService->__invoke($user);
    }

    /**
     * 권한 규칙 생성 도우미
     */
    public function createRule(array $data): DynamicPermissionRule
    {
        return $this->createRuleService->__invoke($data);
    }

    /**
     * 권한 규칙 업데이트 도우미
     */
    public function updateRule(DynamicPermissionRule $rule, array $data): bool
    {
        return $this->updateRuleService->__invoke($rule, $data);
    }

    /**
     * 권한 규칙 삭제 도우미
     */
    public function deleteRule(DynamicPermissionRule $rule): bool
    {
        return $this->deleteRuleService->__invoke($rule);
    }

    /**
     * 권한 매트릭스 반환
     */
    public function getPermissionMatrix(): array
    {
        return $this->getPermissionMatrixService->__invoke();
    }

    /**
     * 역할의 사용 가능한 기능 목록 반환
     */
    public function getRoleFeatures(string $roleName): array
    {
        return $this->getRoleFeaturesService->__invoke($roleName);
    }

    /**
     * 권한의 사용 가능한 기능 목록 반환
     */
    public function getPermissionFeatures(string $permissionName): array
    {
        return $this->getPermissionFeaturesService->__invoke($permissionName);
    }
}