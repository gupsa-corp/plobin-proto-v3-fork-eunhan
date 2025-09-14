<?php

namespace App\Services\FunctionMetadata\GetFunctionDependencies;

use App\Services\FunctionMetadata\GetFunction\Service as GetFunctionService;

class Service
{
    protected $getFunctionService;

    public function __construct(GetFunctionService $getFunctionService)
    {
        $this->getFunctionService = $getFunctionService;
    }

    /**
     * Get function dependencies
     */
    public function __invoke(string $functionName): array
    {
        $function = $this->getFunctionService->__invoke($functionName);
        return $function['dependencies'] ?? [];
    }
}