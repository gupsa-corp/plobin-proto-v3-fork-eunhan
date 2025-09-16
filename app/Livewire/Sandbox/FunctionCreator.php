<?php

namespace App\Livewire\Sandbox;

use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use App\Services\FunctionTemplateService;
use App\Services\FunctionMetadataService;
use Illuminate\Support\Facades\Session;

class FunctionCreator extends Component implements HasForms
{
    use InteractsWithForms;

    // Wizard steps
    public int $currentStep = 1;
    public int $totalSteps = 4;

    // Step 1: Template Selection
    public string $selectedTemplate = '';
    public string $selectedCategory = '';
    public array $availableTemplates = [];
    public array $categories = [];

    // Step 2: Basic Information
    public string $functionName = '';
    public string $description = '';

    // Step 3: Parameters
    public array $templateParameters = [];
    public array $parameterValues = [];

    // Step 4: Preview & Create
    public string $generatedCode = '';
    public bool $isCreating = false;
    public array $creationResult = [];

    // Services
    private FunctionTemplateService $templateService;
    private FunctionMetadataService $metadataService;

    public function boot()
    {
        $this->templateService = app(FunctionTemplateService::class);
        $this->metadataService = app(FunctionMetadataService::class);
    }

    public function mount()
    {
        $this->loadTemplates();
        $this->resetWizard();
    }

    /**
     * Load available templates and categories
     */
    public function loadTemplates()
    {
        $this->availableTemplates = $this->templateService->getTemplates();
        $this->categories = $this->templateService->getCategories();
    }

    /**
     * Reset wizard to initial state
     */
    public function resetWizard()
    {
        $this->currentStep = 1;
        $this->selectedTemplate = '';
        $this->selectedCategory = '';
        $this->functionName = '';
        $this->description = '';
        $this->templateParameters = [];
        $this->parameterValues = [];
        $this->generatedCode = '';
        $this->isCreating = false;
        $this->creationResult = [];
    }

    /**
     * Select template
     */
    public function selectTemplate(string $templateId)
    {
        $this->selectedTemplate = $templateId;
        $template = $this->templateService->getTemplate($templateId);
        
        if ($template) {
            $this->templateParameters = $template['parameters'] ?? [];
            $this->selectedCategory = $template['category'] ?? '';
            
            // Initialize parameter values with defaults
            foreach ($this->templateParameters as $param) {
                $name = $param['name'];
                if ($name !== 'className') { // className will be set from functionName
                    $this->parameterValues[$name] = $param['default'] ?? '';
                }
            }
        }
    }

    /**
     * Go to next step
     */
    public function nextStep()
    {
        if ($this->validateCurrentStep()) {
            if ($this->currentStep < $this->totalSteps) {
                $this->currentStep++;
                
                if ($this->currentStep === 4) {
                    $this->generatePreview();
                }
            }
        }
    }

    /**
     * Go to previous step
     */
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Go to specific step
     */
    public function goToStep(int $step)
    {
        if ($step >= 1 && $step <= $this->totalSteps && $step <= $this->currentStep + 1) {
            $this->currentStep = $step;
            
            if ($step === 4) {
                $this->generatePreview();
            }
        }
    }

    /**
     * Validate current step
     */
    private function validateCurrentStep(): bool
    {
        switch ($this->currentStep) {
            case 1: // Template selection
                if (empty($this->selectedTemplate)) {
                    $this->addError('selectedTemplate', 'Please select a template.');
                    return false;
                }
                break;

            case 2: // Basic information
                if (empty($this->functionName)) {
                    $this->addError('functionName', 'Function name is required.');
                    return false;
                }
                
                if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $this->functionName)) {
                    $this->addError('functionName', 'Function name must start with a letter and contain only letters, numbers, and underscores.');
                    return false;
                }

                // Check if function already exists
                $existingFunction = $this->metadataService->getFunction($this->functionName);
                if ($existingFunction) {
                    $this->addError('functionName', 'A function with this name already exists.');
                    return false;
                }
                break;

