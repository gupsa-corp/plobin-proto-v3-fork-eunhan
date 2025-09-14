<?php

namespace App\Services\FunctionMetadata\SearchFunctions;

use App\Services\FunctionMetadata\GetFunctions\Service as GetFunctionsService;

class Service
{
    protected $getFunctionsService;

    public function __construct(GetFunctionsService $getFunctionsService)
    {
        $this->getFunctionsService = $getFunctionsService;
    }

    /**
     * Search functions by term
     */
    public function __invoke(string $term): array
    {
        $functions = $this->getFunctionsService->__invoke();
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
}