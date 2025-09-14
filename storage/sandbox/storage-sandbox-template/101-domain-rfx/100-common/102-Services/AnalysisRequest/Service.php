<?php

namespace App\Services\AnalysisRequest;

/**
 * RFX 도메인 분석 요청 서비스
 * (기존 001-analysis-requests.php 내용 통합)
 */
class Service
{
    private $repository;
    
    public function __construct()
    {
        $this->repository = new Repository();
    }
    
    /**
     * 분석 요청 생성
     */
    public function createAnalysisRequest($fileId, $requestType = 'document_analysis', $options = [])
    {
        try {
            // 비즈니스 로직 검증
            if (empty($fileId)) {
                throw new \Exception('파일 ID는 필수입니다.');
            }

            // 중복 요청 방지 로직
            $existingRequests = $this->repository->findByFileId($fileId);
            $pendingRequests = array_filter($existingRequests, function($request) {
                return in_array($request['status'], ['pending', 'in_progress']);
            });

            if (!empty($pendingRequests)) {
                throw new \Exception('이미 진행 중인 분석 요청이 있습니다.');
            }

            // Repository를 통한 데이터 저장
            $data = array_merge($options, [
                'file_id' => $fileId,
                'request_type' => $requestType
            ]);
            
            $requestId = $this->repository->create($data);
            
            // 비즈니스 로직: 분석 큐에 추가
            $this->enqueueAnalysis($requestId, $requestType);
            
            return [
                'id' => $requestId,
                'file_id' => $fileId,
                'request_type' => $requestType,
                'status' => 'pending',
                'estimated_completion' => $this->estimateAnalysisTime($requestType)
            ];
            
        } catch (Exception $e) {
            error_log("Create analysis request error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 분석 요청 조회
     */
    public function getAnalysisRequest($requestId)
    {
        try {
            if (empty($requestId)) {
                throw new \Exception('요청 ID는 필수입니다.');
            }

            $result = $this->repository->findById($requestId);
            
            if (!$result) {
                throw new \Exception('분석 요청을 찾을 수 없습니다.');
            }
            
            // 비즈니스 로직: 추가 정보 제공
            $result = $this->enrichAnalysisData($result);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Get analysis request error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 분석 요청 목록 조회
     */
    public function getAnalysisRequests($options = [])
    {
        try {
            $limit = $options['limit'] ?? 20;
            $offset = $options['offset'] ?? 0;
            $conditions = [
                'status' => $options['status'] ?? null,
                'request_type' => $options['request_type'] ?? null,
                'requested_by' => $options['requested_by'] ?? null
            ];
            
            // 빈 조건 제거
            $conditions = array_filter($conditions);
            
            $results = $this->repository->findByConditions($conditions, $limit, $offset);
            
            // 비즈니스 로직: 각 요청에 추가 정보 제공
            foreach ($results as &$result) {
                $result = $this->enrichAnalysisData($result);
            }
            
            return $results;
            
        } catch (Exception $e) {
            error_log("Get analysis requests error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 분석 요청 상태 업데이트
     */
    public function updateAnalysisRequestStatus($requestId, $status, $resultData = null)
    {
        try {
            if (empty($requestId) || empty($status)) {
                throw new \Exception('요청 ID와 상태는 필수입니다.');
            }

            // 비즈니스 로직: 상태 전환 검증
            $validTransitions = $this->getValidStatusTransitions();
            $currentRequest = $this->repository->findById($requestId);
            
            if (!$currentRequest) {
                throw new \Exception('분석 요청을 찾을 수 없습니다.');
            }

            $currentStatus = $currentRequest['status'];
            if (!in_array($status, $validTransitions[$currentStatus] ?? [])) {
                throw new \Exception("'{$currentStatus}'에서 '{$status}'로 상태 변경이 불가능합니다.");
            }

            $updatedRows = $this->repository->updateStatusAndResult($requestId, $status, $resultData);
            
            // 비즈니스 로직: 상태별 후처리
            $this->handleStatusUpdate($requestId, $status, $resultData);
            
            return ['success' => true, 'updated_rows' => $updatedRows];
            
        } catch (Exception $e) {
            error_log("Update analysis request status error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 분석 요청 삭제
     */
    public function deleteAnalysisRequest($requestId)
    {
        try {
            if (empty($requestId)) {
                throw new \Exception('요청 ID는 필수입니다.');
            }

            // 비즈니스 로직: 삭제 가능 여부 검증
            $request = $this->repository->findById($requestId);
            if (!$request) {
                throw new \Exception('분석 요청을 찾을 수 없습니다.');
            }
            
            if ($request['status'] === 'in_progress') {
                throw new \Exception('진행 중인 분석 요청은 삭제할 수 없습니다.');
            }

            $deletedRows = $this->repository->delete($requestId);
            
            // 비즈니스 로직: 관련 리소스 정리
            $this->cleanupAnalysisResources($requestId);
            
            return ['success' => true, 'deleted_rows' => $deletedRows];
            
        } catch (Exception $e) {
            error_log("Delete analysis request error: " . $e->getMessage());
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
            
            return [
                'total_requests' => array_sum($statusCounts),
                'by_status' => $statusCounts,
                'success_rate' => $this->calculateSuccessRate($statusCounts),
                'average_processing_time' => $this->calculateAverageProcessingTime()
            ];
            
        } catch (Exception $e) {
            error_log("Get analysis statistics error: " . $e->getMessage());
            throw $e;
        }
    }

    // === 비즈니스 로직 헬퍼 메서드들 ===

    /**
     * 분석을 큐에 추가
     */
    private function enqueueAnalysis($requestId, $requestType)
    {
        // TODO: 실제 분석 큐 시스템과 연동
        error_log("Analysis queued for request ID: {$requestId}, type: {$requestType}");
    }

    /**
     * 분석 시간 예측
     */
    private function estimateAnalysisTime($requestType)
    {
        $timeMap = [
            'document_analysis' => 10, // 10분
            'image_analysis' => 5,     // 5분
            'data_analysis' => 15,     // 15분
            'text_extraction' => 3     // 3분
        ];
        
        $minutes = $timeMap[$requestType] ?? 10;
        return date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));
    }

    /**
     * 분석 데이터에 추가 정보 제공
     */
    private function enrichAnalysisData($analysisData)
    {
        $analysisData['status_display'] = $this->getStatusDisplay($analysisData['status']);
        $analysisData['type_display'] = $this->getTypeDisplay($analysisData['request_type']);
        $analysisData['progress_percentage'] = $this->calculateProgress($analysisData);
        
        return $analysisData;
    }

    /**
     * 유효한 상태 전환 규칙
     */
    private function getValidStatusTransitions()
    {
        return [
            'pending' => ['in_progress', 'cancelled'],
            'in_progress' => ['completed', 'failed', 'cancelled'],
            'completed' => [], // 완료된 요청은 상태 변경 불가
            'failed' => ['pending'], // 재처리 가능
            'cancelled' => ['pending'] // 재시작 가능
        ];
    }

    /**
     * 상태 업데이트 후처리
     */
    private function handleStatusUpdate($requestId, $status, $resultData)
    {
        switch ($status) {
            case 'completed':
                $this->notifyAnalysisCompleted($requestId);
                break;
            case 'failed':
                $this->handleAnalysisFailed($requestId);
                break;
            case 'cancelled':
                $this->handleAnalysisCancelled($requestId);
                break;
        }
    }

    /**
     * 분석 리소스 정리
     */
    private function cleanupAnalysisResources($requestId)
    {
        // TODO: 임시 파일, 캐시 등 정리
        error_log("Cleaning up resources for request ID: {$requestId}");
    }

    /**
     * 성공률 계산
     */
    private function calculateSuccessRate($statusCounts)
    {
        $total = array_sum($statusCounts);
        $successful = $statusCounts['completed'] ?? 0;
        
        return $total > 0 ? round(($successful / $total) * 100, 2) : 0;
    }

    /**
     * 평균 처리 시간 계산
     */
    private function calculateAverageProcessingTime()
    {
        // TODO: 실제 완료된 요청들의 처리 시간 계산
        return '8.5'; // 분 단위
    }

    private function getStatusDisplay($status)
    {
        $statusMap = [
            'pending' => '대기중',
            'in_progress' => '분석중',
            'completed' => '완료',
            'failed' => '실패',
            'cancelled' => '취소됨'
        ];
        
        return $statusMap[$status] ?? '알 수 없음';
    }

    private function getTypeDisplay($type)
    {
        $typeMap = [
            'document_analysis' => '문서 분석',
            'image_analysis' => '이미지 분석',
            'data_analysis' => '데이터 분석',
            'text_extraction' => '텍스트 추출'
        ];
        
        return $typeMap[$type] ?? '기타';
    }

    private function calculateProgress($analysisData)
    {
        switch ($analysisData['status']) {
            case 'pending': return 0;
            case 'in_progress': return 50;
            case 'completed': return 100;
            case 'failed': return 0;
            case 'cancelled': return 0;
            default: return 0;
        }
    }

    private function notifyAnalysisCompleted($requestId)
    {
        error_log("Analysis completed notification sent for request ID: {$requestId}");
    }

    private function handleAnalysisFailed($requestId)
    {
        error_log("Handling failed analysis for request ID: {$requestId}");
    }

    private function handleAnalysisCancelled($requestId)
    {
        error_log("Handling cancelled analysis for request ID: {$requestId}");
    }
}