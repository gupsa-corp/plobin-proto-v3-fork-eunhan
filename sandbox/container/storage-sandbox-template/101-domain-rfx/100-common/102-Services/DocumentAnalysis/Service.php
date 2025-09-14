<?php

namespace App\Services\DocumentAnalysis;

/**
 * RFX 도메인 문서 분석 서비스
 */
class Service
{
    private $repository;

    public function __construct()
    {
        $this->repository = new Repository();
    }

    /**
     * 문서 분석 처리
     */
    public function analyzeDocument($documentPath, $analysisType = 'general', $options = [])
    {
        try {
            // 비즈니스 로직 검증
            if (empty($documentPath)) {
                throw new \Exception('문서 경로는 필수입니다.');
            }

            if (!file_exists($documentPath)) {
                throw new \Exception('문서 파일을 찾을 수 없습니다.');
            }

            // 중복 분석 방지 로직
            $existingAnalysis = $this->repository->findByFilePath($documentPath);
            $recentAnalysis = array_filter($existingAnalysis, function($analysis) {
                return $analysis['status'] === 'completed' && 
                       strtotime($analysis['created_at']) > strtotime('-1 hour');
            });

            if (!empty($recentAnalysis) && !($options['force_reanalysis'] ?? false)) {
                return array_merge($recentAnalysis[0], [
                    'is_cached' => true,
                    'message' => '1시간 이내 분석 결과가 존재합니다.'
                ]);
            }

            // 초기 분석 기록 생성
            $analysisId = $this->repository->create([
                'file_path' => $documentPath,
                'status' => 'in_progress',
                'analysis_type' => $analysisType
            ]);

            try {
                // AI 분석 시뮬레이션
                $analysisResult = $this->performAIAnalysis($documentPath, $analysisType);
                
                // 비즈니스 로직: 결과 검증
                $validatedResult = $this->validateAnalysisResult($analysisResult);
                
                // 상태 업데이트
                $this->repository->updateStatusAndResult(
                    $analysisId, 
                    'completed', 
                    $validatedResult, 
                    $validatedResult['confidence_score']
                );

                return [
                    'id' => $analysisId,
                    'status' => 'completed',
                    'result' => $validatedResult,
                    'is_cached' => false
                ];

            } catch (\Exception $e) {
                // 실패 시 상태 업데이트
                $this->repository->updateStatusAndResult($analysisId, 'failed');
                throw $e;
            }

        } catch (\Exception $e) {
            error_log("Document analysis error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 분석 결과 조회
     */
    public function getAnalysisResult($id)
    {
        try {
            if (empty($id)) {
                throw new \Exception('분석 ID는 필수입니다.');
            }

            $result = $this->repository->findById($id);

            if (!$result) {
                throw new \Exception('분석 결과를 찾을 수 없습니다.');
            }

            // 비즈니스 로직: 추가 정보 제공
            $result = $this->enrichAnalysisData($result);

            return $result;

        } catch (\Exception $e) {
            error_log("Get analysis result error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 분석 히스토리 조회
     */
    public function getAnalysisHistory($options = [])
    {
        try {
            $limit = $options['limit'] ?? 20;
            $offset = $options['offset'] ?? 0;
            $status = $options['status'] ?? null;

            $results = $this->repository->getHistory($limit, $offset, $status);

            // 비즈니스 로직: 각 결과에 추가 정보 제공
            foreach ($results as &$result) {
                $result = $this->enrichAnalysisData($result);
            }

            return $results;

        } catch (\Exception $e) {
            error_log("Get analysis history error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 분석 삭제
     */
    public function deleteAnalysis($id)
    {
        try {
            if (empty($id)) {
                throw new \Exception('분석 ID는 필수입니다.');
            }

            // 비즈니스 로직: 삭제 가능 여부 검증
            $analysis = $this->repository->findById($id);
            if (!$analysis) {
                throw new \Exception('분석을 찾을 수 없습니다.');
            }

            if ($analysis['status'] === 'in_progress') {
                throw new \Exception('진행 중인 분석은 삭제할 수 없습니다.');
            }

            $deletedRows = $this->repository->delete($id);

            return ['success' => true, 'deleted_rows' => $deletedRows];

        } catch (\Exception $e) {
            error_log("Delete analysis error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 분석 통계 조회
     */
    public function getAnalysisStatistics()
    {
        try {
            $statusCounts = $this->repository->countByStatus();
            $typeCounts = $this->repository->countByAnalysisType();
            
            return [
                'total_analyses' => array_sum($statusCounts),
                'by_status' => $statusCounts,
                'by_type' => $typeCounts,
                'success_rate' => $this->calculateSuccessRate($statusCounts)
            ];
            
        } catch (\Exception $e) {
            error_log("Get analysis statistics error: " . $e->getMessage());
            throw $e;
        }
    }

    // === 비즈니스 로직 헬퍼 메서드들 ===

    /**
     * AI 분석 시뮬레이션
     */
    private function performAIAnalysis($documentPath, $analysisType = 'general')
    {
        // 실제 구현에서는 AI API 호출
        // 여기서는 시뮬레이션 결과 반환
        $baseResult = [
            'document_type' => $this->detectDocumentType($documentPath),
            'summary' => '문서 분석이 완료되었습니다.',
            'key_points' => [
                '주요 항목 1',
                '주요 항목 2',
                '주요 항목 3'
            ],
            'confidence_score' => 0.95,
            'analysis_time' => date('Y-m-d H:i:s'),
            'analysis_type' => $analysisType
        ];

        // 분석 타입별 추가 정보
        switch ($analysisType) {
            case 'detailed':
                $baseResult['detailed_analysis'] = $this->performDetailedAnalysis($documentPath);
                break;
            case 'summary_only':
                $baseResult['extended_summary'] = $this->generateExtendedSummary($documentPath);
                break;
        }

        return $baseResult;
    }

    /**
     * 분석 결과 검증
     */
    private function validateAnalysisResult($result)
    {
        // 비즈니스 로직: 결과 품질 검증
        if (empty($result['confidence_score']) || $result['confidence_score'] < 0.5) {
            throw new \Exception('분석 결과의 신뢰도가 낮습니다.');
        }

        // 필수 필드 검증
        $requiredFields = ['document_type', 'summary', 'confidence_score'];
        foreach ($requiredFields as $field) {
            if (empty($result[$field])) {
                throw new \Exception("분석 결과에 필수 필드({$field})가 누락되었습니다.");
            }
        }

        return $result;
    }

    /**
     * 분석 데이터에 추가 정보 제공
     */
    private function enrichAnalysisData($analysisData)
    {
        $analysisData['status_display'] = $this->getStatusDisplay($analysisData['status']);
        $analysisData['type_display'] = $this->getTypeDisplay($analysisData['analysis_type']);
        
        // 처리 시간 계산
        if ($analysisData['completed_at']) {
            $processingTime = strtotime($analysisData['completed_at']) - strtotime($analysisData['created_at']);
            $analysisData['processing_time'] = $processingTime . '초';
        }
        
        return $analysisData;
    }

    /**
     * 성공률 계산
     */
    private function calculateSuccessRate($statusCounts)
    {
        $total = array_sum($statusCounts);
        $successful = ($statusCounts['completed'] ?? 0);
        
        return $total > 0 ? round(($successful / $total) * 100, 2) : 0;
    }

    /**
     * 문서 타입 감지
     */
    private function detectDocumentType($documentPath)
    {
        $extension = pathinfo($documentPath, PATHINFO_EXTENSION);
        
        switch (strtolower($extension)) {
            case 'pdf':
                return 'pdf_document';
            case 'docx':
            case 'doc':
                return 'word_document';
            case 'xlsx':
            case 'xls':
                return 'excel_document';
            case 'pptx':
            case 'ppt':
                return 'powerpoint_document';
            case 'txt':
                return 'text_document';
            default:
                return 'unknown_document';
        }
    }

    /**
     * 상세 분석 수행
     */
    private function performDetailedAnalysis($documentPath)
    {
        return [
            'word_count' => rand(500, 5000),
            'paragraph_count' => rand(10, 100),
            'sentiment_analysis' => 'neutral',
            'topics' => ['비즈니스', '기술', '프로세스']
        ];
    }

    /**
     * 확장 요약 생성
     */
    private function generateExtendedSummary($documentPath)
    {
        return '이 문서는 주요 비즈니스 프로세스와 기술적 요구사항을 다루고 있으며, 전체적으로 체계적인 구성을 보여줍니다.';
    }

    private function getStatusDisplay($status)
    {
        $statusMap = [
            'pending' => '대기중',
            'in_progress' => '분석중',
            'completed' => '완료',
            'failed' => '실패'
        ];
        
        return $statusMap[$status] ?? '알 수 없음';
    }

    private function getTypeDisplay($type)
    {
        $typeMap = [
            'general' => '일반 분석',
            'detailed' => '상세 분석',
            'summary_only' => '요약 전용'
        ];
        
        return $typeMap[$type] ?? '기타';
    }
}