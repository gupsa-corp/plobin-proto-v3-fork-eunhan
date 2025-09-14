<?php
/**
 * 폼 제출 데이터베이스 헬퍼 함수
 */

require_once 'pms-database.php';

/**
 * FormSubmissionDB 클래스 - 폼 제출 데이터 관리
 */
class FormSubmissionDB {
    private $db;
    
    public function __construct() {
        // 데이터베이스 경로를 현재 디렉토리의 release.sqlite로 수정
        $dbPath = __DIR__ . '/release.sqlite';
        
        try {
            $this->db = new PDO("sqlite:$dbPath", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            // SQLite 설정
            $this->db->exec('PRAGMA foreign_keys = ON');
            $this->db->exec('PRAGMA journal_mode = WAL');
            
        } catch (PDOException $e) {
            throw new Exception('폼 데이터베이스 연결 실패: ' . $e->getMessage());
        }
    }
    
    /**
     * 폼 제출 데이터 저장
     */
    public function saveFormSubmission($formName, $formData, $metadata = []) {
        try {
            $sql = "
                INSERT INTO form_submissions (
                    form_name, 
                    form_data, 
                    ip_address, 
                    user_agent, 
                    session_id, 
                    submitted_at
                ) VALUES (?, ?, ?, ?, ?, ?)
            ";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $formName,
                json_encode($formData, JSON_UNESCAPED_UNICODE),
                $metadata['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
                $metadata['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null,
                $metadata['session_id'] ?? session_id() ?? null,
                date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log('폼 제출 저장 실패: ' . $e->getMessage());
            throw new Exception('폼 제출 저장 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 폼 제출 목록 조회
     */
    public function getFormSubmissions($formName = null, $limit = 50, $offset = 0) {
        try {
            $sql = "SELECT * FROM form_submissions";
            $params = [];
            
            if ($formName) {
                $sql .= " WHERE form_name = ?";
                $params[] = $formName;
            }
            
            $sql .= " ORDER BY submitted_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $results = $stmt->fetchAll();
            
            // JSON 데이터 디코딩
            foreach ($results as &$result) {
                $result['form_data'] = json_decode($result['form_data'], true);
            }
            
            return $results;
            
        } catch (PDOException $e) {
            error_log('폼 제출 조회 실패: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 특정 폼 제출 조회
     */
    public function getFormSubmission($id) {
        try {
            $sql = "SELECT * FROM form_submissions WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            $result = $stmt->fetch();
            
            if ($result) {
                $result['form_data'] = json_decode($result['form_data'], true);
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log('폼 제출 조회 실패: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 폼별 제출 통계
     */
    public function getFormSubmissionStats($formName = null) {
        try {
            $sql = "
                SELECT 
                    form_name,
                    COUNT(*) as total_submissions,
                    MIN(submitted_at) as first_submission,
                    MAX(submitted_at) as latest_submission
                FROM form_submissions
            ";
            
            $params = [];
            if ($formName) {
                $sql .= " WHERE form_name = ?";
                $params[] = $formName;
            }
            
            $sql .= " GROUP BY form_name ORDER BY total_submissions DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log('폼 통계 조회 실패: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 폼 제출 삭제
     */
    public function deleteFormSubmission($id) {
        try {
            $sql = "DELETE FROM form_submissions WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
            
        } catch (PDOException $e) {
            error_log('폼 제출 삭제 실패: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 데이터베이스 연결 테스트
     */
    public function testConnection() {
        try {
            $sql = "SELECT COUNT(*) as count FROM form_submissions";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            
            return [
                'success' => true,
                'message' => '데이터베이스 연결 성공',
                'total_submissions' => $result['count']
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => '데이터베이스 연결 실패: ' . $e->getMessage()
            ];
        }
    }
}

/**
 * 헬퍼 함수들
 */

/**
 * 폼 제출 데이터 저장
 */
function saveFormSubmission($formName, $formData, $metadata = []) {
    $formDB = new FormSubmissionDB();
    return $formDB->saveFormSubmission($formName, $formData, $metadata);
}

/**
 * 폼 제출 목록 조회
 */
function getFormSubmissions($formName = null, $limit = 50, $offset = 0) {
    $formDB = new FormSubmissionDB();
    return $formDB->getFormSubmissions($formName, $limit, $offset);
}

/**
 * 폼 제출 통계 조회
 */
function getFormSubmissionStats($formName = null) {
    $formDB = new FormSubmissionDB();
    return $formDB->getFormSubmissionStats($formName);
}

/**
 * 데이터베이스 연결 테스트
 */
function testFormDBConnection() {
    $formDB = new FormSubmissionDB();
    return $formDB->testConnection();
}

/**
 * JSON 응답 헬퍼
 */
function jsonResponse($data, $success = true, $message = '') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 에러 응답 헬퍼
 */
function errorResponse($message, $data = null) {
    jsonResponse($data, false, $message);
}

/**
 * 성공 응답 헬퍼
 */
function successResponse($data, $message = '성공') {
    jsonResponse($data, true, $message);
}
?>