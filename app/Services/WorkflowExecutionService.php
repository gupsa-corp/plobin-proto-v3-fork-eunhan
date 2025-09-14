<?php

namespace App\Services;

use App\Http\Controllers\Sandbox\Workflows\BaseWorkflow;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class WorkflowExecutionService
{
    private $tempWorkflowDir;

    public function __construct()
    {
        $this->tempWorkflowDir = storage_path('temp/workflows');
        $this->ensureTempDirectoryExists();
    }

    /**
     * 임시 디렉토리 생성
     */
    private function ensureTempDirectoryExists()
    {
        if (!File::exists($this->tempWorkflowDir)) {
            File::makeDirectory($this->tempWorkflowDir, 0755, true);
        }
    }

    /**
     * 사용 가능한 함수 목록 반환 (BaseWorkflow에서 로드)
     */
    public function getAvailableFunctions()
    {
        // 임시 워크플로우 인스턴스 생성하여 함수 목록 가져오기
        $tempWorkflow = new class extends BaseWorkflow {
            public function execute($input) {
                return $input;
            }
        };

        return $tempWorkflow->getAvailableFunctions();
    }

    /**
     * 단일 함수 실행 (테스트 목적)
     */
    public function executeFunction($functionName, $params = [])
    {
        try {
            $tempWorkflow = new class extends BaseWorkflow {
                public function execute($input) {
                    return $input;
                }

                public function testFunction($functionName, $params) {
                    return $this->callFunction($functionName, $params);
                }
            };

            return $tempWorkflow->testFunction($functionName, $params);

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Function execution error: ' . $e->getMessage(),
                'function' => $functionName,
                'timestamp' => now()->toDateTimeString()
            ];
        }
    }

    /**
     * 워크플로우 코드 실행
     */
    public function executeWorkflow($workflowCode, $input = [])
    {
        try {
            // 워크플로우 코드 유효성 검증
            $validationResult = $this->validateWorkflowCode($workflowCode);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'message' => 'Workflow validation failed: ' . $validationResult['error'],
                    'timestamp' => now()->toDateTimeString()
                ];
            }

            // 임시 클래스 파일 생성 및 실행
            $result = $this->executeWorkflowCode($workflowCode, $input);

            return [
                'success' => true,
                'result' => $result['data'] ?? $result,
                'execution_log' => $result['execution_log'] ?? [],
                'timestamp' => now()->toDateTimeString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Workflow execution error: ' . $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ];
        }
    }

    /**
     * 워크플로우 코드 유효성 검증
     */
    public function validateWorkflowCode($workflowCode)
    {
        try {
            // 기본적인 PHP 문법 검증
            if (empty(trim($workflowCode))) {
                return ['valid' => false, 'error' => 'Workflow code is empty'];
            }

            // PHP 오픈 태그 확인
            if (!str_contains($workflowCode, '<?php')) {
                return ['valid' => false, 'error' => 'Missing PHP opening tag'];
            }

            // BaseWorkflow 상속 확인
            if (!str_contains($workflowCode, 'extends BaseWorkflow')) {
                return ['valid' => false, 'error' => 'Workflow class must extend BaseWorkflow'];
            }

            // execute 메서드 존재 확인
            if (!str_contains($workflowCode, 'function execute')) {
                return ['valid' => false, 'error' => 'Workflow class must implement execute method'];
            }

            // 위험한 함수들 체크
            $dangerousFunctions = [
                'eval', 'exec', 'system', 'shell_exec', 'passthru',
                'file_get_contents', 'file_put_contents', 'fopen', 'fwrite',
                'unlink', 'rmdir', 'mkdir', 'move_uploaded_file'
            ];

            foreach ($dangerousFunctions as $func) {
                if (str_contains($workflowCode, $func . '(')) {
                    return ['valid' => false, 'error' => "Dangerous function '{$func}' is not allowed"];
                }
            }

            return ['valid' => true];

        } catch (\Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 워크플로우 코드 실제 실행
     */
    private function executeWorkflowCode($workflowCode, $input)
    {
        // 고유한 임시 파일명 생성
        $tempFileName = 'workflow_' . uniqid() . '.php';
        $tempFilePath = $this->tempWorkflowDir . '/' . $tempFileName;

        try {
            // BaseWorkflow import 추가
            $fullCode = $workflowCode;
            if (!str_contains($fullCode, 'use App\Http\Sandbox\Workflows\BaseWorkflow')) {
                $fullCode = str_replace('<?php', "<?php\n\nuse App\\Http\\Sandbox\\Workflows\\BaseWorkflow;", $fullCode);
            }

            // 임시 파일에 워크플로우 코드 작성
            File::put($tempFilePath, $fullCode);

            // 파일 포함
            require_once $tempFilePath;

            // 클래스명 추출
            preg_match('/class\s+(\w+)/', $workflowCode, $matches);
            $className = $matches[1] ?? null;

            if (!$className) {
                throw new \Exception('Could not extract class name from workflow code');
            }

            // 클래스 존재 확인
            if (!class_exists($className)) {
                throw new \Exception("Workflow class '{$className}' not found");
            }

            // 워크플로우 인스턴스 생성 및 실행
            $workflow = new $className();

            if (!($workflow instanceof BaseWorkflow)) {
                throw new \Exception('Workflow class must extend BaseWorkflow');
            }

            // 워크플로우 실행
            $result = $workflow->execute($input);
            $executionLog = $workflow->getExecutionLog();

            return [
                'data' => $result,
                'execution_log' => $executionLog
            ];

        } finally {
            // 임시 파일 정리
            if (File::exists($tempFilePath)) {
                File::delete($tempFilePath);
            }
        }
    }

    /**
     * 워크플로우 템플릿 목록 반환
     */
    public function getWorkflowTemplates()
    {
        $templatesPath = app_path('Http/Sandbox/Workflows/Templates');
        $templates = [];

        if (!File::exists($templatesPath)) {
            return $templates;
        }

        $files = File::files($templatesPath);

        foreach ($files as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $content = File::get($file->getPathname());

            // 템플릿 설명 추출
            $description = $this->extractTemplateDescription($content);

            $templates[] = [
                'name' => $className,
                'description' => $description,
                'code' => $content,
                'path' => $file->getPathname()
            ];
        }

        return $templates;
    }

    /**
     * 템플릿 설명 추출
     */
    private function extractTemplateDescription($content)
    {
        // 클래스 주석에서 설명 추출
        if (preg_match('/\/\*\*(.*?)\*\//s', $content, $matches)) {
            $comment = $matches[1];
            $lines = explode("\n", $comment);
            foreach ($lines as $line) {
                $line = trim(str_replace(['*', '/'], '', $line));
                if (!empty($line)) {
                    return $line;
                }
            }
        }

        return '설명 없음';
    }

    /**
     * 워크플로우 저장
     */
    public function saveWorkflow($name, $code, $description = '')
    {
        try {
            $sandboxContextService = app(SandboxContextService::class);
        $currentStorage = $sandboxContextService->getCurrentSandbox();
            $workflowsPath = storage_path("sandbox/{$currentStorage}/workflows");

            // 워크플로우 디렉토리 생성
            if (!File::exists($workflowsPath)) {
                File::makeDirectory($workflowsPath, 0755, true);
            }

            $fileName = preg_replace('/[^a-zA-Z0-9_]/', '_', $name) . '.php';
            $filePath = $workflowsPath . '/' . $fileName;

            // 워크플로우 메타데이터 추가
            $metadata = [
                'name' => $name,
                'description' => $description,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString()
            ];

            $codeWithMetadata = "<?php\n/**\n * Workflow: {$name}\n * Description: {$description}\n * Created: " . now()->toDateTimeString() . "\n */\n\n" . ltrim($code, '<?php');

            File::put($filePath, $codeWithMetadata);

            return [
                'success' => true,
                'message' => 'Workflow saved successfully',
                'path' => $filePath
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to save workflow: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 저장된 워크플로우 목록 반환
     */
    public function getSavedWorkflows()
    {
        $sandboxContextService = app(SandboxContextService::class);
        $currentStorage = $sandboxContextService->getCurrentSandbox();
        $workflowsPath = storage_path("sandbox/{$currentStorage}/workflows");
        $workflows = [];

        if (!File::exists($workflowsPath)) {
            return $workflows;
        }

        $files = File::files($workflowsPath);

        foreach ($files as $file) {
            $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $content = File::get($file->getPathname());

            // 메타데이터 추출
            $metadata = $this->extractWorkflowMetadata($content);

            $workflows[] = [
                'name' => $metadata['name'] ?? $name,
                'description' => $metadata['description'] ?? '설명 없음',
                'code' => $content,
                'path' => $file->getPathname(),
                'created_at' => $metadata['created_at'] ?? 'Unknown',
                'updated_at' => $metadata['updated_at'] ?? 'Unknown'
            ];
        }

        return $workflows;
    }

    /**
     * 워크플로우 메타데이터 추출
     */
    private function extractWorkflowMetadata($content)
    {
        $metadata = [];

        if (preg_match('/\/\*\*(.*?)\*\//s', $content, $matches)) {
            $comment = $matches[1];

            if (preg_match('/\* Workflow:\s*(.*)/', $comment, $nameMatch)) {
                $metadata['name'] = trim($nameMatch[1]);
            }

            if (preg_match('/\* Description:\s*(.*)/', $comment, $descMatch)) {
                $metadata['description'] = trim($descMatch[1]);
            }

            if (preg_match('/\* Created:\s*(.*)/', $comment, $createdMatch)) {
                $metadata['created_at'] = trim($createdMatch[1]);
            }
        }

        return $metadata;
    }
}
