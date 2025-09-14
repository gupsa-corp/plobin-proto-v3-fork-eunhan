<?php

namespace App\Http\Controllers\Sandbox\CustomScreen;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Response;
use App\Services\StorageCommonService;
use App\Services\SandboxContextService;

class RawController extends \App\Http\Controllers\Controller
{
    protected $sandboxContextService;

    public function __construct(SandboxContextService $sandboxContextService)
    {
        $this->sandboxContextService = $sandboxContextService;
    }

    public function show($id)
    {
        // 템플릿 경로에서 해당 스크린 파일 찾기
        $templatePath = $this->sandboxContextService->getSandboxStoragePath() . '/frontend';
        $screenPath = null;

        if (File::exists($templatePath)) {
            $folders = File::directories($templatePath);

            foreach ($folders as $folder) {
                $folderName = basename($folder);
                $contentFile = $folder . '/000-content.blade.php';

                if (File::exists($contentFile)) {
                    // 폴더명에서 화면 ID 추출
                    $parts = explode('-', $folderName, 3);
                    $screenId = $parts[0] ?? '000';

                    if ($screenId === $id) {
                        $screenPath = $contentFile;
                        break;
                    }
                }
            }
        }

        if (!$screenPath || !File::exists($screenPath)) {
            return response('템플릿 파일을 찾을 수 없습니다.', 404);
        }

        // 템플릿 파일 내용 읽기
        $templateContent = File::get($screenPath);

        // 샘플 데이터 설정
        $sampleData = [
            'title' => '샘플 화면',
            'users' => collect([
                ['id' => 1, 'name' => '홍길동', 'email' => 'hong@example.com', 'status' => 'active'],
                ['id' => 2, 'name' => '김철수', 'email' => 'kim@example.com', 'status' => 'inactive'],
                ['id' => 3, 'name' => '이영희', 'email' => 'lee@example.com', 'status' => 'active']
            ]),
            'projects' => collect([
                ['id' => 1, 'name' => '프로젝트 A', 'status' => 'active', 'progress' => 75],
                ['id' => 2, 'name' => '프로젝트 B', 'status' => 'pending', 'progress' => 30],
                ['id' => 3, 'name' => '프로젝트 C', 'status' => 'completed', 'progress' => 100]
            ]),
            'organizations' => collect([
                ['id' => 1, 'name' => '샘플 조직 1', 'members' => 15],
                ['id' => 2, 'name' => '샘플 조직 2', 'members' => 8],
            ])
        ];

        try {
            // 글로벌 네비게이션 템플릿 읽기 - 새로운 위치에서
            $globalNavPath = resource_path('views/700-page-sandbox/700-common/100-sandbox-navigation.blade.php');
            $globalNavContent = '';
            if (File::exists($globalNavPath)) {
                $globalNavContent = File::get($globalNavPath);
            }

            // 블레이드 템플릿 직접 렌더링 (임시 파일 없이)
            try {
                // 메인 컨텐츠 렌더링
                $renderedContent = \Illuminate\Support\Facades\Blade::render($templateContent, $sampleData);

                // 글로벌 네비게이션 렌더링
                $globalNavRendered = $globalNavContent ?
                    \Illuminate\Support\Facades\Blade::render($globalNavContent, $sampleData) : '';

                // HTML 문서로 감싸서 반환 (CSRF 토큰 포함)
                $csrfToken = csrf_token();
                $html = '<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="' . $csrfToken . '">
    <title>템플릿 미리보기 - ' . basename($screenName) . '</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: "Malgun Gothic", "맑은 고딕", sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-7xl mx-auto">
        ' . $globalNavRendered . '
        ' . $renderedContent . '
    </div>
</body>
</html>';

                return response($html)->header('Content-Type', 'text/html; charset=utf-8');

            } catch (\Exception $e) {
                return response('렌더링 오류: ' . $e->getMessage(), 500);
            }

        } catch (\Exception $e) {
            return response('템플릿 처리 오류: ' . $e->getMessage(), 500);
        }
    }

