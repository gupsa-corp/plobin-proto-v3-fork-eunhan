<?php

namespace App\Services\AccessControl\CheckCustomRoleAccess;

use App\Models\User;
use App\Models\ProjectPage;

class Service
{
    public function __invoke(User $user, ProjectPage $page): bool
    {
        $allowedRoles = $page->allowed_roles ?? [];

        if (empty($allowedRoles)) {
            return false;
        }

        // 프로젝트 매니저(PM)인지 확인
        if ($page->project->user_id === $user->id) {
            return true;
        }

        foreach ($allowedRoles as $allowedItem) {
            // 이메일 주소로 체크
            if (filter_var($allowedItem, FILTER_VALIDATE_EMAIL)) {
                if ($user->email === $allowedItem) {
                    return true;
                }
            }
            // 사용자 ID로 체크
            elseif (is_numeric($allowedItem)) {
                if ($user->id == $allowedItem) {
                    return true;
                }
            }
        }

        // 기존 커스텀 역할 확인 (하위 호환성)
        $userCustomRoles = $user->roles()
            ->where('name', 'like', 'project_' . $page->project_id . '_%')
            ->pluck('id')
            ->toArray();

        return !empty(array_intersect($userCustomRoles, $allowedRoles));
    }
}