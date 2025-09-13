<?php
/**
 * Excel Report Generation Workflow Template
 * 다양한 데이터를 Excel 리포트로 생성하는 워크플로우 템플릿
 */

namespace App\Http\Controllers\Sandbox\Workflows\Templates;

use App\Http\Sandbox\Workflows\BaseWorkflow;

class ExcelReportWorkflow extends BaseWorkflow
{
    public function execute($input)
    {
        $this->logStep('Starting Excel report workflow', $input);
        
        // 1단계: 입력 데이터 검증
        if (!isset($input['data']) || empty($input['data'])) {
            return $this->errorResponse('Report data is required');
        }
        
        $reportData = $input['data'];
        $reportType = $input['report_type'] ?? 'general';
        
        // 2단계: 리포트 유형에 따른 데이터 처리
        switch ($reportType) {
            case 'user_report':
                return $this->generateUserReport($input);
            case 'data_analysis':
                return $this->generateDataAnalysisReport($input);
            case 'summary_report':
                return $this->generateSummaryReport($input);
            default:
                return $this->generateGeneralReport($input);
        }
    }
    
    private function generateUserReport($input)
    {
        $userData = $input['data'];
        
        // 1단계: 사용자 데이터 가져오기 (필요한 경우)
        if (isset($input['fetch_users']) && $input['fetch_users']) {
            $userResult = $this->callFunction('UserManager', [
                'action' => 'list',
                'limit' => $input['limit'] ?? 100
            ]);
            
            if (isset($userResult['success']) && $userResult['success']) {
                $userData = $userResult['data'] ?? [];
            }
        }
        
        // 2단계: Excel 형태로 데이터 구조화
        $excelData = [
            ['사용자 리포트', '생성일: ' . now()->format('Y-m-d H:i:s')],
            [''], // 빈 행
            ['번호', '이름', '이메일', '등록일', '상태'] // 헤더
        ];
        
        if (is_array($userData)) {
            foreach ($userData as $index => $user) {
                $excelData[] = [
                    $index + 1,
                    $user['name'] ?? 'N/A',
                    $user['email'] ?? 'N/A',
                    $user['created_at'] ?? 'N/A',
                    $user['status'] ?? 'active'
                ];
            }
            
            // 총계 행 추가
            $excelData[] = [''];
            $excelData[] = ['총 사용자 수:', count($userData)];
        }
        
        return $this->createExcelFile($excelData, 'user_report', '사용자 리포트');
    }
    
    private function generateDataAnalysisReport($input)
    {
        $data = $input['data'];
        
        // 1단계: 데이터 분석을 위한 계산
        $analysisResult = $this->callFunction('StringHelper', [
            'operation' => 'calculate',
            'values' => is_array($data) ? array_values($data) : [$data],
            'calc_operation' => 'sum'
        ]);
        
        $averageResult = $this->callFunction('StringHelper', [
            'operation' => 'calculate',
            'values' => is_array($data) ? array_values($data) : [$data],
            'calc_operation' => 'average'
        ]);
        
        $maxResult = $this->callFunction('StringHelper', [
            'operation' => 'calculate',
            'values' => is_array($data) ? array_values($data) : [$data],
            'calc_operation' => 'max'
        ]);
        
        $minResult = $this->callFunction('StringHelper', [
            'operation' => 'calculate',
            'values' => is_array($data) ? array_values($data) : [$data],
            'calc_operation' => 'min'
        ]);
        
        // 2단계: 분석 결과를 Excel 형태로 구조화
        $excelData = [
            ['데이터 분석 리포트', '생성일: ' . now()->format('Y-m-d H:i:s')],
            [''],
            ['분석 항목', '결과값'],
            ['총합', $analysisResult['result'] ?? 'N/A'],
            ['평균', $averageResult['result'] ?? 'N/A'],
            ['최대값', $maxResult['result'] ?? 'N/A'],
            ['최소값', $minResult['result'] ?? 'N/A'],
            ['데이터 개수', is_array($data) ? count($data) : 1],
            [''],
            ['원본 데이터'],
            ['순번', '값']
        ];
        
        // 원본 데이터 추가
        if (is_array($data)) {
            foreach ($data as $index => $value) {
                $excelData[] = [$index + 1, $value];
            }
        } else {
            $excelData[] = [1, $data];
        }
        
        return $this->createExcelFile($excelData, 'data_analysis_report', '데이터 분석');
    }
    
    private function generateSummaryReport($input)
    {
        $data = $input['data'];
        $title = $input['title'] ?? '요약 리포트';
        
        // 1단계: 데이터 요약 생성
        $summary = [
            'total_items' => is_array($data) ? count($data) : 1,
            'report_date' => now()->format('Y-m-d H:i:s'),
            'data_type' => gettype($data)
        ];
        
        // 2단계: Excel 형태로 구조화
        $excelData = [
            [$title, '생성일: ' . $summary['report_date']],
            [''],
            ['요약 정보'],
            ['항목 수', $summary['total_items']],
            ['데이터 타입', $summary['data_type']],
            [''],
            ['상세 데이터']
        ];
        
        // 데이터 내용 추가
        if (is_array($data)) {
            $excelData[] = ['키', '값'];
            foreach ($data as $key => $value) {
                if (!is_array($value) && !is_object($value)) {
                    $excelData[] = [$key, $value];
                } else {
                    $excelData[] = [$key, json_encode($value)];
                }
            }
        } else {
            $excelData[] = ['데이터', $data];
        }
        
        return $this->createExcelFile($excelData, 'summary_report', '요약 리포트');
    }
    
    private function generateGeneralReport($input)
    {
        $data = $input['data'];
        $title = $input['title'] ?? '일반 리포트';
        
        // 1단계: 일반적인 리포트 형태로 데이터 구조화
        $excelData = [
            [$title, '생성일: ' . now()->format('Y-m-d H:i:s')],
            ['']
        ];
        
        // 2단계: 데이터 타입에 따른 처리
        if (is_array($data)) {
            // 2차원 배열인지 확인
            if (isset($data[0]) && is_array($data[0])) {
                // 2차원 배열 - 테이블 형태로 처리
                foreach ($data as $row) {
                    $excelData[] = is_array($row) ? array_values($row) : [$row];
                }
            } else {
                // 1차원 배열 - 키-값 형태로 처리
                $excelData[] = ['키', '값'];
                foreach ($data as $key => $value) {
                    $excelData[] = [$key, is_scalar($value) ? $value : json_encode($value)];
                }
            }
        } else {
            // 스칼라 값 - 단순 표시
            $excelData[] = ['데이터', $data];
        }
        
        return $this->createExcelFile($excelData, 'general_report', '일반 리포트');
    }
    
    private function createExcelFile($excelData, $filenamePrefix, $sheetName)
    {
        $filename = $filenamePrefix . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        $excelResult = $this->callFunction('PHPExcelGenerator', [
            'data' => $excelData,
            'filename' => $filename,
            'sheet_name' => $sheetName,
            'has_headers' => true,
            'auto_width' => true
        ]);
        
        return [
            'success' => isset($excelResult['success']) ? $excelResult['success'] : false,
            'message' => 'Excel report generation workflow completed',
            'excel_result' => $excelResult,
            'filename' => $filename,
            'sheet_name' => $sheetName,
            'workflow' => 'ExcelReportWorkflow',
            'timestamp' => now()->toDateTimeString()
        ];
    }
    
    private function errorResponse($message, $details = null)
    {
        return [
            'success' => false,
            'message' => $message,
            'details' => $details,
            'workflow' => 'ExcelReportWorkflow',
            'timestamp' => now()->toDateTimeString()
        ];
    }
}