            case 3: // Parameters
                // Include className and description in validation
                $parameters = array_merge($this->parameterValues, [
                    'className' => $this->functionName,
                    'description' => $this->description
                ]);
                
                $validation = $this->templateService->validateParameters($this->selectedTemplate, $parameters);
                if (!$validation['valid']) {
                    foreach ($validation['errors'] as $error) {
                        $this->addError('parameters', $error);
                    }
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * Generate code preview
     */
    public function generatePreview()
    {
        if (!empty($this->selectedTemplate) && !empty($this->functionName)) {
            $parameters = array_merge($this->parameterValues, [
                'className' => $this->functionName,
                'description' => $this->description
            ]);

            $this->generatedCode = $this->templateService->processTemplate($this->selectedTemplate, $parameters) ?? '';
        }
    }

    /**
     * Create the function
     */
    public function createFunction()
    {
        $this->isCreating = true;
        $this->creationResult = [];

        try {
            if (!$this->validateCurrentStep()) {
                return;
            }

            $parameters = array_merge($this->parameterValues, [
                'className' => $this->functionName,
                'description' => $this->description
            ]);

            $result = $this->templateService->createFunction(
                $this->functionName,
                $this->selectedTemplate,
                $parameters
            );

            $this->creationResult = $result;

            if ($result['success']) {
                // Emit event to parent component to refresh function list
                $this->dispatch('functionCreated', $this->functionName);
                
                // Show success message
                session()->flash('success', $result['message']);
                
                // Reset wizard after successful creation
                $this->resetWizard();
            }

        } catch (\Exception $e) {
            $this->creationResult = [
                'success' => false,
                'message' => 'Error creating function: ' . $e->getMessage()
            ];
        } finally {
            $this->isCreating = false;
        }
    }

    /**
     * Get templates by category
     */
    public function getTemplatesByCategory(string $category = ''): array
    {
        if (empty($category)) {
            return $this->availableTemplates;
        }

        return array_filter($this->availableTemplates, function($template) use ($category) {
            return ($template['category'] ?? '') === $category;
        });
    }

    /**
     * Get step title
     */
    public function getStepTitle(int $step): string
    {
        $titles = [
            1 => '템플릿 선택',
            2 => '기본 정보',
            3 => '파라미터 설정',
            4 => '미리보기 & 생성'
        ];

        return $titles[$step] ?? "Step {$step}";
    }

    /**
     * Get step description
     */
    public function getStepDescription(int $step): string
    {
        $descriptions = [
            1 => '함수에 사용할 템플릿을 선택하세요',
            2 => '함수의 이름과 설명을 입력하세요',
            3 => '템플릿에 필요한 파라미터를 설정하세요',
            4 => '생성될 코드를 확인하고 함수를 생성하세요'
        ];

        return $descriptions[$step] ?? '';
    }

    /**
     * Check if step is completed
     */
    public function isStepCompleted(int $step): bool
    {
        switch ($step) {
            case 1:
                return !empty($this->selectedTemplate);
            case 2:
                return !empty($this->functionName) && preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $this->functionName);
            case 3:
                // Include className and description in validation
                $parameters = array_merge($this->parameterValues, [
                    'className' => $this->functionName,
                    'description' => $this->description
                ]);
                $validation = $this->templateService->validateParameters($this->selectedTemplate, $parameters);
                return $validation['valid'];
            case 4:
                return !empty($this->generatedCode);
        }

        return false;
    }

    /**
     * Update parameter value
     */
    public function updateParameter(string $paramName, $value)
    {
        $this->parameterValues[$paramName] = $value;
        
        // If we're on the preview step, regenerate preview
        if ($this->currentStep === 4) {
            $this->generatePreview();
        }
    }

    /**
     * Get current template info
     */
    public function getCurrentTemplate(): ?array
    {
        if (empty($this->selectedTemplate)) {
            return null;
        }

        return $this->templateService->getTemplate($this->selectedTemplate);
    }

    public function render()
    {
        return view('livewire.sandbox.function-creator', [
            'template' => $this->getCurrentTemplate(),
            'templatesByCategory' => $this->getTemplatesByCategory($this->selectedCategory)
        ]);
    }
}