    public function showByPath($storageName, $screenFolderName)
    {
        // 스토리지 경로에서 해당 화면 폴더 찾기
        $templatePath = storage_path("sandbox/{$storageName}/frontend");
        $screenPath = $templatePath . '/' . $screenFolderName . '/000-content.blade.php';

        if (!File::exists($screenPath)) {
            return response('템플릿 파일을 찾을 수 없습니다.', 404);
        }

        // 템플릿 파일 내용 읽기
        $templateContent = File::get($screenPath);

        // 기본 샘플 데이터 설정
        $sampleData = [
            'title' => '샘플 화면',
            'users' => collect([
                ['id' => 1, 'name' => '홍길동', 'email' => 'hong@example.com', 'status' => 'active'],
                ['id' => 2, 'name' => '김철수', 'email' => 'kim@example.com', 'status' => 'inactive'],
                ['id' => 3, 'name' => '이영희', 'email' => 'lee@example.com', 'status' => 'active']
            ]),
            'projects' => collect([
                ['id' => 1, 'name' => '프로젝트 A', 'status' => 'active', 'progress' => 75],
                ['id' => 2, 'name' => '프로젝트 B', 'status' => 'pending', 'progress' => 30],
                ['id' => 3, 'name' => '프로젝트 C', 'status' => 'completed', 'progress' => 100]
            ]),
            'organizations' => collect([
                ['id' => 1, 'name' => '샘플 조직 1', 'members' => 15],
                ['id' => 2, 'name' => '샘플 조직 2', 'members' => 8],
            ])
        ];

        // 테이블 뷰의 경우 실제 데이터베이스 데이터 사용
        if (strpos($screenFolderName, 'table-view') !== false) {
            try {
                // StorageCommonService 사용
                // 실제 데이터베이스에서 데이터 조회
                $pdo = StorageCommonService::getDatabaseConnection();

                // 페이징 설정
                $page = max(1, (int)($_GET['page'] ?? 1));
                $perPage = 10;
                $offset = ($page - 1) * $perPage;

                // 검색 및 필터 조건
                $search = $_GET['search'] ?? '';
                $status = $_GET['status'] ?? '';
                $priority = $_GET['priority'] ?? '';
                $sortBy = $_GET['sort'] ?? 'created_at';
                $sortOrder = $_GET['order'] ?? 'desc';

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

                // 페이지네이션 계산
                $totalPages = ceil($totalProjects / $perPage);

                // 통계 데이터 조회
                $statsStmt = $pdo->query("
                    SELECT
                        COUNT(*) as total,
                        COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
                        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                        COUNT(CASE WHEN priority = 'high' THEN 1 END) as high_priority,
                        AVG(COALESCE(progress, 0)) as avg_progress
                    FROM projects
                ");
                $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

                // 동적 컬럼 정보 조회
                $columnsStmt = $pdo->query("
                    SELECT
                        id, column_name, column_type, column_label, display_type,
                        is_required, sort_order, options
                    FROM project_columns
                    WHERE is_active = 1
                    ORDER BY sort_order ASC, column_label ASC
                ");
                $dynamicColumns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);

                // 테이블 뷰 관련 변수들 추가
                $sampleData = array_merge($sampleData, [
                    'search' => $search,
                    'status' => $status,
                    'priority' => $priority,
                    'sortBy' => $sortBy,
                    'sortOrder' => $sortOrder,
                    'page' => $page,
                    'perPage' => $perPage,
                    'totalProjects' => $totalProjects,
                    'totalPages' => $totalPages,
                    'projectsData' => $projectsData,
                    'dynamicColumns' => $dynamicColumns,
                    'stats' => $stats
                ]);
            } catch (\Exception $e) {
                // 데이터베이스 오류 시 기본 샘플 데이터 사용
                $sampleData = array_merge($sampleData, [
                    'search' => $_GET['search'] ?? '',
                    'status' => $_GET['status'] ?? '',
                    'priority' => $_GET['priority'] ?? '',
                    'sortBy' => $_GET['sort'] ?? 'created_at',
                    'sortOrder' => $_GET['order'] ?? 'desc',
                    'page' => max(1, (int)($_GET['page'] ?? 1)),
                    'perPage' => 10,
                    'totalProjects' => 0,
                    'totalPages' => 1,
                    'projectsData' => [],
                    'dynamicColumns' => [],
                    'stats' => ['total' => 0, 'in_progress' => 0, 'completed' => 0, 'high_priority' => 0, 'avg_progress' => 0]
                ]);
            }
        }

        try {
            // 글로벌 네비게이션 템플릿 읽기 - 새로운 위치에서
            $globalNavPath = resource_path('views/700-page-sandbox/700-common/100-sandbox-navigation.blade.php');
            $globalNavContent = '';
            if (File::exists($globalNavPath)) {
                $globalNavContent = File::get($globalNavPath);
            }

            // 블레이드 템플릿 직접 렌더링 (임시 파일 없이)
            try {
                // 메인 컨텐츠 렌더링
                $renderedContent = \Illuminate\Support\Facades\Blade::render($templateContent, $sampleData);

                // 글로벌 네비게이션 렌더링
                $globalNavRendered = $globalNavContent ?
                    \Illuminate\Support\Facades\Blade::render($globalNavContent, $sampleData) : '';

                // HTML 문서로 감싸서 반환 (CSRF 토큰 포함)
                $csrfToken = csrf_token();
                $html = '<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="' . $csrfToken . '">
    <title>템플릿 미리보기 - ' . $screenFolderName . '</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: "Malgun Gothic", "맑은 고딕", sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-7xl mx-auto">
        ' . $globalNavRendered . '
        ' . $renderedContent . '
    </div>
</body>
</html>';

                return response($html)->header('Content-Type', 'text/html; charset=utf-8');

            } catch (\Exception $e) {
                return response('렌더링 오류: ' . $e->getMessage(), 500);
            }

        } catch (\Exception $e) {
            return response('템플릿 처리 오류: ' . $e->getMessage(), 500);
        }
    }
}
