{{-- 샌드박스 테이블 뷰 템플릿 --}}
<?php
    $commonPath = storage_path('sandbox/storage-sandbox-template/common.php');
    require_once $commonPath;

    // 백엔드 common.php도 로드하여 getSandboxConfig 함수 사용
    $backendCommonPath = storage_path('sandbox/storage-sandbox-template/backend/common.php');
    require_once $backendCommonPath;

    $screenInfo = getCurrentScreenInfo();
    $uploadPaths = getUploadPaths();

    // 전역 변수 초기화 (include된 파일들이 사용할 수 있도록)
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $priority = $_GET['priority'] ?? '';
    $sortBy = $_GET['sort'] ?? 'created_at';
    $sortOrder = $_GET['order'] ?? 'desc';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $dynamicColumns = []; // 초기화

    try {
        // SQLite 데이터베이스 연결 - common.php 설정 사용
        $config = getSandboxConfig();
        $pdo = new PDO("sqlite:" . $config['database']['path']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 동적 컬럼 정보 로드
        $columnsStmt = $pdo->query("
            SELECT
                id, column_name, column_type, column_label, display_type,
                is_required, sort_order, options
            FROM project_columns
            WHERE is_active = 1
            ORDER BY sort_order ASC, column_label ASC
        ");
        $dynamicColumns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);

        // 페이징 설정
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // WHERE 조건 구성
        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(name LIKE :search OR description LIKE :search OR client LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $whereConditions[] = "status = :status";
            $params[':status'] = $status;
        }

        if (!empty($priority)) {
            $whereConditions[] = "priority = :priority";
            $params[':priority'] = $priority;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // 정렬 컬럼 검증
        $allowedSortColumns = ['name', 'status', 'priority', 'created_at', 'progress', 'start_date', 'end_date'];
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'created_at';
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'ASC' : 'DESC';

        // 전체 개수 조회
        $countSql = "SELECT COUNT(*) FROM projects $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalProjects = $countStmt->fetchColumn();

        // 프로젝트 데이터 조회
        $sql = "SELECT
                    id, name, description, status, progress, team_members, priority,
                    start_date, end_date, client, category, budget,
                    created_at
                FROM projects
                $whereClause
                ORDER BY $sortBy $sortOrder
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $projectsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 프로젝트 ID 목록 추출
        $projectIds = array_column($projectsData, 'id');

        // 커스텀 데이터 로드 (있는 경우에만)
        $customDataMap = [];
        if (!empty($projectIds) && !empty($dynamicColumns)) {
            $placeholders = str_repeat('?,', count($projectIds) - 1) . '?';
            $customSql = "SELECT project_id, column_name, column_value
                         FROM project_custom_data
                         WHERE project_id IN ($placeholders)";
            $customStmt = $pdo->prepare($customSql);
            $customStmt->execute($projectIds);
            $customData = $customStmt->fetchAll(PDO::FETCH_ASSOC);

            // 커스텀 데이터를 프로젝트별로 그룹화
            foreach ($customData as $data) {
                $customDataMap[$data['project_id']][$data['column_name']] = $data['column_value'];
            }
        }

        // 프로젝트 데이터에 커스텀 데이터 추가
        foreach ($projectsData as &$project) {
            $project['custom_data'] = $customDataMap[$project['id']] ?? [];
        }

        // 페이지네이션 계산
        $totalPages = ceil($totalProjects / $perPage);

        // 통계 데이터 조회
        $statsStmt = $pdo->query("
            SELECT
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN priority = 'high' THEN 1 END) as high_priority,
                AVG(progress) as avg_progress
            FROM projects
        ");
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $error = "데이터베이스 연결 오류: " . $e->getMessage();
        $projectsData = [];
        $totalProjects = 0;
        $totalPages = 1;
        $stats = ['total' => 0, 'in_progress' => 0, 'completed' => 0, 'high_priority' => 0, 'avg_progress' => 0];
        $dynamicColumns = []; // 오류 시에도 초기화
    }
?>
<div class="min-h-screen bg-gray-50 p-6">
    <?php if(isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <?php include storage_path('sandbox/storage-sandbox-template/frontend/003-screen-table-view/100-header-stats.blade.php'); ?>

    <?php include storage_path('sandbox/storage-sandbox-template/frontend/003-screen-table-view/200-filter-bar.blade.php'); ?>

    <?php include storage_path('sandbox/storage-sandbox-template/frontend/003-screen-table-view/300-data-table.blade.php'); ?>
</div>
