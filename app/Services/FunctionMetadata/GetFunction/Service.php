<?php

namespace App\Services\FunctionMetadata\GetFunction;

use App\Services\FunctionMetadata\GetFunctions\Service as GetFunctionsService;

class Service
{
    protected $getFunctionsService;

    public function __construct(GetFunctionsService $getFunctionsService)
    {
        $this->getFunctionsService = $getFunctionsService;
    }

    /**
     * Get specific function metadata
     */
    public function __invoke(string $functionName): ?array
    {
        $functions = $this->getFunctionsService->__invoke();
        return $functions[$functionName] ?? null;
    }
}