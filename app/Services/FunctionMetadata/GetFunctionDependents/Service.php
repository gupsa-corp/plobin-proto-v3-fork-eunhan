<?php

namespace App\Services\FunctionMetadata\GetFunctionDependents;

use App\Services\FunctionMetadata\GetFunctions\Service as GetFunctionsService;

class Service
{
    protected $getFunctionsService;

    public function __construct(GetFunctionsService $getFunctionsService)
    {
        $this->getFunctionsService = $getFunctionsService;
    }

    /**
     * Get functions that depend on given function
     */
    public function __invoke(string $functionName): array
    {
        $functions = $this->getFunctionsService->__invoke();
        $dependents = [];

        foreach ($functions as $name => $function) {
            $dependencies = $function['dependencies'] ?? [];
            if (in_array($functionName, $dependencies)) {
                $dependents[] = $name;
            }
        }

        return $dependents;
    }
}