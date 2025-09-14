<?php

namespace App\Services;

use App\Services\FunctionMetadata\GetFunctions\Service as GetFunctionsService;
use App\Services\FunctionMetadata\GetFunction\Service as GetFunctionService;
use App\Services\FunctionMetadata\GetStatistics\Service as GetStatisticsService;
use App\Services\FunctionMetadata\UpdateFunction\Service as UpdateFunctionService;
use App\Services\FunctionMetadata\DeleteFunction\Service as DeleteFunctionService;
use App\Services\FunctionMetadata\AddVersion\Service as AddVersionService;
use App\Services\FunctionMetadata\GetFunctionsByCategory\Service as GetFunctionsByCategoryService;
use App\Services\FunctionMetadata\SearchFunctions\Service as SearchFunctionsService;
use App\Services\FunctionMetadata\GetFunctionDependencies\Service as GetFunctionDependenciesService;
use App\Services\FunctionMetadata\GetFunctionDependents\Service as GetFunctionDependentsService;
use App\Services\FunctionMetadata\HasCircularDependency\Service as HasCircularDependencyService;
use App\Services\FunctionMetadata\InitializeMetadata\Service as InitializeMetadataService;

class FunctionMetadataService
{
    protected $getFunctionsService;
    protected $getFunctionService;
    protected $getStatisticsService;
    protected $updateFunctionService;
    protected $deleteFunctionService;
    protected $addVersionService;
    protected $getFunctionsByCategoryService;
    protected $searchFunctionsService;
    protected $getFunctionDependenciesService;
    protected $getFunctionDependentsService;
    protected $hasCircularDependencyService;
    protected $initializeMetadataService;

    public function __construct(
        GetFunctionsService $getFunctionsService,
        GetFunctionService $getFunctionService,
        GetStatisticsService $getStatisticsService,
        UpdateFunctionService $updateFunctionService,
        DeleteFunctionService $deleteFunctionService,
        AddVersionService $addVersionService,
        GetFunctionsByCategoryService $getFunctionsByCategoryService,
        SearchFunctionsService $searchFunctionsService,
        GetFunctionDependenciesService $getFunctionDependenciesService,
        GetFunctionDependentsService $getFunctionDependentsService,
        HasCircularDependencyService $hasCircularDependencyService,
        InitializeMetadataService $initializeMetadataService
    ) {
        $this->getFunctionsService = $getFunctionsService;
        $this->getFunctionService = $getFunctionService;
        $this->getStatisticsService = $getStatisticsService;
        $this->updateFunctionService = $updateFunctionService;
        $this->deleteFunctionService = $deleteFunctionService;
        $this->addVersionService = $addVersionService;
        $this->getFunctionsByCategoryService = $getFunctionsByCategoryService;
        $this->searchFunctionsService = $searchFunctionsService;
        $this->getFunctionDependenciesService = $getFunctionDependenciesService;
        $this->getFunctionDependentsService = $getFunctionDependentsService;
        $this->hasCircularDependencyService = $hasCircularDependencyService;
        $this->initializeMetadataService = $initializeMetadataService;
    }

    /**
     * Get all functions metadata
     */
    public function getFunctions(): array
    {
        return $this->getFunctionsService->__invoke();
    }

    /**
     * Get specific function metadata
     */
    public function getFunction(string $functionName): ?array
    {
        return $this->getFunctionService->__invoke($functionName);
    }

    /**
     * Get functions statistics
     */
    public function getStatistics(): array
    {
        return $this->getStatisticsService->__invoke();
    }

    /**
     * Update function metadata
     */
    public function updateFunction(string $functionName, array $metadata): bool
    {
        return $this->updateFunctionService->__invoke($functionName, $metadata);
    }

    /**
     * Delete function metadata
     */
    public function deleteFunction(string $functionName): bool
    {
        return $this->deleteFunctionService->__invoke($functionName);
    }

    /**
     * Add new version to function
     */
    public function addVersion(string $functionName, string $version): bool
    {
        return $this->addVersionService->__invoke($functionName, $version);
    }

    /**
     * Get functions by category
     */
    public function getFunctionsByCategory(string $category): array
    {
        return $this->getFunctionsByCategoryService->__invoke($category);
    }

    /**
     * Search functions by term
     */
    public function searchFunctions(string $term): array
    {
        return $this->searchFunctionsService->__invoke($term);
    }

    /**
     * Get function dependencies
     */
    public function getFunctionDependencies(string $functionName): array
    {
        return $this->getFunctionDependenciesService->__invoke($functionName);
    }

    /**
     * Get functions that depend on given function
     */
    public function getFunctionDependents(string $functionName): array
    {
        return $this->getFunctionDependentsService->__invoke($functionName);
    }

    /**
     * Check for circular dependencies
     */
    public function hasCircularDependency(string $functionName, array $dependencies, array $visited = []): bool
    {
        return $this->hasCircularDependencyService->__invoke($functionName, $dependencies, $visited);
    }

    /**
     * Initialize metadata files if they don't exist
     */
    public function initializeMetadata(): bool
    {
        return $this->initializeMetadataService->__invoke();
    }
}