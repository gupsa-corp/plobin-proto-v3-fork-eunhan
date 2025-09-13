-- Phase 2: 간트차트 기능을 위한 컬럼 추가
-- 프로젝트 일정 관리, 의존성 관리, 시간 추적을 위한 컬럼들

-- 간트차트 관련 컬럼 추가
ALTER TABLE projects ADD COLUMN start_date DATE;
ALTER TABLE projects ADD COLUMN end_date DATE;
ALTER TABLE projects ADD COLUMN estimated_hours INTEGER DEFAULT 0 CHECK (estimated_hours >= 0);
ALTER TABLE projects ADD COLUMN actual_hours INTEGER DEFAULT 0 CHECK (actual_hours >= 0);

-- 프로젝트 계층 구조 (부모-자식 관계)
ALTER TABLE projects ADD COLUMN parent_id INTEGER REFERENCES projects(id);

-- 프로젝트 의존성 (JSON 배열로 저장)
-- 예: [1, 3, 5] - 프로젝트 1, 3, 5가 완료된 후 시작 가능
ALTER TABLE projects ADD COLUMN dependencies TEXT DEFAULT '[]';

-- 추가 메타데이터
ALTER TABLE projects ADD COLUMN budget DECIMAL(10,2) DEFAULT 0;
ALTER TABLE projects ADD COLUMN client TEXT;
ALTER TABLE projects ADD COLUMN category TEXT;
ALTER TABLE projects ADD COLUMN tags TEXT DEFAULT '[]'; -- JSON 배열

-- 간트차트 관련 인덱스 생성
CREATE INDEX IF NOT EXISTS idx_projects_start_date ON projects(start_date);
CREATE INDEX IF NOT EXISTS idx_projects_end_date ON projects(end_date);
CREATE INDEX IF NOT EXISTS idx_projects_parent_id ON projects(parent_id);
CREATE INDEX IF NOT EXISTS idx_projects_category ON projects(category);

-- 일정 검증을 위한 트리거
CREATE TRIGGER IF NOT EXISTS validate_project_dates 
    BEFORE UPDATE OF start_date, end_date ON projects
    WHEN NEW.start_date IS NOT NULL AND NEW.end_date IS NOT NULL AND NEW.start_date > NEW.end_date
BEGIN
    SELECT RAISE(ABORT, '시작일은 종료일보다 이전이어야 합니다.');
END;

-- 확인용 쿼리
SELECT 'projects 테이블에 간트차트 컬럼이 성공적으로 추가되었습니다.' as message;