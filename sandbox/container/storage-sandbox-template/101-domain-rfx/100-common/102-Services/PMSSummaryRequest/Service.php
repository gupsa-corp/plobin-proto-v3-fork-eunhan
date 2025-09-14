<?php

namespace App\Services\PMSSummaryRequest;

/**
 * RFX 도메인 PMS 요약 요청 서비스
 * (기존 002-pms-summary-requests.php 내용 통합)
 */
class Service
{
    private $repository;
    
    public function __construct()
    {
        $this->repository = new Repository();
    }
    
    /**
     * PMS 요약 요청 생성
     */
    public function createSummaryRequest($projectId, $summaryType = 'project_summary', $options = [])
    {
        try {
            // 비즈니스 로직 검증
            if (empty($projectId)) {
                throw new \Exception('프로젝트 ID는 필수입니다.');
            }

            // Repository를 통한 데이터 저장
            $data = array_merge($options, [
                'project_id' => $projectId,
                'summary_type' => $summaryType
            ]);
            
            $requestId = $this->repository->create($data);
            
            // 비즈니스 로직: 자동 요약 생성 큐에 추가
            $this->enqueueSummaryGeneration($requestId);
            
            return [
                'id' => $requestId,
                'project_id' => $projectId,
                'summary_type' => $summaryType,
                'status' => 'pending'
            ];
            
        } catch (Exception $e) {
            error_log("Create PMS summary request error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * PMS 요약 요청 조회
     */
    public function getSummaryRequest($requestId)
    {
        try {
            if (empty($requestId)) {
                throw new \Exception('요청 ID는 필수입니다.');
            }

            $result = $this->repository->findById($requestId);
            
            if (!$result) {
                throw new \Exception('요약 요청을 찾을 수 없습니다.');
            }
            
            // 비즈니스 로직: 상태별 추가 정보 제공
            $result = $this->enrichRequestData($result);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Get PMS summary request error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * PMS 요약 요청 목록 조회
     */
    public function getSummaryRequests($options = [])
    {
        try {
            $limit = $options['limit'] ?? 20;
            $offset = $options['offset'] ?? 0;
            $conditions = [
                'status' => $options['status'] ?? null,
                'project_id' => $options['project_id'] ?? null
            ];
            
            // 빈 조건 제거
            $conditions = array_filter($conditions);
            
            $results = $this->repository->findByConditions($conditions, $limit, $offset);
            
            // 비즈니스 로직: 각 요청에 추가 정보 제공
            foreach ($results as &$result) {
                $result = $this->enrichRequestData($result);
            }
            
            return $results;
            
        } catch (Exception $e) {
            error_log("Get PMS summary requests error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * PMS 요약 요청 상태 업데이트
     */
    public function updateSummaryRequestStatus($requestId, $status, $summaryData = null)
    {
        try {
            if (empty($requestId) || empty($status)) {
                throw new \Exception('요청 ID와 상태는 필수입니다.');
            }

            // 비즈니스 로직: 유효한 상태 검증
            $validStatuses = ['pending', 'in_progress', 'completed', 'failed', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                throw new \Exception('유효하지 않은 상태입니다.');
            }

            $updatedRows = $this->repository->updateStatusAndData($requestId, $status, $summaryData);
            
            if ($updatedRows === 0) {
                throw new \Exception('요약 요청을 찾을 수 없습니다.');
            }
            
            // 비즈니스 로직: 완료 시 알림 발송
            if ($status === 'completed') {
                $this->notifyRequestCompleted($requestId);
            }
            
            return ['success' => true, 'updated_rows' => $updatedRows];
            
        } catch (Exception $e) {
            error_log("Update PMS summary request status error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * PMS 요약 요청 삭제
     */
    public function deleteSummaryRequest($requestId)
    {
        try {
            if (empty($requestId)) {
                throw new \Exception('요청 ID는 필수입니다.');
            }

            // 비즈니스 로직: 진행 중인 요청은 삭제 불가
            $request = $this->repository->findById($requestId);
            if (!$request) {
                throw new \Exception('요약 요청을 찾을 수 없습니다.');
            }
            
            if ($request['status'] === 'in_progress') {
                throw new \Exception('진행 중인 요약 요청은 삭제할 수 없습니다.');
            }

            $deletedRows = $this->repository->delete($requestId);
            
            return ['success' => true, 'deleted_rows' => $deletedRows];
            
        } catch (Exception $e) {
            error_log("Delete PMS summary request error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 프로젝트별 요약 생성
     */
    public function generateProjectSummary($projectId, $options = [])
    {
        try {
            if (empty($projectId)) {
                throw new \Exception('프로젝트 ID는 필수입니다.');
            }

            // 비즈니스 로직: 프로젝트 데이터 수집 및 분석
            $summaryData = [
                'project_id' => $projectId,
                'total_tasks' => $this->getProjectTaskCount($projectId),
                'completed_tasks' => $this->getCompletedTaskCount($projectId),
                'pending_tasks' => $this->getPendingTaskCount($projectId),
                'progress_percentage' => $this->calculateProgressPercentage($projectId),
                'generated_at' => date('Y-m-d H:i:s'),
                'summary_text' => $this->generateSummaryText($projectId)
            ];
            
            return $summaryData;
            
        } catch (Exception $e) {
            error_log("Generate project summary error: " . $e->getMessage());
            throw $e;
        }
    }

    // === 비즈니스 로직 헬퍼 메서드들 ===

    /**
     * 요약 생성을 큐에 추가
     */
    private function enqueueSummaryGeneration($requestId)
    {
        // TODO: 실제 큐 시스템과 연동
        error_log("Summary generation queued for request ID: {$requestId}");
    }

    /**
     * 요청 데이터에 추가 정보 제공
     */
    private function enrichRequestData($requestData)
    {
        // 비즈니스 로직: 상태별 추가 정보
        $requestData['status_display'] = $this->getStatusDisplay($requestData['status']);
        $requestData['estimated_completion'] = $this->estimateCompletion($requestData);
        
        return $requestData;
    }

    /**
     * 요청 완료 알림
     */
    private function notifyRequestCompleted($requestId)
    {
        // TODO: 실제 알림 시스템과 연동
        error_log("Notification sent for completed request ID: {$requestId}");
    }

    /**
     * 상태 표시명 반환
     */
    private function getStatusDisplay($status)
    {
        $statusMap = [
            'pending' => '대기중',
            'in_progress' => '진행중',
            'completed' => '완료',
            'failed' => '실패',
            'cancelled' => '취소됨'
        ];
        
        return $statusMap[$status] ?? '알 수 없음';
    }

    /**
     * 완료 예상 시간 계산
     */
    private function estimateCompletion($requestData)
    {
        if ($requestData['status'] === 'completed') {
            return null;
        }
        
        // 비즈니스 로직: 우선순위와 요청 타입에 따른 예상 시간
        $priority = $requestData['priority'] ?? 1;
        $baseMinutes = $requestData['summary_type'] === 'detailed_summary' ? 30 : 15;
        $adjustedMinutes = $baseMinutes / $priority;
        
        return date('Y-m-d H:i:s', strtotime("+{$adjustedMinutes} minutes"));
    }

    // 프로젝트 관련 헬퍼 메서드들 (실제 구현에서는 다른 서비스에서 가져와야 함)
    private function getProjectTaskCount($projectId) { return rand(10, 100); }
    private function getCompletedTaskCount($projectId) { return rand(5, 50); }
    private function getPendingTaskCount($projectId) { return rand(1, 20); }
    private function calculateProgressPercentage($projectId) { return rand(20, 90); }
    private function generateSummaryText($projectId) { 
        return "프로젝트 {$projectId}에 대한 자동 생성된 요약입니다."; 
    }
}