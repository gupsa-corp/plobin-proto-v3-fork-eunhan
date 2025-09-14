<?php

namespace App\Services\FunctionMetadata\GetStatistics;

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

    /**
     * Get functions statistics
     */
    public function __invoke(): array
    {
        $functionsFile = $this->basePath . '/metadata/functions.json';

        if (!File::exists($functionsFile)) {
            return [
                'total_functions' => 0,
                'total_versions' => 0,
                'last_updated' => null
            ];
        }

        $content = File::get($functionsFile);
        $data = json_decode($content, true);

        return $data['statistics'] ?? [];
    }
}