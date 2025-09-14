<?php

namespace App\Services;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectPage;
use App\Enums\ProjectRole;

class AccessControlService
{

    /**
     * 사용자가 페이지에 접근할 수 있는지 확인
     */
    public function canUserAccessPage(User $user, ProjectPage $page): bool
    {
        return app(\App\Services\AccessControl\CanUserAccessPage\Service::class)($user, $page);
    }

    /**
     * 사용자의 프로젝트 내 역할 조회
     */
    public function getUserProjectRole(User $user, Project $project): ProjectRole
    {
        return app(\App\Services\AccessControl\GetUserProjectRole\Service::class)($user, $project);
    }

    /**
     * 조직 역할을 프로젝트 역할로 매핑
     */
    private function mapOrganizationRoleToProjectRole(?string $organizationRole): ProjectRole
    {
        return app(\App\Services\AccessControl\MapOrganizationRoleToProjectRole\Service::class)($organizationRole);
    }

    /**
     * 커스텀 역할 접근 권한 확인
     */
    private function checkCustomRoleAccess(User $user, ProjectPage $page): bool
    {
        return app(\App\Services\AccessControl\CheckCustomRoleAccess\Service::class)($user, $page);
    }

    /**
     * 사용자가 프로젝트에 접근할 수 있는지 확인
     */
    public function canUserAccessProject(User $user, Project $project): bool
    {
        return app(\App\Services\AccessControl\CanUserAccessProject\Service::class)($user, $project);
    }

    /**
     * 사용자에게 프로젝트 내 역할 할당
     */
    public function assignUserRole(User $user, Project $project, ProjectRole $role): void
    {
        app(\App\Services\AccessControl\AssignUserRole\Service::class)($user, $project, $role);
    }

    /**
     * 사용자의 프로젝트 내 역할 제거
     */
    public function removeUserRole(User $user, Project $project): void
    {
        app(\App\Services\AccessControl\RemoveUserRole\Service::class)($user, $project);
    }

    /**
     * 프로젝트의 모든 멤버와 역할 조회
     */
    public function getProjectMembers(Project $project): array
    {
        return app(\App\Services\AccessControl\GetProjectMembers\Service::class)($project);
    }

    /**
     * 페이지에 접근 가능한 역할 목록 조회
     */
    public function getPageAccessibleRoles(ProjectPage $page): array
    {
        return app(\App\Services\AccessControl\GetPageAccessibleRoles\Service::class)($page);
    }

    /**
     * 프로젝트의 페이지별 접근 권한 요약 조회
     */
    public function getProjectPagesAccessSummary(Project $project): array
    {
        return app(\App\Services\AccessControl\GetProjectPagesAccessSummary\Service::class)($project);
    }
}