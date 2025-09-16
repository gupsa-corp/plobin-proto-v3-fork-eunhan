<?php

namespace App\Services\AnalysisRequest;

/**
 * RFX 도메인 분석 요청 데이터 접근 계층
 */
class Repository
{
    private $dbFile;
    private $db;
    
    public function __construct()
    {
        $this->dbFile = __DIR__ . '/../../database/analysis_requests.sqlite';
        $this->initializeDatabase();
    }
    
    /**
     * 데이터베이스 초기화
     */
    private function initializeDatabase()
    {
        try {
            $this->db = new PDO('sqlite:' . $this->dbFile);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 테이블 생성
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS analysis_requests (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    file_id INTEGER NOT NULL,
                    request_type VARCHAR(50) DEFAULT 'document_analysis',
                    status VARCHAR(20) DEFAULT 'pending',
                    priority INTEGER DEFAULT 1,
                    requested_by VARCHAR(100),
                    request_data TEXT,
                    result_data TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    completed_at DATETIME NULL
                )
            ");
            
        } catch (Exception $e) {
            error_log("Analysis Request database initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 분석 요청 생성
     */
    public function create($data)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO analysis_requests (file_id, request_type, priority, requested_by, request_data, created_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['file_id'],
                $data['request_type'] ?? 'document_analysis',
                $data['priority'] ?? 1,
                $data['requested_by'] ?? 'system',
                json_encode($data),
                date('Y-m-d H:i:s')
            ]);
            
            return $this->db->lastInsertId();

        } catch (Exception $e) {
            error_log("Create analysis request error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ID로 분석 요청 조회
     */
    public function findById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM analysis_requests WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $result['request_data'] = json_decode($result['request_data'], true);
                $result['result_data'] = json_decode($result['result_data'], true);
            }
            
            return $result;

        } catch (Exception $e) {
            error_log("Find analysis request error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 ID로 분석 요청 조회
     */
    public function findByFileId($fileId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM analysis_requests WHERE file_id = ? ORDER BY created_at DESC");
            $stmt->execute([$fileId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                $result['request_data'] = json_decode($result['request_data'], true);
                $result['result_data'] = json_decode($result['result_data'], true);
            }
            
            return $results;

        } catch (Exception $e) {
            error_log("Find analysis requests by file ID error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 조건에 따른 분석 요청 목록 조회
     */
    public function findByConditions($conditions = [], $limit = 20, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM analysis_requests";
            $params = [];
            $whereClauses = [];
            
            if (!empty($conditions['status'])) {
                $whereClauses[] = "status = ?";
                $params[] = $conditions['status'];
            }
            
            if (!empty($conditions['request_type'])) {
                $whereClauses[] = "request_type = ?";
                $params[] = $conditions['request_type'];
            }
            
            if (!empty($conditions['requested_by'])) {
                $whereClauses[] = "requested_by = ?";
                $params[] = $conditions['requested_by'];
            }
            
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(" AND ", $whereClauses);
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                $result['request_data'] = json_decode($result['request_data'], true);
                $result['result_data'] = json_decode($result['result_data'], true);
            }
            
            return $results;

        } catch (Exception $e) {
            error_log("Find analysis requests error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 분석 요청 상태 및 결과 업데이트
     */
    public function updateStatusAndResult($id, $status, $resultData = null)
    {
        try {
            $sql = "UPDATE analysis_requests SET status = ?, updated_at = ?";
            $params = [$status, date('Y-m-d H:i:s')];
            
            if ($resultData) {
                $sql .= ", result_data = ?";
                $params[] = json_encode($resultData);
            }
            
            if ($status === 'completed') {
                $sql .= ", completed_at = ?";
                $params[] = date('Y-m-d H:i:s');
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount();

        } catch (Exception $e) {
            error_log("Update analysis request error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 분석 요청 삭제
     */
    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM analysis_requests WHERE id = ?");
            $stmt->execute([$id]);
            
            return $stmt->rowCount();

        } catch (Exception $e) {
            error_log("Delete analysis request error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 상태별 분석 요청 개수
     */
    public function countByStatus($status = null)
    {
        try {
            if ($status) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM analysis_requests WHERE status = ?");
                $stmt->execute([$status]);
            } else {
                $stmt = $this->db->prepare("
                    SELECT status, COUNT(*) as count 
                    FROM analysis_requests 
                    GROUP BY status
                ");
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $counts = [];
                foreach ($results as $result) {
                    $counts[$result['status']] = $result['count'];
                }
                return $counts;
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;

        } catch (Exception $e) {
            error_log("Count analysis requests error: " . $e->getMessage());
            throw $e;
        }
    }
}