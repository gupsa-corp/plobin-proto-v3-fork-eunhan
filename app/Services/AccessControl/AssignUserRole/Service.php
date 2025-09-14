<?php

namespace App\Services\AccessControl\AssignUserRole;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMemberRole;
use App\Enums\ProjectRole;

class Service
{
    public function __invoke(User $user, Project $project, ProjectRole $role): void
    {
        ProjectMemberRole::updateOrCreate(
            [
                'project_id' => $project->id,
                'user_id' => $user->id,
            ],
            [
                'role' => $role->value,
            ]
        );
    }
}