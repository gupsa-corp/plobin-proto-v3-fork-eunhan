<?php

namespace App\Services\FunctionMetadata\HasCircularDependency;

use App\Services\FunctionMetadata\GetFunction\Service as GetFunctionService;

class Service
{
    protected $getFunctionService;

    public function __construct(GetFunctionService $getFunctionService)
    {
        $this->getFunctionService = $getFunctionService;
    }

    /**
     * Check for circular dependencies
     */
    public function __invoke(string $functionName, array $dependencies, array $visited = []): bool
    {
        if (in_array($functionName, $visited)) {
            return true; // Circular dependency found
        }

        $visited[] = $functionName;

        foreach ($dependencies as $dependency) {
            $dependencyFunction = $this->getFunctionService->__invoke($dependency);
            if ($dependencyFunction) {
                $subDependencies = $dependencyFunction['dependencies'] ?? [];
                if ($this->__invoke($dependency, $subDependencies, $visited)) {
                    return true;
                }
            }
        }

        return false;
    }
}