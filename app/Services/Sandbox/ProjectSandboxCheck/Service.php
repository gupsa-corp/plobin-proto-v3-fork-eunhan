<?php

namespace App\Services\Sandbox\ProjectSandboxCheck;

use App\Models\Project;

class Service
{
    public function __invoke(Project $project): bool
    {
        return !empty($project->sandbox_folder);
    }
}