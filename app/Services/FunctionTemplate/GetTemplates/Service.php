<?php

namespace App\Services\FunctionTemplate\GetTemplates;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class Service
{
    private string $basePath;

    public function __construct()
    {
        $currentStorage = Session::get('sandbox_storage', 'template');
        $this->basePath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . "/storage-sandbox-{$currentStorage}");
    }

    public function __invoke(): array
    {
        $templatesFile = $this->basePath . '/metadata/templates.json';

        if (!File::exists($templatesFile)) {
            return [];
        }

        $content = File::get($templatesFile);
        $data = json_decode($content, true);

        return $data['templates'] ?? [];
    }
}