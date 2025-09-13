<?php
/**
 * 폼 제출 데이터베이스 초기화 스크립트
 */

// 현재 파일의 절대경로를 기준으로 release.sqlite 경로 설정
$dbPath = __DIR__ . '/release.sqlite';

try {
    // SQLite 데이터베이스 연결
    $pdo = new PDO("sqlite:$dbPath", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // 폼 제출 데이터를 저장할 테이블 생성
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS form_submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            form_name VARCHAR(255) NOT NULL,
            form_data JSON NOT NULL,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            user_agent TEXT,
            session_id VARCHAR(128),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($createTableSQL);
    
    // 인덱스 추가 (성능 향상)
    $indexSQL = [
        "CREATE INDEX IF NOT EXISTS idx_form_submissions_form_name ON form_submissions(form_name)",
        "CREATE INDEX IF NOT EXISTS idx_form_submissions_submitted_at ON form_submissions(submitted_at)",
        "CREATE INDEX IF NOT EXISTS idx_form_submissions_created_at ON form_submissions(created_at)"
    ];
    
    foreach ($indexSQL as $sql) {
        $pdo->exec($sql);
    }
    
    // 폼 설정을 저장할 테이블 생성 (선택적)
    $createFormConfigTableSQL = "
        CREATE TABLE IF NOT EXISTS form_configs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            form_name VARCHAR(255) NOT NULL UNIQUE,
            form_schema JSON NOT NULL,
            is_active BOOLEAN DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($createFormConfigTableSQL);
    
    echo "✅ 데이터베이스 초기화 완료: $dbPath\n";
    echo "📋 테이블 생성: form_submissions, form_configs\n";
    echo "🚀 폼 제출 기능이 준비되었습니다!\n";
    
} catch (PDOException $e) {
    echo "❌ 데이터베이스 초기화 실패: " . $e->getMessage() . "\n";
    exit(1);
}
?>