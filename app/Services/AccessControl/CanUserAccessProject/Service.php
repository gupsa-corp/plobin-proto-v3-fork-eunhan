<?php

namespace App\Services\AccessControl\CanUserAccessProject;

use App\Models\User;
use App\Models\Project;
use App\Enums\PageAccessLevel;

class Service
{
    public function __invoke(User $user, Project $project): bool
    {
        // 프로젝트 소유자는 항상 접근 가능
        if ($project->user_id === $user->id) {
            return true;
        }

        // 사용자의 프로젝트 내 역할 확인
        $userRole = app(\App\Services\AccessControl\GetUserProjectRole\Service::class)($user, $project);

        // 프로젝트 기본 접근 레벨 확인
        $projectAccessLevel = PageAccessLevel::from($project->default_access_level ?? 'member');

        return $projectAccessLevel->canRoleAccess($userRole);
    }
}