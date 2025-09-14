<?php

namespace App\Services\Sandbox\PageInfo;

use App\Models\ProjectPage;

class Service
{
    public function __invoke(ProjectPage $page): array
    {
        $project = $page->project;

        return [
            'has_sandbox' => !empty($project->sandbox_folder),
            'sandbox_name' => $project->sandbox_folder,
            'sandbox_level' => 'project',
            'has_custom_screen' => $page->custom_screen_enabled && !empty($page->sandbox_custom_screen_folder),
            'custom_screen_folder' => $page->sandbox_custom_screen_folder,
            'custom_screen_enabled' => $page->custom_screen_enabled,
            'template_path' => $page->template_path,
        ];
    }
}