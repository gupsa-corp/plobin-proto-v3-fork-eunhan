<?php

namespace App\Livewire\Sandbox;

use Livewire\Component;
use App\Services\WorkflowExecutionService;
use Illuminate\Support\Facades\Session;

class FunctionAutomation extends Component
{
    // 워크플로우 코드 및 실행
    public string $workflowCode = '';
    public string $testInput = '{}';
    public array $executionResult = [];
    public array $executionLog = [];
    public bool $isExecuting = false;
    
    // 함수 목록
    public array $availableFunctions = [];
    public array $globalFunctions = [];
    public array $storageFunctions = [];
    
    // 템플릿 관리
    public string $selectedTemplate = '';
    public array $workflowTemplates = [];
    public array $savedWorkflows = [];
    
    // 워크플로우 저장
    public string $workflowName = '';
    public string $workflowDescription = '';
    public bool $showSaveModal = false;
    
    // 현재 선택된 함수 정보
    public array $selectedFunctionInfo = [];
    public bool $showFunctionDetails = false;
    
    private WorkflowExecutionService $workflowService;
    
    public function boot()
    {
        $this->workflowService = app(WorkflowExecutionService::class);
    }
    
    public function mount()
    {
        $this->loadAvailableFunctions();
        $this->loadWorkflowTemplates();
        $this->loadSavedWorkflows();
        $this->setDefaultWorkflowCode();
    }
    
    /**
     * 사용 가능한 함수 목록 로드
     */
    private function loadAvailableFunctions()
    {
        $functions = $this->workflowService->getAvailableFunctions();
        $this->globalFunctions = $functions['global'] ?? [];
        $this->storageFunctions = $functions['storage'] ?? [];
        $this->availableFunctions = $functions['all'] ?? [];
    }
    
    /**
     * 워크플로우 템플릿 로드
     */
    private function loadWorkflowTemplates()
    {
        $this->workflowTemplates = $this->workflowService->getWorkflowTemplates();
    }
    
    /**
     * 저장된 워크플로우 로드
     */
    private function loadSavedWorkflows()
    {
        $this->savedWorkflows = $this->workflowService->getSavedWorkflows();
    }
    
