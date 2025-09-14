<?php

namespace App\Services\FunctionMetadata\UpdateFunction;

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
     * Update function metadata
     */
    public function __invoke(string $functionName, array $metadata): bool
    {
        try {
            $functionsFile = $this->basePath . '/metadata/functions.json';

            // Load existing data
            $data = [];
            if (File::exists($functionsFile)) {
                $content = File::get($functionsFile);
                $data = json_decode($content, true) ?? [];
            }

            // Initialize if not exists
            if (!isset($data['functions'])) {
                $data['functions'] = [];
            }

            // Update function metadata
            if (isset($data['functions'][$functionName])) {
                $data['functions'][$functionName] = array_merge(
                    $data['functions'][$functionName],
                    $metadata
                );
                $data['functions'][$functionName]['updated_at'] = now()->toISOString();
            } else {
                $data['functions'][$functionName] = array_merge($metadata, [
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString()
                ]);
            }

            // Update statistics
            $this->updateStatistics($data);

            // Save
            File::put($functionsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update statistics in data array
     */
    private function updateStatistics(array &$data): void
    {
        $data['statistics'] = [
            'total_functions' => count($data['functions'] ?? []),
            'total_versions' => array_sum(array_map(function($func) {
                return count($func['versions'] ?? []);
            }, $data['functions'] ?? [])),
            'last_updated' => now()->toISOString()
        ];
    }
}