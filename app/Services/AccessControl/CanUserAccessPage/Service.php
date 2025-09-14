<?php

namespace App\Services\AccessControl\CanUserAccessPage;

use App\Models\User;
use App\Models\ProjectPage;
use App\Enums\PageAccessLevel;

class Service
{
    public function __invoke(User $user, ProjectPage $page): bool
    {
        $project = $page->project;

        // 프로젝트 소유자는 항상 접근 가능
        if ($project->user_id === $user->id) {
            return true;
        }

        // 사용자의 프로젝트 내 역할 확인
        $userRole = app(\App\Services\AccessControl\GetUserProjectRole\Service::class)($user, $project);

        // 페이지의 접근 레벨 확인
        $pageAccessLevel = $page->getEffectivePageAccessLevel();

        // 커스텀 역할인 경우
        if ($pageAccessLevel === PageAccessLevel::CUSTOM) {
            return app(\App\Services\AccessControl\CheckCustomRoleAccess\Service::class)($user, $page);
        }

        // 표준 역할 기반 접근 확인
        return $pageAccessLevel->canRoleAccess($userRole);
    }
}