    /**
     * 기본 워크플로우 코드 설정
     */
    private function setDefaultWorkflowCode()
    {
        if (empty($this->workflowCode)) {
            $this->workflowCode = '<?php

use App\Http\Sandbox\Workflows\BaseWorkflow;

class MyWorkflow extends BaseWorkflow
{
    public function execute($input)
    {
        // 예시: StringHelper 함수 호출
        $step1 = $this->callFunction(\'StringHelper\', [
            \'operation\' => \'format\',
            \'data\' => $input[\'data\'] ?? \'test data\',
            \'format\' => \'json\'
        ]);
        
        // 예시: 조건부 실행
        if (isset($step1[\'success\']) && $step1[\'success\']) {
            return [
                \'success\' => true,
                \'message\' => \'Workflow completed successfully\',
                \'data\' => $step1[\'formatted_data\'] ?? $step1
            ];
        }
        
        return [
            \'success\' => false,
            \'message\' => \'Workflow failed\'
        ];
    }
}';
        }
    }
    
    /**
     * 워크플로우 실행
     */
    public function executeWorkflow()
    {
        $this->isExecuting = true;
        $this->executionResult = [];
        $this->executionLog = [];
        
        try {
            // 입력 데이터 파싱
            $input = json_decode($this->testInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON input: ' . json_last_error_msg());
            }
            
            // 워크플로우 실행
            $result = $this->workflowService->executeWorkflow($this->workflowCode, $input);
            
            $this->executionResult = $result;
            $this->executionLog = $result['execution_log'] ?? [];
            
            if ($result['success']) {
                $this->dispatch('workflow-success', [
                    'message' => '워크플로우가 성공적으로 실행되었습니다.'
                ]);
            } else {
                $this->dispatch('workflow-error', [
                    'message' => $result['message'] ?? '워크플로우 실행 중 오류가 발생했습니다.'
                ]);
            }
            
        } catch (\Exception $e) {
            $this->executionResult = [
                'success' => false,
                'message' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ];
            
            $this->dispatch('workflow-error', [
                'message' => $e->getMessage()
            ]);
        } finally {
            $this->isExecuting = false;
        }
    }
    
    /**
     * 템플릿 선택
     */
    public function selectTemplate($templateName)
    {
        $template = collect($this->workflowTemplates)->firstWhere('name', $templateName);
        
        if ($template) {
            $this->workflowCode = $template['code'];
            $this->selectedTemplate = $templateName;
            
            $this->dispatch('template-loaded', [
                'message' => "템플릿 '{$templateName}'이 로드되었습니다."
            ]);
        }
    }
    
    /**
     * 저장된 워크플로우 로드
     */
    public function loadSavedWorkflow($workflowName)
    {
        $workflow = collect($this->savedWorkflows)->firstWhere('name', $workflowName);
        
        if ($workflow) {
            $this->workflowCode = $workflow['code'];
            $this->workflowName = $workflow['name'];
            $this->workflowDescription = $workflow['description'] ?? '';
            
            $this->dispatch('workflow-loaded', [
                'message' => "워크플로우 '{$workflowName}'이 로드되었습니다."
            ]);
        }
    }
    
    /**
     * 함수 코드에 삽입
     */
    public function insertFunction($functionName)
    {
        $function = collect($this->availableFunctions)->firstWhere('name', $functionName);
        
        if (!$function) {
            return;
        }
        
        $insertCode = '';
        
        if ($function['type'] === 'global') {
            // Global Function 삽입 예시
            $params = $function['parameters'] ?? [];
            $paramExamples = [];
            
            foreach ($params as $paramName => $paramInfo) {
                $example = $paramInfo['example'] ?? '';
                if (is_string($example)) {
                    $paramExamples[] = "'{$paramName}' => '{$example}'";
                } else {
                    $paramExamples[] = "'{$paramName}' => " . json_encode($example);
                }
            }
            
            $paramString = implode(",\n            ", $paramExamples);
            
            $insertCode = "\$result = \$this->callFunction('{$functionName}', [\n            {$paramString}\n        ]);";
            
        } else {
            // Storage Function 삽입 예시
            $insertCode = "\$result = \$this->callFunction('{$functionName}', [\n            'operation' => 'main', // 필요에 따라 수정\n            // 추가 파라미터들...\n        ]);";
        }
        
        // 커서 위치를 찾아 코드 삽입 (간단한 구현)
        $this->dispatch('insert-function-code', [
            'code' => $insertCode,
            'functionName' => $functionName
        ]);
    }
    
    /**
     * 함수 상세 정보 보기
     */
    public function showFunctionInfo($functionName)
    {
        $function = collect($this->availableFunctions)->firstWhere('name', $functionName);
        
        if ($function) {
            $this->selectedFunctionInfo = $function;
            $this->showFunctionDetails = true;
        }
    }
    
    /**
     * 함수 상세 정보 닫기
     */
    public function closeFunctionDetails()
    {
        $this->showFunctionDetails = false;
        $this->selectedFunctionInfo = [];
    }
    
    /**
     * 워크플로우 저장 모달 열기
     */
    public function openSaveModal()
    {
        $this->showSaveModal = true;
        $this->workflowName = '';
        $this->workflowDescription = '';
    }
    
    /**
     * 워크플로우 저장
     */
    public function saveWorkflow()
    {
        if (empty($this->workflowName)) {
            $this->dispatch('validation-error', [
                'message' => '워크플로우 이름을 입력해주세요.'
            ]);
            return;
        }
        
        $result = $this->workflowService->saveWorkflow(
            $this->workflowName,
            $this->workflowCode,
            $this->workflowDescription
        );
        
        if ($result['success']) {
            $this->loadSavedWorkflows(); // 목록 새로고침
            $this->showSaveModal = false;
            
            $this->dispatch('workflow-saved', [
                'message' => '워크플로우가 저장되었습니다.'
            ]);
        } else {
            $this->dispatch('save-error', [
                'message' => $result['message']
            ]);
        }
    }
    
    /**
     * 저장 모달 닫기
     */
    public function closeSaveModal()
    {
        $this->showSaveModal = false;
        $this->workflowName = '';
        $this->workflowDescription = '';
    }
    
    /**
     * 워크플로우 코드 초기화
     */
    public function resetWorkflow()
    {
        $this->workflowCode = '';
        $this->setDefaultWorkflowCode();
        $this->executionResult = [];
        $this->executionLog = [];
        $this->selectedTemplate = '';
        
        $this->dispatch('workflow-reset', [
            'message' => '워크플로우가 초기화되었습니다.'
        ]);
    }
    
    /**
     * 단일 함수 테스트 실행
     */
    public function testSingleFunction($functionName)
    {
        try {
            $input = json_decode($this->testInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON input');
            }
            
            $result = $this->workflowService->executeFunction($functionName, $input);
            
            $this->executionResult = [
                'success' => true,
                'single_function_test' => true,
                'function_name' => $functionName,
                'result' => $result,
                'timestamp' => now()->toDateTimeString()
            ];
            
        } catch (\Exception $e) {
            $this->executionResult = [
                'success' => false,
                'message' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ];
        }
    }
    
    /**
     * 현재 샌드박스 변경 시 함수 목록 새로고침
     */
    public function refreshFunctions()
    {
        $this->loadAvailableFunctions();
        $this->loadSavedWorkflows();
        
        $this->dispatch('functions-refreshed', [
            'message' => '함수 목록이 새로고침되었습니다.'
        ]);
    }
    
    public function render()
    {
        return view('livewire.sandbox.function-automation');
    }
}