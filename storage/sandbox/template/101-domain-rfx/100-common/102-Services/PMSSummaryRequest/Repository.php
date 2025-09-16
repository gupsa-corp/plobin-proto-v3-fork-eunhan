<?php

namespace App\Services\PMSSummaryRequest;

/**
 * RFX 도메인 PMS 요약 요청 데이터 접근 계층
 */
class Repository
{
    private $dbFile;
    private $db;
    
    public function __construct()
    {
        $this->dbFile = __DIR__ . '/../../database/pms_summary_requests.sqlite';
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
                CREATE TABLE IF NOT EXISTS pms_summary_requests (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    project_id INTEGER NOT NULL,
                    summary_type VARCHAR(50) DEFAULT 'project_summary',
                    status VARCHAR(20) DEFAULT 'pending',
                    priority INTEGER DEFAULT 1,
                    requested_by VARCHAR(100),
                    request_data TEXT,
                    summary_data TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    completed_at DATETIME NULL
                )
            ");
            
        } catch (Exception $e) {
            error_log("PMS Summary database initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 요약 요청 생성
     */
    public function create($data)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO pms_summary_requests (project_id, summary_type, priority, requested_by, request_data, created_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['project_id'],
                $data['summary_type'] ?? 'project_summary',
                $data['priority'] ?? 1,
                $data['requested_by'] ?? 'system',
                json_encode($data),
                date('Y-m-d H:i:s')
            ]);
            
            return $this->db->lastInsertId();

        } catch (Exception $e) {
            error_log("Create PMS summary request error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ID로 요약 요청 조회
     */
    public function findById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM pms_summary_requests WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $result['request_data'] = json_decode($result['request_data'], true);
                $result['summary_data'] = json_decode($result['summary_data'], true);
            }
            
            return $result;

        } catch (Exception $e) {
            error_log("Find PMS summary request error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 조건에 따른 요약 요청 목록 조회
     */
    public function findByConditions($conditions = [], $limit = 20, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM pms_summary_requests";
            $params = [];
            $whereClauses = [];
            
            if (!empty($conditions['status'])) {
                $whereClauses[] = "status = ?";
                $params[] = $conditions['status'];
            }
            
            if (!empty($conditions['project_id'])) {
                $whereClauses[] = "project_id = ?";
                $params[] = $conditions['project_id'];
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
                $result['summary_data'] = json_decode($result['summary_data'], true);
            }
            
            return $results;

        } catch (Exception $e) {
            error_log("Find PMS summary requests error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 요약 요청 상태 및 데이터 업데이트
     */
    public function updateStatusAndData($id, $status, $summaryData = null)
    {
        try {
            $sql = "UPDATE pms_summary_requests SET status = ?, updated_at = ?";
            $params = [$status, date('Y-m-d H:i:s')];
            
            if ($summaryData) {
                $sql .= ", summary_data = ?";
                $params[] = json_encode($summaryData);
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
            error_log("Update PMS summary request error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 요약 요청 삭제
     */
    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM pms_summary_requests WHERE id = ?");
            $stmt->execute([$id]);
            
            return $stmt->rowCount();

        } catch (Exception $e) {
            error_log("Delete PMS summary request error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 프로젝트별 요약 요청 개수
     */
    public function countByProject($projectId)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM pms_summary_requests WHERE project_id = ?");
            $stmt->execute([$projectId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] ?? 0;

        } catch (Exception $e) {
            error_log("Count PMS summary requests error: " . $e->getMessage());
            throw $e;
        }
    }
}