<?php
/**
 * Multi-Step Processing Workflow Template
 * 복합적인 다단계 데이터 처리 워크플로우 템플릿
 */

namespace App\Http\Controllers\Sandbox\Workflows\Templates;

use App\Http\Sandbox\Workflows\BaseWorkflow;

class MultiStepProcessingWorkflow extends BaseWorkflow
{
    public function execute($input)
    {
        $this->logStep('Starting multi-step processing workflow', $input);
        
        try {
            // 1단계: 입력 데이터 검증 및 전처리
            $preprocessedData = $this->preprocessData($input);
            
            if (!$preprocessedData['success']) {
                return $preprocessedData;
            }
            
            // 2단계: 데이터 변환
            $transformedData = $this->transformData($preprocessedData['data']);
            
            if (!$transformedData['success']) {
                return $transformedData;
            }
            
            // 3단계: 비즈니스 로직 처리
            $processedData = $this->processBusinessLogic($transformedData['data'], $input);
            
            if (!$processedData['success']) {
                return $processedData;
            }
            
            // 4단계: 후처리 및 결과 생성
            $finalResult = $this->postProcessData($processedData['data'], $input);
            
            // 5단계: 리포트 생성 (선택사항)
            if (isset($input['generate_report']) && $input['generate_report']) {
                $this->generateProcessingReport($finalResult, $input);
            }
            
            return [
                'success' => true,
                'message' => 'Multi-step processing completed successfully',
                'result' => $finalResult,
                'steps_completed' => ['preprocess', 'transform', 'business_logic', 'post_process'],
                'workflow' => 'MultiStepProcessingWorkflow',
                'timestamp' => now()->toDateTimeString()
            ];
            
        } catch (\Exception $e) {
            return $this->errorResponse('Workflow execution failed: ' . $e->getMessage());
        }
    }
    
    private function preprocessData($input)
    {
        $this->logStep('Step 1: Preprocessing data');
        
        // 입력 데이터 유효성 검증
        if (!isset($input['data'])) {
            return $this->errorResponse('Input data is required');
        }
        
        $data = $input['data'];
        
        // StringHelper를 사용한 데이터 정규화
        if (isset($input['normalize_data']) && $input['normalize_data']) {
            $normalizedResult = $this->callFunction('StringHelper', [
                'operation' => 'convert',
                'input' => $data,
                'from' => 'auto',
                'to' => 'array'
            ]);
            
            if (isset($normalizedResult['success']) && $normalizedResult['success']) {
                $data = $normalizedResult['converted_data'];
            }
        }
        
        // 데이터 검증 로직
        if (empty($data)) {
            return $this->errorResponse('Data is empty after preprocessing');
        }
        
        return [
            'success' => true,
            'data' => $data,
            'step' => 'preprocess'
        ];
    }
    
    private function transformData($data)
    {
        $this->logStep('Step 2: Transforming data');
        
        // 데이터 포맷팅
        $formattedResult = $this->callFunction('StringHelper', [
            'operation' => 'format',
            'data' => $data,
            'format' => 'json'
        ]);
        
        if (!isset($formattedResult['success']) || !$formattedResult['success']) {
            return $this->errorResponse('Data formatting failed', $formattedResult);
        }
        
        $transformedData = $formattedResult['formatted_data'] ?? $data;
        
        // 추가 변환이 필요한 경우 DataProcessor 사용
        if (is_string($transformedData) && json_decode($transformedData, true) !== null) {
            $transformedData = json_decode($transformedData, true);
        }
        
        return [
            'success' => true,
            'data' => $transformedData,
            'step' => 'transform'
        ];
    }
    
    private function processBusinessLogic($data, $input)
    {
        $this->logStep('Step 3: Processing business logic');
        
        $processingType = $input['processing_type'] ?? 'default';
        
        switch ($processingType) {
            case 'calculation':
                return $this->performCalculations($data, $input);
                
            case 'user_operation':
                return $this->performUserOperations($data, $input);
                
            case 'data_aggregation':
                return $this->performDataAggregation($data, $input);
                
            default:
                return $this->performDefaultProcessing($data, $input);
        }
    }
    
    private function performCalculations($data, $input)
    {
        if (!is_array($data)) {
            $data = [$data];
        }
        
        // 숫자 데이터 추출
        $numericData = array_filter($data, 'is_numeric');
        
        if (empty($numericData)) {
            return $this->errorResponse('No numeric data found for calculations');
        }
        
        // 계산 수행
        $calculations = [];
        
        $sumResult = $this->callFunction('StringHelper', [
            'operation' => 'calculate',
            'values' => array_values($numericData),
            'calc_operation' => 'sum'
        ]);
        
        if (isset($sumResult['success']) && $sumResult['success']) {
            $calculations['sum'] = $sumResult['result'];
        }
        
        $avgResult = $this->callFunction('StringHelper', [
            'operation' => 'calculate',
            'values' => array_values($numericData),
            'calc_operation' => 'average'
        ]);
        
        if (isset($avgResult['success']) && $avgResult['success']) {
            $calculations['average'] = $avgResult['result'];
        }
        
        return [
            'success' => true,
            'data' => [
                'original_data' => $data,
                'calculations' => $calculations,
                'numeric_count' => count($numericData)
            ],
            'step' => 'business_logic_calculation'
        ];
    }
    
