<?php
/**
 * PMS 마이그레이션 유틸리티
 */

require_once __DIR__ . '/../001-database/pms-database.php';

/**
 * 마이그레이션 관리자
 */
class PMSMigration {
    private $db;
    
    public function __construct() {
        $this->db = PMSDatabase::getInstance();
        $this->createMigrationsTable();
    }
    
    /**
     * 마이그레이션 테이블 생성
     */
    private function createMigrationsTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS pms_migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration VARCHAR(255) NOT NULL,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $this->db->query($sql);
    }
    
    /**
     * 실행된 마이그레이션 목록 조회
     */
    public function getExecutedMigrations() {
        $sql = "SELECT migration FROM pms_migrations ORDER BY id";
        $result = $this->db->fetchAll($sql);
        return array_column($result, 'migration');
    }
    
    /**
     * 마이그레이션 실행
     */
    public function executeMigration($migrationName, $sql) {
        try {
            $this->db->beginTransaction();
            
            // 마이그레이션 SQL 실행
            $this->db->query($sql);
            
            // 마이그레이션 기록
            $this->db->query(
                "INSERT INTO pms_migrations (migration) VALUES (?)",
                [$migrationName]
            );
            
            $this->db->commit();
            echo "마이그레이션 실행 완료: $migrationName\n";
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("마이그레이션 실행 실패 ($migrationName): " . $e->getMessage());
        }
    }
    
    /**
     * 모든 마이그레이션 실행
     */
    public function runMigrations() {
        $migrations = $this->getMigrationFiles();
        $executed = $this->getExecutedMigrations();
        
        foreach ($migrations as $migration) {
            if (!in_array($migration['name'], $executed)) {
                echo "실행 중: {$migration['name']}\n";
                $this->executeMigration($migration['name'], $migration['sql']);
            }
        }
        
        echo "모든 마이그레이션 완료\n";
    }
    
    /**
     * 마이그레이션 파일 목록 조회
     */
    private function getMigrationFiles() {
        $migrations = [];
        $files = glob(__DIR__ . '/migrations/*.sql');
        
        foreach ($files as $file) {
            $name = basename($file, '.sql');
            $sql = file_get_contents($file);
            
            $migrations[] = [
                'name' => $name,
                'sql' => $sql
            ];
        }
        
        // 파일명으로 정렬
        usort($migrations, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return $migrations;
    }
}

/**
 * 기본 PMS 테이블 생성 스크립트
 */
function createPMSTables() {
    $migration = new PMSMigration();
    
    // 프로젝트 테이블
    $projectsTable = "
        CREATE TABLE IF NOT EXISTS pms_projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            status VARCHAR(50) DEFAULT 'planned',
            priority VARCHAR(20) DEFAULT 'medium',
            progress INTEGER DEFAULT 0,
            team_members INTEGER DEFAULT 1,
            start_date DATE,
            end_date DATE,
            estimated_hours INTEGER,
            actual_hours INTEGER,
            budget DECIMAL(12,2),
            client VARCHAR(255),
            category VARCHAR(100),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    // 파일 테이블
    $filesTable = "
        CREATE TABLE IF NOT EXISTS pms_files (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id INTEGER,
            original_name VARCHAR(255) NOT NULL,
            stored_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size INTEGER,
            mime_type VARCHAR(100),
            uploaded_by VARCHAR(255),
            uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES pms_projects(id) ON DELETE CASCADE
        )
    ";
    
    // 태스크 테이블 (칸반/간트용)
    $tasksTable = "
        CREATE TABLE IF NOT EXISTS pms_tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id INTEGER,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            status VARCHAR(50) DEFAULT 'todo',
            priority VARCHAR(20) DEFAULT 'medium',
            assigned_to VARCHAR(255),
            due_date DATE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            sort_order INTEGER DEFAULT 0,
            FOREIGN KEY (project_id) REFERENCES pms_projects(id) ON DELETE CASCADE
        )
    ";
    
    try {
        echo "PMS 테이블 생성 중...\n";
        
        if (!in_array('001_create_projects_table', $migration->getExecutedMigrations())) {
            $migration->executeMigration('001_create_projects_table', $projectsTable);
        }
        
        if (!in_array('002_create_files_table', $migration->getExecutedMigrations())) {
            $migration->executeMigration('002_create_files_table', $filesTable);
        }
        
        if (!in_array('003_create_tasks_table', $migration->getExecutedMigrations())) {
            $migration->executeMigration('003_create_tasks_table', $tasksTable);
        }
        
        echo "PMS 테이블 생성 완료\n";
        
    } catch (Exception $e) {
        echo "테이블 생성 실패: " . $e->getMessage() . "\n";
    }
}

/**
 * 샘플 데이터 생성
 */
function createSampleData() {
    $db = PMSDatabase::getInstance();
    
    // 기존 데이터 확인
    $existingProjects = $db->fetchValue("SELECT COUNT(*) FROM pms_projects");
    if ($existingProjects > 0) {
        echo "샘플 데이터가 이미 존재합니다.\n";
        return;
    }
    
    echo "샘플 데이터 생성 중...\n";
    
    try {
        $db->beginTransaction();
        
        // 샘플 프로젝트들
        $projects = [
            ['웹사이트 리뉴얼 프로젝트', '기업 공식 웹사이트 전면 리뉴얼 및 모바일 최적화', 'in_progress', 'high', 75],
            ['모바일 앱 개발', '고객 서비스용 모바일 애플리케이션 개발', 'in_progress', 'high', 45],
            ['데이터베이스 마이그레이션', '레거시 시스템에서 신규 클라우드 DB로 이전', 'planned', 'medium', 0],
            ['AI 챗봇 구축', '고객 지원용 AI 챗봇 시스템 구축', 'on_hold', 'medium', 20],
            ['보안 시스템 강화', '전사 보안 시스템 점검 및 강화', 'completed', 'high', 100]
        ];
        
        foreach ($projects as $i => $project) {
            $db->insert(
                "INSERT INTO pms_projects (name, description, status, priority, progress) VALUES (?, ?, ?, ?, ?)",
                $project
            );
        }
        
        $db->commit();
        echo "샘플 데이터 생성 완료\n";
        
    } catch (Exception $e) {
        $db->rollback();
        echo "샘플 데이터 생성 실패: " . $e->getMessage() . "\n";
    }
}

// CLI에서 직접 실행된 경우
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    try {
        createPMSTables();
        createSampleData();
    } catch (Exception $e) {
        echo "오류 발생: " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>