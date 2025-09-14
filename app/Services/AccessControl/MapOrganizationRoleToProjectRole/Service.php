<?php

namespace App\Services\AccessControl\MapOrganizationRoleToProjectRole;

use App\Enums\ProjectRole;

class Service
{
    public function __invoke(?string $organizationRole): ProjectRole
    {
        if (!$organizationRole) {
            return ProjectRole::GUEST;
        }

        // 조직 역할을 프로젝트 역할로 매핑
        return match($organizationRole) {
            'admin', 'owner' => ProjectRole::ADMIN,
            'moderator' => ProjectRole::MODERATOR,
            'contributor' => ProjectRole::CONTRIBUTOR,
            'member' => ProjectRole::MEMBER,
            default => ProjectRole::GUEST,
        };
    }
}