    private function performUserOperations($data, $input)
    {
        $operation = $input['user_operation'] ?? 'list';
        
        // UserManager를 통한 사용자 관련 작업
        $userResult = $this->callFunction('UserManager', [
            'action' => $operation,
            'data' => $data
        ]);
        
        return [
            'success' => isset($userResult['success']) ? $userResult['success'] : false,
            'data' => [
                'operation' => $operation,
                'user_result' => $userResult,
                'original_data' => $data
            ],
            'step' => 'business_logic_user'
        ];
    }
    
    private function performDataAggregation($data, $input)
    {
        if (!is_array($data)) {
            return $this->errorResponse('Data must be an array for aggregation');
        }
        
        // 데이터 집계 수행
        $aggregation = [
            'total_items' => count($data),
            'data_types' => [],
            'unique_values' => array_unique($data),
            'grouped_data' => []
        ];
        
        // 데이터 타입 분석
        foreach ($data as $item) {
            $type = gettype($item);
            $aggregation['data_types'][$type] = ($aggregation['data_types'][$type] ?? 0) + 1;
        }
        
        return [
            'success' => true,
            'data' => [
                'original_data' => $data,
                'aggregation' => $aggregation
            ],
            'step' => 'business_logic_aggregation'
        ];
    }
    
    private function performDefaultProcessing($data, $input)
    {
        // 기본 처리 로직
        $processedData = [
            'processed_at' => now()->toDateTimeString(),
            'data_size' => is_array($data) ? count($data) : strlen((string)$data),
            'data_type' => gettype($data),
            'data' => $data
        ];
        
        return [
            'success' => true,
            'data' => $processedData,
            'step' => 'business_logic_default'
        ];
    }
    
    private function postProcessData($data, $input)
    {
        $this->logStep('Step 4: Post-processing data');
        
        // 최종 데이터 구조화
        $finalData = [
            'processed_result' => $data,
            'processing_summary' => [
                'completed_at' => now()->toDateTimeString(),
                'input_parameters' => array_keys($input),
                'processing_type' => $input['processing_type'] ?? 'default',
                'success' => true
            ]
        ];
        
        // 출력 포맷 지정이 있는 경우
        if (isset($input['output_format'])) {
            $formatResult = $this->callFunction('StringHelper', [
                'operation' => 'format',
                'data' => $finalData,
                'format' => $input['output_format']
            ]);
            
            if (isset($formatResult['success']) && $formatResult['success']) {
                $finalData['formatted_output'] = $formatResult['formatted_data'];
            }
        }
        
        return [
            'success' => true,
            'data' => $finalData,
            'step' => 'post_process'
        ];
    }
    
    private function generateProcessingReport($data, $input)
    {
        $this->logStep('Step 5: Generating processing report');
        
        try {
            $reportData = [
                ['다단계 처리 리포트', '생성일: ' . now()->format('Y-m-d H:i:s')],
                [''],
                ['처리 요약'],
                ['처리 유형', $input['processing_type'] ?? 'default'],
                ['완료 시간', now()->format('Y-m-d H:i:s')],
                ['성공 여부', '성공'],
                [''],
                ['처리 결과 요약']
            ];
            
            // 처리 결과 데이터 추가
            if (isset($data['data']['processing_summary'])) {
                $summary = $data['data']['processing_summary'];
                foreach ($summary as $key => $value) {
                    if (!is_array($value) && !is_object($value)) {
                        $reportData[] = [$key, $value];
                    }
                }
            }
            
            $this->callFunction('PHPExcelGenerator', [
                'data' => $reportData,
                'filename' => 'multi_step_processing_report_' . date('Y-m-d_H-i-s') . '.xlsx',
                'sheet_name' => '처리 리포트',
                'has_headers' => true,
                'auto_width' => true
            ]);
            
        } catch (\Exception $e) {
            $this->logStep('Failed to generate processing report', ['error' => $e->getMessage()]);
        }
    }
    
    private function errorResponse($message, $details = null)
    {
        return [
            'success' => false,
            'message' => $message,
            'details' => $details,
            'workflow' => 'MultiStepProcessingWorkflow',
            'timestamp' => now()->toDateTimeString()
        ];
    }
}