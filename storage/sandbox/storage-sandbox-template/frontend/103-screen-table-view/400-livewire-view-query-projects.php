<?php

namespace App\Livewire;

use Livewire\Component;
use PDO;
use PDOException;

class SandboxTableView extends Component
{
    public $search = '';
    public $status = '';
    public $priority = '';
    public $sortBy = 'created_at';
    public $sortOrder = 'desc';
    public $page = 1;
    public $perPage = 10;

    public $projectsData = [];
    public $totalProjects = 0;
    public $totalPages = 1;
    public $stats = [];
    public $error = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'priority' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortOrder' => ['except' => 'desc'],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadData();
    }

    public function updatedStatus()
    {
        $this->resetPage();
        $this->loadData();
    }

    public function updatedPriority()
    {
        $this->resetPage();
        $this->loadData();
    }

    public function resetPage()
    {
        $this->page = 1;
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortOrder = 'asc';
        }
        $this->resetPage();
        $this->loadData();
    }

    public function goToPage($pageNumber)
    {
        $this->page = max(1, $pageNumber);
        $this->loadData();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->priority = '';
        $this->resetPage();
        $this->loadData();
    }

    public function deleteProject($projectId)
    {
        try {
            $dbPath = storage_path('sandbox/storage-sandbox-template/backend/database/release.sqlite');
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 삭제할 프로젝트가 존재하는지 확인
            $checkStmt = $pdo->prepare("SELECT id, name FROM projects WHERE id = ?");
            $checkStmt->execute([$projectId]);
            $project = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$project) {
                $this->error = '삭제할 프로젝트를 찾을 수 없습니다.';
                return;
            }

            // 프로젝트 삭제 실행
            $deleteStmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
            $result = $deleteStmt->execute([$projectId]);

            if ($result && $deleteStmt->rowCount() > 0) {
                $this->error = null;
                $this->loadData(); // 데이터 다시 로드
            } else {
                $this->error = '프로젝트 삭제에 실패했습니다.';
            }

        } catch (PDOException $e) {
            $this->error = "데이터베이스 오류: " . $e->getMessage();
        }
    }

    private function loadData()
    {
        try {
            $dbPath = storage_path('sandbox/storage-sandbox-template/backend/database/release.sqlite');
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // WHERE 조건 구성
            $whereConditions = [];
            $params = [];

            if (!empty($this->search)) {
                $whereConditions[] = "(name LIKE :search OR description LIKE :search OR client LIKE :search)";
                $params[':search'] = '%' . $this->search . '%';
            }

            if (!empty($this->status)) {
                $whereConditions[] = "status = :status";
                $params[':status'] = $this->status;
            }

            if (!empty($this->priority)) {
                $whereConditions[] = "priority = :priority";
                $params[':priority'] = $this->priority;
            }

            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

            // 정렬 컬럼 검증
            $allowedSortColumns = ['name', 'status', 'priority', 'created_at', 'progress', 'start_date', 'end_date'];
            $sortBy = in_array($this->sortBy, $allowedSortColumns) ? $this->sortBy : 'created_at';
            $sortOrder = strtolower($this->sortOrder) === 'asc' ? 'ASC' : 'DESC';

            // 전체 개수 조회
            $countSql = "SELECT COUNT(*) FROM projects $whereClause";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $this->totalProjects = $countStmt->fetchColumn();

            // 프로젝트 데이터 조회
            $offset = ($this->page - 1) * $this->perPage;
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
            $stmt->bindValue(':limit', $this->perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $this->projectsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 페이지네이션 계산
            $this->totalPages = ceil($this->totalProjects / $this->perPage);

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
            $this->stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

            $this->error = null;

        } catch (PDOException $e) {
            $this->error = "데이터베이스 연결 오류: " . $e->getMessage();
            $this->projectsData = [];
            $this->totalProjects = 0;
            $this->totalPages = 1;
            $this->stats = ['total' => 0, 'in_progress' => 0, 'completed' => 0, 'high_priority' => 0, 'avg_progress' => 0];
        }
    }

    public function render()
    {
        // 샌드박스 템플릿 경로에서 뷰 렌더링
        $viewPath = storage_path('sandbox/storage-sandbox-template/frontend/003-screen-table-view/700-livewire-table-view.blade.php');
        return view()->file($viewPath);
    }
}
