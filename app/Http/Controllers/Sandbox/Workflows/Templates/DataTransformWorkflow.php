<?php
/**
 * Data Transform Workflow Template
 * 데이터 변환 및 처리를 위한 워크플로우 템플릿
 */

namespace App\Http\Controllers\Sandbox\Workflows\Templates;

use App\Http\Sandbox\Workflows\BaseWorkflow;

class DataTransformWorkflow extends BaseWorkflow
{
    public function execute($input)
    {
        // 1단계: 입력 데이터 검증
        $this->logStep('Starting data transform workflow', $input);
        
        // 2단계: StringHelper로 데이터 포맷팅
        $formatted = $this->callFunction('StringHelper', [
            'operation' => 'format',
            'data' => $input['data'] ?? 'sample data',
            'format' => $input['output_format'] ?? 'json'
        ]);
        
        if (!isset($formatted['success']) || !$formatted['success']) {
            return $this->errorResponse('Data formatting failed', $formatted);
        }
        
        // 3단계: 데이터 변환 (필요한 경우)
        if (isset($input['transform_type']) && $input['transform_type'] !== 'none') {
            $converted = $this->callFunction('StringHelper', [
                'operation' => 'convert',
                'input' => $formatted['formatted_data'] ?? $formatted['data'],
                'from' => 'auto',
                'to' => $input['transform_type'] ?? 'array'
            ]);
            
            if (isset($converted['success']) && $converted['success']) {
                $formatted = $converted;
            }
        }
        
        // 4단계: 추가 처리가 있는 경우 DataProcessor 호출
        if (isset($input['additional_processing']) && $input['additional_processing']) {
            $processed = $this->callFunction('DataProcessor', [
                'data' => $formatted['converted_data'] ?? $formatted['formatted_data'] ?? $formatted['data']
            ]);
            
            if (isset($processed['success']) && $processed['success']) {
                $formatted = $processed;
            }
        }
        
        // 최종 결과 반환
        return [
            'success' => true,
            'message' => 'Data transformation completed successfully',
            'original_data' => $input,
            'transformed_data' => $formatted,
            'workflow' => 'DataTransformWorkflow',
            'timestamp' => now()->toDateTimeString()
        ];
    }
    
    private function errorResponse($message, $details = null)
    {
        return [
            'success' => false,
            'message' => $message,
            'details' => $details,
            'workflow' => 'DataTransformWorkflow',
            'timestamp' => now()->toDateTimeString()
        ];
    }
}