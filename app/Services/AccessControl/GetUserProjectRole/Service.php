<?php

namespace App\Services\AccessControl\GetUserProjectRole;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMemberRole;
use App\Models\OrganizationMember;
use App\Enums\ProjectRole;

class Service
{
    public function __invoke(User $user, Project $project): ProjectRole
    {
        // 프로젝트 소유자 확인
        if ($project->user_id === $user->id) {
            return ProjectRole::OWNER;
        }

        // 프로젝트별 역할 확인
        $projectRole = ProjectMemberRole::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->first();

        if ($projectRole) {
            return ProjectRole::from($projectRole->role);
        }

        // 조직 멤버십을 통한 기본 역할 확인
        $organizationMember = OrganizationMember::where('organization_id', $project->organization_id)
            ->where('user_id', $user->id)
            ->where('invitation_status', 'accepted')
            ->first();

        if ($organizationMember) {
            // 조직 역할을 프로젝트 역할로 매핑
            return app(\App\Services\AccessControl\MapOrganizationRoleToProjectRole\Service::class)($organizationMember->role_name);
        }

        // 기본값은 게스트
        return ProjectRole::GUEST;
    }
}