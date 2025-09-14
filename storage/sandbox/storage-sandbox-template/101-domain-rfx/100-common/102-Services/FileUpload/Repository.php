<?php

namespace App\Services\FileUpload;

/**
 * RFX 도메인 파일 업로드 데이터 접근 계층
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
                CREATE TABLE IF NOT EXISTS uploaded_files (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    original_name TEXT NOT NULL,
                    stored_name TEXT NOT NULL,
                    file_path TEXT NOT NULL,
                    file_size INTEGER NOT NULL,
                    mime_type VARCHAR(255),
                    status VARCHAR(20) DEFAULT 'active',
                    uploaded_by VARCHAR(100),
                    upload_session_id VARCHAR(255),
                    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    deleted_at DATETIME NULL
                )
            ");
            
        } catch (Exception $e) {
            error_log("File Upload database initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 업로드 기록 생성
     */
    public function create($data)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO uploaded_files (original_name, stored_name, file_path, file_size, mime_type, uploaded_by, upload_session_id, uploaded_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['original_name'],
                $data['stored_name'],
                $data['file_path'],
                $data['file_size'],
                $data['mime_type'] ?? null,
                $data['uploaded_by'] ?? 'system',
                $data['upload_session_id'] ?? null,
                date('Y-m-d H:i:s')
            ]);
            
            return $this->db->lastInsertId();

        } catch (Exception $e) {
            error_log("Create file upload record error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ID로 파일 정보 조회
     */
    public function findById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM uploaded_files WHERE id = ? AND status != 'deleted'");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Find file by ID error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 업로드된 파일 목록 조회
     */
    public function getFiles($conditions = [], $limit = 20, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM uploaded_files WHERE status != 'deleted'";
            $params = [];
            
            if (!empty($conditions['mime_type'])) {
                $sql .= " AND mime_type LIKE ?";
                $params[] = $conditions['mime_type'] . '%';
            }
            
            if (!empty($conditions['uploaded_by'])) {
                $sql .= " AND uploaded_by = ?";
                $params[] = $conditions['uploaded_by'];
            }
            
            if (!empty($conditions['upload_session_id'])) {
                $sql .= " AND upload_session_id = ?";
                $params[] = $conditions['upload_session_id'];
            }
            
            $sql .= " ORDER BY uploaded_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Get files error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 상태 업데이트
     */
    public function updateStatus($id, $status)
    {
        try {
            $sql = "UPDATE uploaded_files SET status = ?, updated_at = ?";
            $params = [$status, date('Y-m-d H:i:s')];
            
            if ($status === 'deleted') {
                $sql .= ", deleted_at = ?";
                $params[] = date('Y-m-d H:i:s');
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount();

        } catch (Exception $e) {
            error_log("Update file status error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 영구 삭제
     */
    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM uploaded_files WHERE id = ?");
            $stmt->execute([$id]);
            
            return $stmt->rowCount();

        } catch (Exception $e) {
            error_log("Delete file record error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 전체 파일 크기 계산
     */
    public function getTotalFileSize($conditions = [])
    {
        try {
            $sql = "SELECT SUM(file_size) as total_size FROM uploaded_files WHERE status != 'deleted'";
            $params = [];
            
            if (!empty($conditions['uploaded_by'])) {
                $sql .= " AND uploaded_by = ?";
                $params[] = $conditions['uploaded_by'];
            }
            
            if (!empty($conditions['upload_session_id'])) {
                $sql .= " AND upload_session_id = ?";
                $params[] = $conditions['upload_session_id'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total_size'] ?? 0;

        } catch (Exception $e) {
            error_log("Get total file size error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 개수 조회
     */
    public function countFiles($conditions = [])
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM uploaded_files WHERE status != 'deleted'";
            $params = [];
            
            if (!empty($conditions['mime_type'])) {
                $sql .= " AND mime_type LIKE ?";
                $params[] = $conditions['mime_type'] . '%';
            }
            
            if (!empty($conditions['uploaded_by'])) {
                $sql .= " AND uploaded_by = ?";
                $params[] = $conditions['uploaded_by'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] ?? 0;

        } catch (Exception $e) {
            error_log("Count files error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 타입별 통계
     */
    public function getFileTypeStatistics()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    CASE 
                        WHEN mime_type LIKE 'image/%' THEN 'image'
                        WHEN mime_type LIKE 'video/%' THEN 'video'
                        WHEN mime_type LIKE 'audio/%' THEN 'audio'
                        WHEN mime_type LIKE 'text/%' THEN 'text'
                        WHEN mime_type LIKE 'application/pdf' THEN 'pdf'
                        WHEN mime_type LIKE 'application/msword' OR mime_type LIKE 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' THEN 'document'
                        ELSE 'other'
                    END as file_type,
                    COUNT(*) as count,
                    SUM(file_size) as total_size
                FROM uploaded_files 
                WHERE status != 'deleted'
                GROUP BY file_type
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Get file type statistics error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 세션별 파일 조회
     */
    public function findBySessionId($sessionId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM uploaded_files WHERE upload_session_id = ? AND status != 'deleted' ORDER BY uploaded_at DESC");
            $stmt->execute([$sessionId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Find files by session ID error: " . $e->getMessage());
            throw $e;
        }
    }
}