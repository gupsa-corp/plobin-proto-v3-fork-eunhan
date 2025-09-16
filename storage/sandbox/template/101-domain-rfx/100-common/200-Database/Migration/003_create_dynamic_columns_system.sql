-- Phase 3: 동적 컬럼 관리 시스템 생성
-- 런타임에서 새로운 컬럼을 추가/제거할 수 있는 시스템

-- 동적 컬럼 메타데이터 테이블
CREATE TABLE IF NOT EXISTS project_columns (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    column_name TEXT NOT NULL UNIQUE,
    column_type TEXT NOT NULL CHECK (column_type IN ('TEXT', 'INTEGER', 'DECIMAL', 'DATE', 'BOOLEAN', 'JSON')),
    column_label TEXT NOT NULL,
    default_value TEXT,
    is_required BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    sort_order INTEGER DEFAULT 0,
    validation_rule TEXT, -- 예: 'min:1,max:100' 또는 'in:option1,option2,option3'
    display_type TEXT DEFAULT 'input' CHECK (display_type IN ('input', 'textarea', 'select', 'checkbox', 'radio', 'date', 'number')),
    options TEXT DEFAULT '[]', -- JSON 배열: select/radio 옵션들
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 프로젝트별 커스텀 데이터 저장 테이블 (EAV 패턴)
CREATE TABLE IF NOT EXISTS project_custom_data (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    column_name TEXT NOT NULL,
    column_value TEXT, -- 모든 타입을 텍스트로 저장하고 애플리케이션에서 변환
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (column_name) REFERENCES project_columns(column_name) ON DELETE CASCADE,
    UNIQUE(project_id, column_name)
);

-- 인덱스 생성
CREATE INDEX IF NOT EXISTS idx_project_columns_active ON project_columns(is_active);
CREATE INDEX IF NOT EXISTS idx_project_columns_sort_order ON project_columns(sort_order);
CREATE INDEX IF NOT EXISTS idx_project_custom_data_project_id ON project_custom_data(project_id);
CREATE INDEX IF NOT EXISTS idx_project_custom_data_column_name ON project_custom_data(column_name);

-- 업데이트 시간 자동 갱신 트리거
CREATE TRIGGER IF NOT EXISTS update_project_columns_timestamp 
    AFTER UPDATE ON project_columns
BEGIN
    UPDATE project_columns SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

CREATE TRIGGER IF NOT EXISTS update_project_custom_data_timestamp 
    AFTER UPDATE ON project_custom_data
BEGIN
    UPDATE project_custom_data SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;

-- 기본 컬럼 메타데이터 삽입 (기존 컬럼들에 대한 정보)
INSERT OR IGNORE INTO project_columns (column_name, column_type, column_label, is_required, sort_order) VALUES
('name', 'TEXT', '프로젝트명', 1, 1),
('description', 'TEXT', '설명', 0, 2),
('status', 'TEXT', '상태', 1, 3),
('progress', 'INTEGER', '진행률', 0, 4),
('team_members', 'INTEGER', '팀 멤버 수', 0, 5),
('priority', 'TEXT', '우선순위', 0, 6),
('start_date', 'DATE', '시작일', 0, 7),
('end_date', 'DATE', '종료일', 0, 8),
('estimated_hours', 'INTEGER', '예상 시간', 0, 9),
('actual_hours', 'INTEGER', '실제 시간', 0, 10),
('budget', 'DECIMAL', '예산', 0, 11),
('client', 'TEXT', '클라이언트', 0, 12),
('category', 'TEXT', '카테고리', 0, 13),
('created_date', 'DATE', '생성일', 0, 14);

-- 확인용 쿼리
SELECT 'project_columns와 project_custom_data 테이블이 성공적으로 생성되었습니다.' as message;

SELECT 
    column_name,
    column_type,
    column_label,
    is_required,
    sort_order
FROM project_columns 
ORDER BY sort_order;