<?php

/**
 * PMS 요약 요청 관리 클래스
 * 프로젝트 관리 시스템 요약 요청의 생성, 조회, 수정, 삭제 기능을 제공
 */

class PMSSummaryRequestManager {
    
    private $dbFile;
    private $db;
    
    public function __construct() {
        $this->dbFile = __DIR__ . '/pms_summary_requests.sqlite';
        $this->initializeDatabase();
    }
    
    /**
     * 데이터베이스 초기화
     */
    private function initializeDatabase() {
        try {
            $this->db = new PDO('sqlite:' . $this->dbFile);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // pms_summary_requests 테이블 생성
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS pms_summary_requests (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    description TEXT,
                    request_type TEXT DEFAULT 'project_summary',
                    priority TEXT DEFAULT 'medium',
                    status TEXT DEFAULT 'pending',
                    start_date DATE NULL,
                    end_date DATE NULL,
                    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    started_at DATETIME NULL,
                    completed_at DATETIME NULL,
                    summary_result TEXT NULL,
                    error_message TEXT NULL,
                    requester_name TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // 인덱스 생성
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_pms_status ON pms_summary_requests(status)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_pms_priority ON pms_summary_requests(priority)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_pms_requested_at ON pms_summary_requests(requested_at)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_pms_type ON pms_summary_requests(request_type)");
            
        } catch (Exception $e) {
            error_log("PMS Database initialization error: " . $e->getMessage());
            throw new Exception("PMS 데이터베이스 초기화에 실패했습니다: " . $e->getMessage());
        }
    }
    
    /**
     * 새로운 PMS 요약 요청 생성
     */
    public function createSummaryRequest($data) {
        try {
            // 필수 필드 검증
            if (empty($data['title'])) {
                throw new Exception("제목이 필요합니다.");
            }
            
            // 유효한 값들 검증
            $validTypes = ['project_summary', 'task_summary', 'progress_summary', 'issue_summary', 'report_summary', 'meeting_summary'];
            $validPriorities = ['low', 'medium', 'high', 'urgent'];
            
            $requestType = in_array($data['request_type'] ?? '', $validTypes) ? $data['request_type'] : 'project_summary';
            $priority = in_array($data['priority'] ?? '', $validPriorities) ? $data['priority'] : 'medium';
            
            // 날짜 검증
            $startDate = null;
            $endDate = null;
            
            if (!empty($data['start_date'])) {
                $startDate = date('Y-m-d', strtotime($data['start_date']));
            }
            
            if (!empty($data['end_date'])) {
                $endDate = date('Y-m-d', strtotime($data['end_date']));
            }
            
            // 종료일이 시작일보다 이른 경우 검증
            if ($startDate && $endDate && $endDate < $startDate) {
                throw new Exception("종료일은 시작일보다 늦어야 합니다.");
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO pms_summary_requests (
                    title, description, request_type, priority, status,
                    start_date, end_date, requested_at, requester_name
                ) VALUES (?, ?, ?, ?, 'pending', ?, ?, datetime('now'), ?)
            ");
            
            $stmt->execute([
                $data['title'],
                $data['description'] ?? null,
                $requestType,
                $priority,
                $startDate,
                $endDate,
                $data['requester_name'] ?? 'System'
            ]);
            
            $requestId = $this->db->lastInsertId();
            
            return [
                'success' => true,
                'data' => [
                    'id' => $requestId,
                    'message' => 'PMS 요약 요청이 생성되었습니다.'
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Create PMS summary request error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * PMS 요약 요청 목록 조회
     */
    public function getSummaryRequests($filters = []) {
        try {
            $where = [];
            $params = [];
            
            // 상태 필터
            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            
            // 우선순위 필터
            if (!empty($filters['priority'])) {
                $where[] = "priority = ?";
                $params[] = $filters['priority'];
            }
            
            // 요청 유형 필터
            if (!empty($filters['request_type'])) {
                $where[] = "request_type = ?";
                $params[] = $filters['request_type'];
            }
            
            // 제목 및 설명 검색
            if (!empty($filters['search'])) {
                $where[] = "(title LIKE ? OR description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // WHERE 절 구성
            $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
            
            // 정렬
            $orderBy = $this->getOrderByClause($filters['sort'] ?? 'requested_at_desc');
            
            // 페이지네이션
            $limit = $filters['limit'] ?? 50;
            $offset = $filters['offset'] ?? 0;
            
            $sql = "
                SELECT 
                    id, title, description, request_type, priority, status,
                    start_date, end_date, requested_at, started_at, completed_at,
                    summary_result, error_message, requester_name,
                    created_at, updated_at
                FROM pms_summary_requests 
                {$whereClause}
                {$orderBy}
                LIMIT {$limit} OFFSET {$offset}
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 총 개수 조회
            $countSql = "SELECT COUNT(*) FROM pms_summary_requests {$whereClause}";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            return [
                'success' => true,
                'data' => $requests,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ];
            
        } catch (Exception $e) {
            error_log("Get PMS summary requests error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'PMS 요약 요청 목록 조회에 실패했습니다: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * 정렬 절 생성
     */
    private function getOrderByClause($sort) {
        $sortOptions = [
            'requested_at_desc' => 'ORDER BY requested_at DESC',
            'requested_at_asc' => 'ORDER BY requested_at ASC',
            'title_asc' => 'ORDER BY title ASC',
            'title_desc' => 'ORDER BY title DESC',
            'priority_desc' => 'ORDER BY CASE priority WHEN \'urgent\' THEN 4 WHEN \'high\' THEN 3 WHEN \'medium\' THEN 2 WHEN \'low\' THEN 1 END DESC, requested_at DESC',
            'status_asc' => 'ORDER BY status ASC, requested_at DESC'
        ];
        
        return $sortOptions[$sort] ?? 'ORDER BY requested_at DESC';
    }
    
    /**
     * 개별 PMS 요약 요청 조회
     */
    public function getSummaryRequest($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id, title, description, request_type, priority, status,
                    start_date, end_date, requested_at, started_at, completed_at,
                    summary_result, error_message, requester_name,
                    created_at, updated_at
                FROM pms_summary_requests 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$request) {
                return [
                    'success' => false,
                    'message' => 'PMS 요약 요청을 찾을 수 없습니다.'
                ];
            }
            
            return [
                'success' => true,
                'data' => $request
            ];
            
        } catch (Exception $e) {
            error_log("Get PMS summary request error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'PMS 요약 요청 조회에 실패했습니다: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * PMS 요약 요청 상태 업데이트
     */
    public function updateSummaryRequestStatus($id, $status, $data = []) {
        try {
            $updateFields = ['status = ?', 'updated_at = datetime(\'now\')'];
            $params = [$status];
            
            // 상태별 추가 필드 업데이트
            if ($status === 'processing' && empty($data['started_at'])) {
                $updateFields[] = 'started_at = datetime(\'now\')';
            } elseif (isset($data['started_at'])) {
                $updateFields[] = 'started_at = ?';
                $params[] = $data['started_at'];
            }
            
            if ($status === 'completed') {
                $updateFields[] = 'completed_at = datetime(\'now\')';
                if (isset($data['summary_result'])) {
                    $updateFields[] = 'summary_result = ?';
                    $params[] = $data['summary_result'];
                }
            }
            
            if ($status === 'failed' && isset($data['error_message'])) {
                $updateFields[] = 'error_message = ?';
                $params[] = $data['error_message'];
            }
            
            $params[] = $id;
            
            $sql = "UPDATE pms_summary_requests SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'PMS 요약 요청 상태가 업데이트되었습니다.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'PMS 요약 요청을 찾을 수 없거나 업데이트에 실패했습니다.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Update PMS summary request status error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => '상태 업데이트에 실패했습니다: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * PMS 요약 요청 수정
     */
    public function updateSummaryRequest($id, $data) {
        try {
            $updateFields = [];
            $params = [];
            
            // 수정 가능한 필드들
            $allowedFields = ['title', 'description', 'request_type', 'priority', 'start_date', 'end_date'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    if ($field === 'start_date' || $field === 'end_date') {
                        $updateFields[] = "{$field} = ?";
                        $params[] = !empty($data[$field]) ? date('Y-m-d', strtotime($data[$field])) : null;
                    } else {
                        $updateFields[] = "{$field} = ?";
                        $params[] = $data[$field];
                    }
                }
            }
            
            if (empty($updateFields)) {
                return [
                    'success' => false,
                    'message' => '수정할 내용이 없습니다.'
                ];
            }
            
            $updateFields[] = 'updated_at = datetime(\'now\')';
            $params[] = $id;
            
            $sql = "UPDATE pms_summary_requests SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'PMS 요약 요청이 수정되었습니다.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'PMS 요약 요청을 찾을 수 없거나 수정에 실패했습니다.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Update PMS summary request error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'PMS 요약 요청 수정에 실패했습니다: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * PMS 요약 요청 삭제
     */
    public function deleteSummaryRequest($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM pms_summary_requests WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'PMS 요약 요청이 삭제되었습니다.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'PMS 요약 요청을 찾을 수 없습니다.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Delete PMS summary request error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'PMS 요약 요청 삭제에 실패했습니다: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 대기 중인 PMS 요약 요청 조회 (처리용)
     */
    public function getPendingSummaryRequests($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id, title, description, request_type, priority,
                    start_date, end_date, requested_at, requester_name
                FROM pms_summary_requests 
                WHERE status = 'pending'
                ORDER BY 
                    CASE priority 
                        WHEN 'urgent' THEN 4 
                        WHEN 'high' THEN 3 
                        WHEN 'medium' THEN 2 
                        WHEN 'low' THEN 1 
                    END DESC,
                    requested_at ASC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $requests
            ];
            
        } catch (Exception $e) {
            error_log("Get pending PMS summary requests error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => '대기 중인 PMS 요약 요청 조회에 실패했습니다: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * 통계 정보 조회
     */
    public function getStatistics() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    status,
                    COUNT(*) as count
                FROM pms_summary_requests 
                GROUP BY status
            ");
            $stmt->execute();
            $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stats = [
                'pending' => 0,
                'processing' => 0,
                'completed' => 0,
                'failed' => 0,
                'total' => 0
            ];
            
            foreach ($statusCounts as $statusCount) {
                $stats[$statusCount['status']] = (int)$statusCount['count'];
                $stats['total'] += (int)$statusCount['count'];
            }
            
            // 우선순위별 통계
            $priorityStmt = $this->db->prepare("
                SELECT 
                    priority,
                    COUNT(*) as count
                FROM pms_summary_requests 
                WHERE status IN ('pending', 'processing')
                GROUP BY priority
            ");
            $priorityStmt->execute();
            $priorityCounts = $priorityStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $priorityStats = [
                'urgent' => 0,
                'high' => 0,
                'medium' => 0,
                'low' => 0
            ];
            
            foreach ($priorityCounts as $priorityCount) {
                $priorityStats[$priorityCount['priority']] = (int)$priorityCount['count'];
            }
            
            $stats['by_priority'] = $priorityStats;
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("Get PMS summary statistics error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'PMS 요약 통계 조회에 실패했습니다: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * 데이터베이스 연결 종료
     */
    public function __destruct() {
        $this->db = null;
    }
}

/**
 * PMS 요약 요청 매니저 인스턴스 반환
 */
function getPMSSummaryRequestManager() {
    static $instance = null;
    if ($instance === null) {
        $instance = new PMSSummaryRequestManager();
    }
    return $instance;
}

/**
 * PMS 요약 요청 데이터 검증
 */
function validatePMSSummaryRequestData($data) {
    $errors = [];
    
    if (empty($data['title'])) {
        $errors[] = '제목이 필요합니다.';
    }
    
    $validTypes = ['project_summary', 'task_summary', 'progress_summary', 'issue_summary', 'report_summary', 'meeting_summary'];
    if (!empty($data['request_type']) && !in_array($data['request_type'], $validTypes)) {
        $errors[] = '유효하지 않은 요청 유형입니다.';
    }
    
    $validPriorities = ['low', 'medium', 'high', 'urgent'];
    if (!empty($data['priority']) && !in_array($data['priority'], $validPriorities)) {
        $errors[] = '유효하지 않은 우선순위입니다.';
    }
    
    // 날짜 검증
    if (!empty($data['start_date']) && !strtotime($data['start_date'])) {
        $errors[] = '유효하지 않은 시작일입니다.';
    }
    
    if (!empty($data['end_date']) && !strtotime($data['end_date'])) {
        $errors[] = '유효하지 않은 종료일입니다.';
    }
    
    if (!empty($data['start_date']) && !empty($data['end_date'])) {
        $startDate = strtotime($data['start_date']);
        $endDate = strtotime($data['end_date']);
        if ($endDate < $startDate) {
            $errors[] = '종료일은 시작일보다 늦어야 합니다.';
        }
    }
    
    return $errors;
}