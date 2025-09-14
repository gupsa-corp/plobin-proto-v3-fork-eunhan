<?php

namespace App\Services\WorkflowExecution\ExecuteWorkflow;

class Service
{
    public function __invoke($workflowCode, $input = [])
    {
        try {
            // 워크플로우 코드 유효성 검증
            $validateWorkflowCodeService = app(\App\Services\WorkflowExecution\ValidateWorkflowCode\Service::class);
            $validationResult = $validateWorkflowCodeService($workflowCode);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'message' => 'Workflow validation failed: ' . $validationResult['error'],
                    'timestamp' => now()->toDateTimeString()
                ];
            }

            // 임시 클래스 파일 생성 및 실행
            $executeWorkflowCodeService = app(\App\Services\WorkflowExecution\ExecuteWorkflowCode\Service::class);
            $result = $executeWorkflowCodeService($workflowCode, $input);

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
}