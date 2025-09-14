<?php

namespace App\Services\DocumentAnalysis;

/**
 * RFX 도메인 문서 분석 데이터 접근 계층
 */
class Repository
{
    private $dbFile;
    private $db;
    
    public function __construct()
    {
        $this->dbFile = __DIR__ . '/../../database/release.sqlite';
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
                CREATE TABLE IF NOT EXISTS document_analysis (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    file_path TEXT NOT NULL,
                    status VARCHAR(20) DEFAULT 'pending',
                    result TEXT,
                    analysis_type VARCHAR(50) DEFAULT 'general',
                    confidence_score DECIMAL(3,2),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    completed_at DATETIME NULL
                )
            ");
            
        } catch (Exception $e) {
            error_log("Document Analysis database initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 문서 분석 기록 생성
     */
    public function create($data)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO document_analysis (file_path, status, result, analysis_type, confidence_score, created_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['file_path'],
                $data['status'] ?? 'pending',
                isset($data['result']) ? json_encode($data['result']) : null,
                $data['analysis_type'] ?? 'general',
                $data['confidence_score'] ?? null,
                date('Y-m-d H:i:s')
            ]);
            
            return $this->db->lastInsertId();

        } catch (Exception $e) {
            error_log("Create document analysis error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ID로 문서 분석 기록 조회
     */
    public function findById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM document_analysis WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['result']) {
                $result['result'] = json_decode($result['result'], true);
            }
            
            return $result;

        } catch (Exception $e) {
            error_log("Find document analysis error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 경로로 문서 분석 기록 조회
     */
    public function findByFilePath($filePath)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM document_analysis WHERE file_path = ? ORDER BY created_at DESC");
            $stmt->execute([$filePath]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                if ($result['result']) {
                    $result['result'] = json_decode($result['result'], true);
                }
            }
            
            return $results;

        } catch (Exception $e) {
            error_log("Find document analysis by file path error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 분석 히스토리 조회
     */
    public function getHistory($limit = 20, $offset = 0, $status = null)
    {
        try {
            $sql = "SELECT * FROM document_analysis";
            $params = [];
            
            if ($status) {
                $sql .= " WHERE status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($results as &$result) {
                if ($result['result']) {
                    $result['result'] = json_decode($result['result'], true);
                }
            }
            
            return $results;

        } catch (Exception $e) {
            error_log("Get document analysis history error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 분석 상태 및 결과 업데이트
     */
    public function updateStatusAndResult($id, $status, $result = null, $confidenceScore = null)
    {
        try {
            $sql = "UPDATE document_analysis SET status = ?, updated_at = ?";
            $params = [$status, date('Y-m-d H:i:s')];
            
            if ($result) {
                $sql .= ", result = ?";
                $params[] = json_encode($result);
            }
            
            if ($confidenceScore) {
                $sql .= ", confidence_score = ?";
                $params[] = $confidenceScore;
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
            error_log("Update document analysis error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 분석 기록 삭제
     */
    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM document_analysis WHERE id = ?");
            $stmt->execute([$id]);
            
            return $stmt->rowCount();

        } catch (Exception $e) {
            error_log("Delete document analysis error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 상태별 분석 개수
     */
    public function countByStatus($status = null)
    {
        try {
            if ($status) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM document_analysis WHERE status = ?");
                $stmt->execute([$status]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['count'] ?? 0;
            } else {
                $stmt = $this->db->prepare("
                    SELECT status, COUNT(*) as count 
                    FROM document_analysis 
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

        } catch (Exception $e) {
            error_log("Count document analysis error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 분석 타입별 개수
     */
    public function countByAnalysisType()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT analysis_type, COUNT(*) as count 
                FROM document_analysis 
                GROUP BY analysis_type
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $counts = [];
            foreach ($results as $result) {
                $counts[$result['analysis_type']] = $result['count'];
            }
            return $counts;

        } catch (Exception $e) {
            error_log("Count document analysis by type error: " . $e->getMessage());
            throw $e;
        }
    }
}