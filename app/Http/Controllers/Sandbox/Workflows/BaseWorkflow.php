<?php

namespace App\Http\Controllers\Sandbox\Workflows;

abstract class BaseWorkflow
{
    protected string $name;
    protected string $description;
    protected array $parameters = [];
    protected array $results = [];
    protected array $errors = [];

    /**
     * Execute the workflow
     */
    abstract public function execute(array $parameters = []): array;

    /**
     * Validate workflow parameters
     */
    protected function validate(array $parameters): bool
    {
        foreach ($this->parameters as $param => $rules) {
            if (isset($rules['required']) && $rules['required'] && !isset($parameters[$param])) {
                $this->errors[] = "Missing required parameter: {$param}";
                return false;
            }

            if (isset($parameters[$param]) && isset($rules['type'])) {
                $type = gettype($parameters[$param]);
                if ($type !== $rules['type']) {
                    $this->errors[] = "Invalid type for parameter {$param}: expected {$rules['type']}, got {$type}";
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get workflow name
     */
    public function getName(): string
    {
        return $this->name ?? class_basename($this);
    }

    /**
     * Get workflow description
     */
    public function getDescription(): string
    {
        return $this->description ?? '';
    }

    /**
     * Get workflow parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get workflow results
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Get workflow errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Clear errors
     */
    protected function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * Add error
     */
    protected function addError(string $error): void
    {
        $this->errors[] = $error;
    }
}