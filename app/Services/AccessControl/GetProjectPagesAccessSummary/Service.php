<?php

namespace App\Services\AccessControl\GetProjectPagesAccessSummary;

use App\Models\Project;
use App\Enums\PageAccessLevel;

class Service
{
    public function __invoke(Project $project): array
    {
        $pages = $project->pages()->get();
        $summary = [];

        foreach ($pages as $page) {
            $accessLevel = $page->getEffectivePageAccessLevel();
            $accessibleRoles = app(\App\Services\AccessControl\GetPageAccessibleRoles\Service::class)($page);

            $summary[] = [
                'page' => $page,
                'access_level' => $accessLevel,
                'accessible_roles' => $accessibleRoles,
                'is_restricted' => $accessLevel !== PageAccessLevel::PUBLIC,
            ];
        }

        return $summary;
    }
}