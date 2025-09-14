<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class FunctionTemplateService
{
    private string $basePath;
    private string $currentStorage;

    public function __construct()
    {
        $this->currentStorage = Session::get('sandbox_storage', 'template');
        $this->basePath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . "/storage-sandbox-{$this->currentStorage}");
    }

    /**
     * Get all available templates
     */
    public function getTemplates(): array
    {
        $templatesFile = $this->basePath . '/metadata/templates.json';

        if (!File::exists($templatesFile)) {
            return [];
        }

        $content = File::get($templatesFile);
        $data = json_decode($content, true);

        return $data['templates'] ?? [];
    }

    /**
     * Get template categories
     */
    public function getCategories(): array
    {
        $templatesFile = $this->basePath . '/metadata/templates.json';

        if (!File::exists($templatesFile)) {
            return [];
        }

        $content = File::get($templatesFile);
        $data = json_decode($content, true);

        return $data['categories'] ?? [];
    }

    /**
     * Get specific template by ID
     */
    public function getTemplate(string $templateId): ?array
    {
        $templates = $this->getTemplates();
        return $templates[$templateId] ?? null;
    }

    /**
     * Get template content (PHP code)
     */
    public function getTemplateContent(string $templateId): ?string
    {
        $template = $this->getTemplate($templateId);

        if (!$template || !isset($template['file'])) {
            return null;
        }

        $templateFile = $this->basePath . '/templates/' . $template['file'];

        if (!File::exists($templateFile)) {
            return null;
        }

        return File::get($templateFile);
    }

    /**
     * Process template with parameters
     */
    public function processTemplate(string $templateId, array $parameters): ?string
    {
        $content = $this->getTemplateContent($templateId);

        if (!$content) {
            return null;
        }

        // Process array parameters first (they need special handling)
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $content = $this->processArrayParameter($content, $key, $value);
            }
        }

        // Then process simple string replacements
        foreach ($parameters as $key => $value) {
            if (!is_array($value)) {
                $content = str_replace('{{' . $key . '}}', $value, $content);
            }
        }

        // Clean up any remaining template variables
        $content = preg_replace('/\{\{[^}]+\}\}/', '', $content);

        return $content;
    }

    /**
     * Process array parameters (like actions for API template)
     */
    private function processArrayParameter(string $content, string $key, array $value): string
    {
        // Handle {{#each actions}} blocks with more aggressive pattern matching
        $pattern = '/\{\{#each\s+' . preg_quote($key) . '\s*\}\}(.*?)\{\{\/each\}\}/s';

        if (preg_match($pattern, $content, $matches)) {
            $template = $matches[1];
            $result = '';

            foreach ($value as $item) {
                $itemContent = $template;
                $itemContent = str_replace('{{this}}', $item, $itemContent);
                $itemContent = str_replace('{{capitalize this}}', ucfirst($item), $itemContent);
                $result .= $itemContent;
            }

            $content = str_replace($matches[0], $result, $content);
        }

        // Handle actionsList placeholder
        if ($key === 'actions') {
            $actionsList = implode(', ', $value);
            $content = str_replace('{{actionsList}}', $actionsList, $content);
        }

        return $content;
    }

    /**
     * Create new function from template
     */
    public function createFunction(string $functionName, string $templateId, array $parameters): array
    {
        try {
            // Validate function name
            if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $functionName)) {
                throw new \Exception('Invalid function name. Use only letters, numbers, and underscores, starting with a letter.');
            }

            // Check if function already exists
            $functionPath = $this->basePath . '/functions/' . $functionName;
            if (File::exists($functionPath)) {
                throw new \Exception('Function with this name already exists.');
            }

            // Process template
            $parameters['className'] = $functionName;
            $processedContent = $this->processTemplate($templateId, $parameters);

            if (!$processedContent) {
                throw new \Exception('Failed to process template.');
            }

            // Create function directory structure
            $releasePath = $functionPath . '/release';
            File::makeDirectory($releasePath, 0755, true);

            // Create Function.php file
            File::put($releasePath . '/Function.php', $processedContent);

            // Update functions metadata
            $this->updateFunctionsMetadata($functionName, $templateId, $parameters);

            return [
                'success' => true,
                'message' => "Function '$functionName' created successfully",
                'path' => $releasePath,
                'function_name' => $functionName
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating function: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update functions metadata after creating new function
     */
    private function updateFunctionsMetadata(string $functionName, string $templateId, array $parameters): void
    {
        $functionsFile = $this->basePath . '/metadata/functions.json';

        // Load existing metadata
        $data = [];
        if (File::exists($functionsFile)) {
            $content = File::get($functionsFile);
            $data = json_decode($content, true) ?? [];
        }

        // Initialize structure if not exists
        if (!isset($data['functions'])) {
            $data['functions'] = [];
        }
        if (!isset($data['statistics'])) {
            $data['statistics'] = [
                'total_functions' => 0,
                'total_versions' => 0
            ];
        }

        // Get template info
        $template = $this->getTemplate($templateId);

        // Add new function
        $data['functions'][$functionName] = [
            'versions' => ['release'],
            'description' => $parameters['description'] ?? ($template['description'] ?? 'Generated from template'),
            'dependencies' => $template['dependencies'] ?? [],
            'parameters' => $this->generateParameterSchema($template, $parameters),
            'category' => $template['category'] ?? 'generated',
            'template_used' => $templateId,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
            'author' => 'user',
            'tags' => $template['tags'] ?? []
        ];

        // Update statistics
        $data['statistics']['total_functions'] = count($data['functions']);
        $data['statistics']['total_versions'] = array_sum(array_map(function($func) {
            return count($func['versions']);
        }, $data['functions']));
        $data['statistics']['last_updated'] = now()->toISOString();

        // Save updated metadata
        File::put($functionsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Generate parameter schema for the function
     */
    private function generateParameterSchema(array $template, array $parameters): array
    {
        $schema = [];

        // Basic parameters based on template type
        switch ($template['category']) {
            case 'api':
                $schema['action'] = [
                    'type' => 'string',
                    'required' => true,
                    'description' => '수행할 액션',
                    'options' => $parameters['actions'] ?? []
                ];
                break;

            case 'data':
                $schema['data'] = [
                    'type' => 'array',
                    'required' => true,
                    'description' => '처리할 데이터'
                ];
                $schema['operation'] = [
                    'type' => 'string',
                    'required' => false,
                    'description' => '데이터 처리 작업',
                    'options' => ['process', 'filter', 'transform', 'aggregate', 'validate']
                ];
                break;

            case 'utility':
                $schema['operation'] = [
                    'type' => 'string',
                    'required' => false,
                    'description' => '유틸리티 작업',
                    'options' => ['main', 'format', 'convert', 'calculate', 'generate']
                ];
                break;
        }

        return $schema;
    }

    /**
     * Validate template parameters
     */
    public function validateParameters(string $templateId, array $parameters): array
    {
        $template = $this->getTemplate($templateId);

        if (!$template) {
            return [
                'valid' => false,
                'errors' => ['Template not found']
            ];
        }

        $errors = [];
        $templateParams = $template['parameters'] ?? [];

        foreach ($templateParams as $param) {
            $name = $param['name'];
            $required = $param['required'] ?? false;

            if ($required && !isset($parameters[$name])) {
                $errors[] = "Required parameter '{$name}' is missing";
            }

            if (isset($parameters[$name]) && $param['type'] === 'array' && !is_array($parameters[$name])) {
                $errors[] = "Parameter '{$name}' must be an array";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
