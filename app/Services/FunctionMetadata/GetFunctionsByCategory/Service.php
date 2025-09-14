<?php

namespace App\Services\FunctionMetadata\GetFunctionsByCategory;

use App\Services\FunctionMetadata\GetFunctions\Service as GetFunctionsService;

class Service
{
    protected $getFunctionsService;

    public function __construct(GetFunctionsService $getFunctionsService)
    {
        $this->getFunctionsService = $getFunctionsService;
    }

    /**
     * Get functions by category
     */
    public function __invoke(string $category): array
    {
        $functions = $this->getFunctionsService->__invoke();

        return array_filter($functions, function($function) use ($category) {
            return ($function['category'] ?? '') === $category;
        });
    }
}