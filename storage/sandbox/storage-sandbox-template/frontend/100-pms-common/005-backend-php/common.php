<?php
/**
 * 샌드박스 템플릿 공통 설정 파일
 */

/**
 * 샌드박스 설정 반환
 */
function getSandboxConfig() {
    return [
        'database' => [
            'path' => __DIR__ . '/database/release.sqlite'
        ],
        'upload' => [
            'max_file_size' => 50 * 1024 * 1024, // 50MB
            'allowed_extensions' => [
                'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'
            ]
        ]
    ];
}

/**
 * 데이터베이스 초기화
 */
function initializeDatabase() {
    $config = getSandboxConfig();
    $dbPath = $config['database']['path'];
    
    // 데이터베이스가 없으면 생성
    if (!file_exists($dbPath)) {
        $pdo = new PDO("sqlite:" . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 프로젝트 테이블 생성
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS projects (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                status TEXT DEFAULT 'pending',
                progress INTEGER DEFAULT 0,
                team_members INTEGER DEFAULT 1,
                priority TEXT DEFAULT 'medium',
                client TEXT,
                budget INTEGER DEFAULT 0,
                category TEXT DEFAULT 'general',
                start_date DATE,
                end_date DATE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // 샘플 데이터 추가
        $sampleProjects = [
            [19, 'IoT 센서 네트워크', '스마트 빌딩용 IoT 센서 및 제어 시스템', 'planning', 0, 6, 'medium', '스마트빌딩', 14000000, 'IoT', '2024-05-01', '2024-08-31'],
            [13, '앱 테스트 및 배포', '앱스토어 배포 및 품질 보증 테스트', 'planning', 0, 2, 'medium', '디지털솔루션', 2000000, 'Testing', '2024-05-15', '2024-06-15'],
            [15, 'ERP 시스템 구축', '전사 자원 관리 시스템 구축 프로젝트', 'planning', 5, 7, 'high', '엔터프라이즈', 35000000, 'Enterprise System', '2024-04-01', '2024-09-30']
        ];
        
        $stmt = $pdo->prepare("
            INSERT OR REPLACE INTO projects (id, name, description, status, progress, team_members, priority, client, budget, category, start_date, end_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sampleProjects as $project) {
            $stmt->execute($project);
        }
    }
}

// 데이터베이스 초기화 실행
initializeDatabase();