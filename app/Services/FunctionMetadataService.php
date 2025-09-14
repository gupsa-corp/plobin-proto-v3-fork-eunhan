<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class FunctionMetadataService
{
    private string $basePath;
    private string $currentStorage;

    public function __construct()
    {
        $this->currentStorage = Session::get('sandbox_storage', 'template');
        $this->basePath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . "/storage-sandbox-{$this->currentStorage}");
    }

    /**
     * Get all functions metadata
     */
    public function getFunctions(): array
    {
        $functionsFile = $this->basePath . '/metadata/functions.json';

        if (!File::exists($functionsFile)) {
            return [];
        }

        $content = File::get($functionsFile);
        $data = json_decode($content, true);

        return $data['functions'] ?? [];
    }

    /**
     * Get specific function metadata
     */
    public function getFunction(string $functionName): ?array
    {
        $functions = $this->getFunctions();
        return $functions[$functionName] ?? null;
    }

    /**
     * Get functions statistics
     */
    public function getStatistics(): array
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

    /**
     * Update function metadata
     */
    public function updateFunction(string $functionName, array $metadata): bool
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
     * Delete function metadata
     */
    public function deleteFunction(string $functionName): bool
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
     * Add new version to function
     */
    public function addVersion(string $functionName, string $version): bool
    {
        try {
            $function = $this->getFunction($functionName);

            if (!$function) {
                return false;
            }

            $versions = $function['versions'] ?? [];

            if (!in_array($version, $versions)) {
                $versions[] = $version;

                // Sort versions (release first, then by name)
                usort($versions, function($a, $b) {
                    if ($a === 'release') return -1;
                    if ($b === 'release') return 1;
                    return strcmp($b, $a);
                });

                return $this->updateFunction($functionName, ['versions' => $versions]);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get functions by category
     */
    public function getFunctionsByCategory(string $category): array
    {
        $functions = $this->getFunctions();

        return array_filter($functions, function($function) use ($category) {
            return ($function['category'] ?? '') === $category;
        });
    }

    /**
     * Search functions by term
     */
    public function searchFunctions(string $term): array
    {
        $functions = $this->getFunctions();
        $results = [];
        $term = strtolower($term);

        foreach ($functions as $name => $function) {
            $searchable = strtolower($name . ' ' . ($function['description'] ?? '') . ' ' . implode(' ', $function['tags'] ?? []));

            if (strpos($searchable, $term) !== false) {
                $results[$name] = $function;
            }
        }

        return $results;
    }

    /**
     * Get function dependencies
     */
    public function getFunctionDependencies(string $functionName): array
    {
        $function = $this->getFunction($functionName);
        return $function['dependencies'] ?? [];
    }

    /**
     * Get functions that depend on given function
     */
    public function getFunctionDependents(string $functionName): array
    {
        $functions = $this->getFunctions();
        $dependents = [];

        foreach ($functions as $name => $function) {
            $dependencies = $function['dependencies'] ?? [];
            if (in_array($functionName, $dependencies)) {
                $dependents[] = $name;
            }
        }

        return $dependents;
    }

    /**
     * Check for circular dependencies
     */
    public function hasCircularDependency(string $functionName, array $dependencies, array $visited = []): bool
    {
        if (in_array($functionName, $visited)) {
            return true; // Circular dependency found
        }

        $visited[] = $functionName;

        foreach ($dependencies as $dependency) {
            $dependencyFunction = $this->getFunction($dependency);
            if ($dependencyFunction) {
                $subDependencies = $dependencyFunction['dependencies'] ?? [];
                if ($this->hasCircularDependency($dependency, $subDependencies, $visited)) {
                    return true;
                }
            }
        }

        return false;
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

    /**
     * Initialize metadata files if they don't exist
     */
    public function initializeMetadata(): bool
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
