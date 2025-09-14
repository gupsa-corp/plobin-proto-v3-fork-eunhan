<?php

namespace App\Services\FileManager;

/**
 * RFX 도메인 파일 관리 데이터 접근 계층
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
            
            // 파일 관리 테이블 생성
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS managed_files (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    original_name TEXT NOT NULL,
                    stored_name TEXT NOT NULL,
                    file_path TEXT NOT NULL,
                    download_path TEXT,
                    file_size INTEGER NOT NULL,
                    mime_type VARCHAR(255),
                    file_category VARCHAR(50) DEFAULT 'general',
                    status VARCHAR(20) DEFAULT 'active',
                    access_count INTEGER DEFAULT 0,
                    last_accessed_at DATETIME NULL,
                    managed_by VARCHAR(100),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    deleted_at DATETIME NULL
                )
            ");
            
        } catch (Exception $e) {
            error_log("File Manager database initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 관리 기록 생성
     */
    public function create($data)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO managed_files (original_name, stored_name, file_path, download_path, file_size, mime_type, file_category, managed_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['original_name'],
                $data['stored_name'],
                $data['file_path'],
                $data['download_path'] ?? null,
                $data['file_size'],
                $data['mime_type'] ?? null,
                $data['file_category'] ?? 'general',
                $data['managed_by'] ?? 'system',
                date('Y-m-d H:i:s')
            ]);
            
            return $this->db->lastInsertId();

        } catch (Exception $e) {
            error_log("Create managed file record error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ID로 파일 정보 조회
     */
    public function findById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM managed_files WHERE id = ? AND status != 'deleted'");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Find managed file by ID error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 저장된 파일명으로 조회
     */
    public function findByStoredName($storedName)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM managed_files WHERE stored_name = ? AND status != 'deleted'");
            $stmt->execute([$storedName]);
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Find managed file by stored name error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 목록 조회
     */
    public function getFiles($conditions = [], $limit = 20, $offset = 0)
    {
        try {
            $sql = "SELECT * FROM managed_files WHERE status != 'deleted'";
            $params = [];
            
            if (!empty($conditions['file_category'])) {
                $sql .= " AND file_category = ?";
                $params[] = $conditions['file_category'];
            }
            
            if (!empty($conditions['mime_type'])) {
                $sql .= " AND mime_type LIKE ?";
                $params[] = $conditions['mime_type'] . '%';
            }
            
            if (!empty($conditions['managed_by'])) {
                $sql .= " AND managed_by = ?";
                $params[] = $conditions['managed_by'];
            }
            
            if (!empty($conditions['search'])) {
                $sql .= " AND (original_name LIKE ? OR stored_name LIKE ?)";
                $params[] = '%' . $conditions['search'] . '%';
                $params[] = '%' . $conditions['search'] . '%';
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Get managed files error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 상태 업데이트
     */
    public function updateStatus($id, $status)
    {
        try {
            $sql = "UPDATE managed_files SET status = ?, updated_at = ?";
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
            error_log("Update managed file status error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 접근 기록 업데이트
     */
    public function updateAccess($id)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE managed_files 
                SET access_count = access_count + 1, last_accessed_at = ?, updated_at = ?
                WHERE id = ?
            ");
            $stmt->execute([date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $id]);
            
            return $stmt->rowCount();

        } catch (Exception $e) {
            error_log("Update file access error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 영구 삭제
     */
    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM managed_files WHERE id = ?");
            $stmt->execute([$id]);
            
            return $stmt->rowCount();

        } catch (Exception $e) {
            error_log("Delete managed file record error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 전체 파일 크기 계산
     */
    public function getTotalFileSize($conditions = [])
    {
        try {
            $sql = "SELECT SUM(file_size) as total_size FROM managed_files WHERE status != 'deleted'";
            $params = [];
            
            if (!empty($conditions['file_category'])) {
                $sql .= " AND file_category = ?";
                $params[] = $conditions['file_category'];
            }
            
            if (!empty($conditions['managed_by'])) {
                $sql .= " AND managed_by = ?";
                $params[] = $conditions['managed_by'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total_size'] ?? 0;

        } catch (Exception $e) {
            error_log("Get total managed file size error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 파일 개수 조회
     */
    public function countFiles($conditions = [])
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM managed_files WHERE status != 'deleted'";
            $params = [];
            
            if (!empty($conditions['file_category'])) {
                $sql .= " AND file_category = ?";
                $params[] = $conditions['file_category'];
            }
            
            if (!empty($conditions['managed_by'])) {
                $sql .= " AND managed_by = ?";
                $params[] = $conditions['managed_by'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] ?? 0;

        } catch (Exception $e) {
            error_log("Count managed files error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 카테고리별 파일 통계
     */
    public function getCategoryStatistics()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    file_category,
                    COUNT(*) as count,
                    SUM(file_size) as total_size,
                    AVG(access_count) as avg_access_count
                FROM managed_files 
                WHERE status != 'deleted'
                GROUP BY file_category
                ORDER BY count DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Get category statistics error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 인기 파일 조회 (접근 횟수 기준)
     */
    public function getPopularFiles($limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM managed_files 
                WHERE status != 'deleted' AND access_count > 0
                ORDER BY access_count DESC, last_accessed_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Get popular files error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 최근 접근 파일 조회
     */
    public function getRecentlyAccessedFiles($limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM managed_files 
                WHERE status != 'deleted' AND last_accessed_at IS NOT NULL
                ORDER BY last_accessed_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Get recently accessed files error: " . $e->getMessage());
            throw $e;
        }
    }
}