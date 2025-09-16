<?php
/**
 * PMS 데이터베이스 공통 설정 및 유틸리티
 */

/**
 * 데이터베이스 연결 설정
 */
class PMSDatabase {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            // SQLite 데이터베이스 경로
            $dbPath = __DIR__ . '/../../../backend/database/release.sqlite';
            
            $this->pdo = new PDO("sqlite:$dbPath", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            // SQLite 설정
            $this->pdo->exec('PRAGMA foreign_keys = ON');
            $this->pdo->exec('PRAGMA journal_mode = WAL');
            
        } catch (PDOException $e) {
            throw new Exception('데이터베이스 연결 실패: ' . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * 쿼리 실행
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception('쿼리 실행 실패: ' . $e->getMessage());
        }
    }
    
    /**
     * 단일 행 조회
     */
    public function fetchRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * 모든 행 조회
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * 단일 값 조회
     */
    public function fetchValue($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * INSERT 실행 후 ID 반환
     */
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * 트랜잭션 시작
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * 트랜잭션 커밋
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * 트랜잭션 롤백
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
}

/**
 * 공통 데이터베이스 쿼리 빌더
 */
class PMSQueryBuilder {
    private $db;
    private $table;
    private $select = ['*'];
    private $where = [];
    private $joins = [];
    private $orderBy = [];
    private $limit = null;
    private $offset = null;
    private $params = [];
    
    public function __construct($table) {
        $this->db = PMSDatabase::getInstance();
        $this->table = $table;
    }
    
    /**
     * SELECT 절
     */
    public function select($columns) {
        if (is_string($columns)) {
            $columns = explode(',', $columns);
        }
        $this->select = array_map('trim', $columns);
        return $this;
    }
    
    /**
     * WHERE 절
     */
    public function where($column, $operator, $value) {
        $placeholder = ':param_' . count($this->params);
        $this->where[] = "$column $operator $placeholder";
        $this->params[$placeholder] = $value;
        return $this;
    }
    
    /**
     * WHERE LIKE 절
     */
    public function whereLike($column, $value) {
        return $this->where($column, 'LIKE', "%$value%");
    }
    
    /**
     * WHERE IN 절
     */
    public function whereIn($column, $values) {
        if (empty($values)) return $this;
        
        $placeholders = [];
        foreach ($values as $value) {
            $placeholder = ':param_' . count($this->params);
            $placeholders[] = $placeholder;
            $this->params[$placeholder] = $value;
        }
        
        $this->where[] = "$column IN (" . implode(',', $placeholders) . ")";
        return $this;
    }
    
    /**
     * JOIN 절
     */
    public function join($table, $condition, $type = 'INNER') {
        $this->joins[] = "$type JOIN $table ON $condition";
        return $this;
    }
    
    /**
     * LEFT JOIN 절
     */
    public function leftJoin($table, $condition) {
        return $this->join($table, $condition, 'LEFT');
    }
    
    /**
     * ORDER BY 절
     */
    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy[] = "$column $direction";
        return $this;
    }
    
    /**
     * LIMIT 절
     */
    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }
    
    /**
     * OFFSET 절
     */
    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }
    
    /**
     * 쿼리 빌드
     */
    public function buildSelect() {
        $sql = 'SELECT ' . implode(', ', $this->select);
        $sql .= " FROM {$this->table}";
        
        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        
        if (!empty($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }
        
        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }
        
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }
        
        return $sql;
    }
    
    /**
     * 결과 조회
     */
    public function get() {
        $sql = $this->buildSelect();
        return $this->db->fetchAll($sql, $this->params);
    }
    
    /**
     * 단일 결과 조회
     */
    public function first() {
        $this->limit(1);
        $sql = $this->buildSelect();
        return $this->db->fetchRow($sql, $this->params);
    }
    
    /**
     * 개수 조회
     */
    public function count() {
        $this->select = ['COUNT(*) as count'];
        $sql = $this->buildSelect();
        $result = $this->db->fetchRow($sql, $this->params);
        return $result['count'] ?? 0;
    }
}

/**
 * 팩토리 함수
 */
function pmsDB($table = null) {
    if ($table) {
        return new PMSQueryBuilder($table);
    }
    return PMSDatabase::getInstance();
}
?>