<?php

namespace App\Services\WorkflowExecution\ValidateWorkflowCode;

class Service
{
    public function __invoke($workflowCode)
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
}