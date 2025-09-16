<?php

namespace App\Livewire\Sandbox;

use Livewire\Component;
use App\Services\FunctionMetadataService;

class FunctionDependencies extends Component
{
    public $functions = [];
    public $selectedFunction = null;
    public $selectedFunctionData = null;
    public $viewMode = 'graph';
    public $searchTerm = '';
    public $filterCategory = '';
    public $dependencyGraph = [];

    public function mount()
    {
        $this->loadFunctions();
        $this->buildDependencyGraph();
    }

    public function loadFunctions()
    {
        $metadataService = app(FunctionMetadataService::class);
        $this->functions = $metadataService->getFunctions();
    }

    public function selectFunction($functionName)
    {
        $metadataService = app(FunctionMetadataService::class);
        $this->selectedFunction = $functionName;
        $this->selectedFunctionData = [
            'info' => $this->functions[$functionName] ?? null,
            'dependencies' => $metadataService->getFunctionDependencies($functionName),
            'dependents' => $metadataService->getFunctionDependents($functionName)
        ];
        $this->dispatch('functionSelected', $functionName);
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        $this->dispatch('viewModeChanged', $mode);
    }

    public function updatedSearchTerm()
    {
        $this->filterFunctions();
    }

    public function updatedFilterCategory()
    {
        $this->filterFunctions();
    }

    public function filterFunctions()
    {
        $this->dispatch('filterChanged', [
            'search' => $this->searchTerm,
            'category' => $this->filterCategory
        ]);
    }

    private function buildDependencyGraph()
    {
        $nodes = [];
        $links = [];
        $allDependencies = [];

        // First pass: collect all function nodes and dependencies
        foreach ($this->functions as $functionName => $functionData) {
            $nodes[] = [
                'id' => $functionName,
                'name' => $functionName,
                'category' => $functionData['category'] ?? 'default',
                'description' => $functionData['description'] ?? ''
            ];

            $dependencies = $functionData['dependencies'] ?? [];
            foreach ($dependencies as $dependency) {
                $allDependencies[$dependency] = true;
                $links[] = [
                    'source' => $dependency,
                    'target' => $functionName
                ];
            }
        }

        // Second pass: create placeholder nodes for dependencies that don't exist as functions
        $existingNodeIds = array_column($nodes, 'id');
        foreach ($allDependencies as $dependency => $value) {
            if (!in_array($dependency, $existingNodeIds)) {
                $nodes[] = [
                    'id' => $dependency,
                    'name' => $dependency,
                    'category' => 'external',
                    'description' => 'External dependency'
                ];
            }
        }

        $this->dependencyGraph = [
            'nodes' => $nodes,
            'links' => $links
        ];
    }

    public function render()
    {
        return view('livewire.sandbox.function-dependencies');
    }
}