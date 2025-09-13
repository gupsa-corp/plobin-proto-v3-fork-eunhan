-- 샌드박스 커스텀 화면 메타데이터 추가
INSERT INTO sandbox_custom_screens (title, description, type, folder_name, file_path, sandbox_folder, created_at, updated_at) VALUES
(
    '프로젝트 테이블 뷰',
    '프로젝트 데이터를 테이블 형식으로 관리하고 필터링, 정렬, 검색 기능을 제공합니다.',
    'table',
    '002-screen-table-view',
    'custom-screens/002-screen-table-view/000-content.blade.php',
    'template',
    datetime('now'),
    datetime('now')
),
(
    '프로젝트 칸반 보드',
    '드래그 앤 드롭으로 작업을 관리할 수 있는 칸반 보드 인터페이스입니다.',
    'kanban',
    '003-screen-kanban-board',
    'custom-screens/003-screen-kanban-board/000-content.blade.php',
    'template',
    datetime('now'),
    datetime('now')
),
(
    '프로젝트 간트 차트',
    '프로젝트 일정을 시각적으로 관리할 수 있는 간트 차트 뷰입니다.',
    'gantt',
    '004-screen-gantt-chart',
    'custom-screens/004-screen-gantt-chart/000-content.blade.php',
    'template',
    datetime('now'),
    datetime('now')
),
(
    '프로젝트 캘린더',
    '일정과 마일스톤을 월간/주간/일간 캘린더로 관리할 수 있습니다.',
    'calendar',
    '005-screen-calendar-view',
    'custom-screens/005-screen-calendar-view/000-content.blade.php',
    'template',
    datetime('now'),
    datetime('now')
),
(
    'AI 문서 분석',
    '팔란티어 온톨로지 기반으로 문서를 에셋별로 분류하고 AI 요약/도움내용을 제공하는 화면입니다.',
    'analysis',
    '013-screen-document-analysis',
    'custom-screens/013-screen-document-analysis/000-content.blade.php',
    'template',
    datetime('now'),
    datetime('now')
);

-- 기존 샘플 화면 업데이트 (있는 경우)
UPDATE sandbox_custom_screens
SET description = '기본적인 대시보드 레이아웃과 통계 카드, 차트를 제공하는 샘플 화면입니다.'
WHERE folder_name = '000-screen-dashboard';

UPDATE sandbox_custom_screens
SET description = '프로젝트 목록을 테이블 형태로 표시하고 관리할 수 있는 화면입니다.'
WHERE folder_name = '001-screen-project-list';

-- 확인용 쿼리
SELECT
    id,
    title,
    type,
    folder_name,
    sandbox_folder,
    created_at
FROM sandbox_custom_screens
ORDER BY folder_name;
