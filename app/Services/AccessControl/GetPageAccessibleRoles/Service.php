<?php

namespace App\Services\AccessControl\GetPageAccessibleRoles;

use App\Models\ProjectPage;
use App\Enums\PageAccessLevel;
use App\Enums\ProjectRole;

class Service
{
    public function __invoke(ProjectPage $page): array
    {
        $accessLevel = $page->getEffectivePageAccessLevel();

        if ($accessLevel === PageAccessLevel::CUSTOM) {
            return $page->allowed_roles ?? [];
        }

        $roles = [];
        foreach (ProjectRole::getAllInOrder() as $role) {
            if ($accessLevel->canRoleAccess($role)) {
                $roles[] = $role->value;
            }
        }

        return $roles;
    }
}