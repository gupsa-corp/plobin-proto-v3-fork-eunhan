<?php

namespace App\Services\FunctionMetadata\InitializeMetadata;

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
     * Initialize metadata files if they don't exist
     */
    public function __invoke(): bool
    {
        try {
            $metadataDir = $this->basePath . '/metadata';

            if (!File::exists($metadataDir)) {
                File::makeDirectory($metadataDir, 0755, true);
            }

            $functionsFile = $metadataDir . '/functions.json';

            if (!File::exists($functionsFile)) {
                $initialData = [
                    'functions' => [],
                    'categories' => [],
                    'statistics' => [
                        'total_functions' => 0,
                        'total_versions' => 0,
                        'last_updated' => now()->toISOString()
                    ]
                ];

                File::put($functionsFile, json_encode($initialData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}