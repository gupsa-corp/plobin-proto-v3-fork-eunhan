<?php

namespace App\Services\FunctionMetadata\DeleteFunction;

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
     * Delete function metadata
     */
    public function __invoke(string $functionName): bool
    {
        try {
            $functionsFile = $this->basePath . '/metadata/functions.json';

            if (!File::exists($functionsFile)) {
                return false;
            }

            $content = File::get($functionsFile);
            $data = json_decode($content, true) ?? [];

            if (isset($data['functions'][$functionName])) {
                unset($data['functions'][$functionName]);

                // Update statistics
                $this->updateStatistics($data);

                // Save
                File::put($functionsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                return true;
            }

            return false;
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