<?php

namespace App\Services\AccessControl\RemoveUserRole;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMemberRole;

class Service
{
    public function __invoke(User $user, Project $project): void
    {
        ProjectMemberRole::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->delete();
    }
}