<?php

namespace App\Services\AccessControl\GetProjectMembers;

use App\Models\Project;
use App\Models\OrganizationMember;

class Service
{
    public function __invoke(Project $project): array
    {
        $members = [];

        // 조직 멤버들 조회
        $organizationMembers = OrganizationMember::with('user')
            ->where('organization_id', $project->organization_id)
            ->where('invitation_status', 'accepted')
            ->get();

        foreach ($organizationMembers as $orgMember) {
            $projectRole = app(\App\Services\AccessControl\GetUserProjectRole\Service::class)($orgMember->user, $project);

            $members[] = [
                'user' => $orgMember->user,
                'role' => $projectRole,
                'is_owner' => $project->user_id === $orgMember->user->id,
                'organization_role' => $orgMember->role_name,
            ];
        }

        return $members;
    }
}