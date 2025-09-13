<?php

/**
 * 분석 요청 관리 클래스
 * 파일 분석 요청의 생성, 조회, 수정, 삭제 기능을 제공
 */

class AnalysisRequestManager {
    
    private $dbFile;
    private $db;
    
    public function __construct() {
        $this->dbFile = __DIR__ . '/analysis_requests.sqlite';
        $this->initializeDatabase();
    }
    
    /**
     * 데이터베이스 초기화
     */
    private function initializeDatabase() {
        try {
            $this->db = new PDO('sqlite:' . $this->dbFile);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // analysis_requests 테이블 생성
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS analysis_requests (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    file_id INTEGER,
                    file_name TEXT NOT NULL,
                    file_path TEXT NOT NULL,
                    mime_type TEXT,
                    file_size INTEGER,
                    status TEXT DEFAULT 'pending',
                    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    started_at DATETIME NULL,
                    completed_at DATETIME NULL,
                    analysis_result TEXT NULL,
                    error_message TEXT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // 인덱스 생성
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_status ON analysis_requests(status)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_requested_at ON analysis_requests(requested_at)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_file_id ON analysis_requests(file_id)");
            
        } catch (Exception $e) {
            error_log("Database initialization error: " . $e->getMessage());
            throw new Exception("데이터베이스 초기화에 실패했습니다: " . $e->getMessage());
        }
    }
    
    /**
     * 새로운 분석 요청 생성
     */
    public function createAnalysisRequest($data) {
        try {
            // 필수 필드 검증
            $requiredFields = ['file_name', 'file_path', 'file_size'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("필수 필드가 누락되었습니다: {$field}");
                }
            }
            
            // 동일한 파일에 대한 대기중/처리중인 요청이 있는지 확인
            $existingRequest = $this->checkExistingRequest($data['file_id'] ?? null, $data['file_path']);
            if ($existingRequest) {
                throw new Exception("이미 처리 중인 분석 요청이 있습니다.");
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO analysis_requests (
                    file_id, file_name, file_path, mime_type, file_size, status, requested_at
                ) VALUES (?, ?, ?, ?, ?, 'pending', datetime('now'))
            ");
            
            $stmt->execute([
                $data['file_id'] ?? null,
                $data['file_name'],
                $data['file_path'],
                $data['mime_type'] ?? 'application/octet-stream',
                $data['file_size']
            ]);
            
            $requestId = $this->db->lastInsertId();
            
            return [
                'success' => true,
                'data' => [
                    'id' => $requestId,
                    'message' => '분석 요청이 생성되었습니다.'
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Create analysis request error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 기존 요청 확인
     */
    private function checkExistingRequest($fileId, $filePath) {
        $stmt = $this->db->prepare("
            SELECT id FROM analysis_requests 
            WHERE (file_id = ? OR file_path = ?) 
            AND status IN ('pending', 'processing')
            LIMIT 1
        ");
        $stmt->execute([$fileId, $filePath]);
        return $stmt->fetch();
    }
    
    /**
     * 분석 요청 목록 조회
     */
    public function getAnalysisRequests($filters = []) {
        try {
            $where = [];
            $params = [];
            
            // 상태 필터
            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            
            // 파일명 검색
            if (!empty($filters['search'])) {
                $where[] = "file_name LIKE ?";
                $params[] = '%' . $filters['search'] . '%';
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
                    id, file_id, file_name, file_path, mime_type, file_size,
                    status, requested_at, started_at, completed_at,
                    analysis_result, error_message,
                    created_at, updated_at
                FROM analysis_requests 
                {$whereClause}
                {$orderBy}
                LIMIT {$limit} OFFSET {$offset}
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 총 개수 조회
            $countSql = "SELECT COUNT(*) FROM analysis_requests {$whereClause}";
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
            error_log("Get analysis requests error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => '분석 요청 목록 조회에 실패했습니다: ' . $e->getMessage(),
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
            'file_name_asc' => 'ORDER BY file_name ASC',
            'file_name_desc' => 'ORDER BY file_name DESC',
            'status_asc' => 'ORDER BY status ASC, requested_at DESC'
        ];
        
        return $sortOptions[$sort] ?? 'ORDER BY requested_at DESC';
    }
    
    /**
     * 개별 분석 요청 조회
     */
    public function getAnalysisRequest($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id, file_id, file_name, file_path, mime_type, file_size,
                    status, requested_at, started_at, completed_at,
                    analysis_result, error_message,
                    created_at, updated_at
                FROM analysis_requests 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$request) {
                return [
                    'success' => false,
                    'message' => '분석 요청을 찾을 수 없습니다.'
                ];
            }
            
            return [
                'success' => true,
                'data' => $request
            ];
            
        } catch (Exception $e) {
            error_log("Get analysis request error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => '분석 요청 조회에 실패했습니다: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 분석 요청 상태 업데이트
     */
    public function updateAnalysisRequestStatus($id, $status, $data = []) {
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
                if (isset($data['analysis_result'])) {
                    $updateFields[] = 'analysis_result = ?';
                    $params[] = $data['analysis_result'];
                }
            }
            
            if ($status === 'failed' && isset($data['error_message'])) {
                $updateFields[] = 'error_message = ?';
                $params[] = $data['error_message'];
            }
            
            $params[] = $id;
            
            $sql = "UPDATE analysis_requests SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => '분석 요청 상태가 업데이트되었습니다.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '분석 요청을 찾을 수 없거나 업데이트에 실패했습니다.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Update analysis request status error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => '상태 업데이트에 실패했습니다: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 분석 요청 삭제
     */
    public function deleteAnalysisRequest($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM analysis_requests WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => '분석 요청이 삭제되었습니다.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '분석 요청을 찾을 수 없습니다.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Delete analysis request error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => '분석 요청 삭제에 실패했습니다: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 대기 중인 분석 요청 조회 (처리용)
     */
    public function getPendingRequests($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id, file_id, file_name, file_path, mime_type, file_size,
                    requested_at
                FROM analysis_requests 
                WHERE status = 'pending'
                ORDER BY requested_at ASC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $requests
            ];
            
        } catch (Exception $e) {
            error_log("Get pending requests error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => '대기 중인 요청 조회에 실패했습니다: ' . $e->getMessage(),
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
                FROM analysis_requests 
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
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("Get statistics error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => '통계 조회에 실패했습니다: ' . $e->getMessage(),
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
 * 분석 요청 매니저 인스턴스 반환
 */
function getAnalysisRequestManager() {
    static $instance = null;
    if ($instance === null) {
        $instance = new AnalysisRequestManager();
    }
    return $instance;
}

/**
 * API 응답 헬퍼 함수
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 요청 데이터 검증
 */
function validateAnalysisRequestData($data) {
    $errors = [];
    
    if (empty($data['file_name'])) {
        $errors[] = '파일명이 필요합니다.';
    }
    
    if (empty($data['file_path'])) {
        $errors[] = '파일 경로가 필요합니다.';
    }
    
    if (empty($data['file_size']) || !is_numeric($data['file_size'])) {
        $errors[] = '유효한 파일 크기가 필요합니다.';
    }
    
    return $errors;
}