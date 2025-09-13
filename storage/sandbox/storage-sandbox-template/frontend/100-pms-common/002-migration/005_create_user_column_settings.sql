-- 사용자별 컬럼 설정 테이블 생성
CREATE TABLE IF NOT EXISTS user_column_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL DEFAULT 1, -- 현재는 기본 사용자로 설정
    screen_type VARCHAR(50) NOT NULL DEFAULT 'table_view', -- 화면 구분
    column_name VARCHAR(100) NOT NULL,
    is_visible INTEGER NOT NULL DEFAULT 1, -- 1: 표시, 0: 숨김
    column_order INTEGER DEFAULT 0, -- 컬럼 순서
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, screen_type, column_name)
);

-- 기본 컬럼 설정 데이터 삽입 (필수 컬럼들)
INSERT OR IGNORE INTO user_column_settings (user_id, screen_type, column_name, is_visible, column_order) VALUES
(1, 'table_view', 'name', 1, 1),
(1, 'table_view', 'status', 1, 2), 
(1, 'table_view', 'start_date', 1, 6),
(1, 'table_view', 'progress', 1, 3),
(1, 'table_view', 'team_members', 1, 4),
(1, 'table_view', 'priority', 1, 5),
(1, 'table_view', 'client', 1, 7);

-- 사용자 프로필 테이블 (추후 확장용)
CREATE TABLE IF NOT EXISTS user_profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL DEFAULT 1,
    profile_name VARCHAR(100) DEFAULT 'default',
    settings JSON, -- 기타 사용자 설정 저장용
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, profile_name)
);

-- 기본 사용자 프로필 생성
INSERT OR IGNORE INTO user_profiles (user_id, profile_name, settings) VALUES
(1, 'default', '{"theme": "light", "language": "ko"}');