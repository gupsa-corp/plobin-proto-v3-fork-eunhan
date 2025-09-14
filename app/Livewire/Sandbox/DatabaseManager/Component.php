<?php

namespace App\Livewire\Sandbox\DatabaseManager;

use Livewire\Component as LivewireComponent;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\SandboxContextService;

class Component extends LivewireComponent
{
    use WithPagination;

    protected $sandboxContextService;
    public $selectedTable = null;
    public $tableData = [];
    public $columns = [];
    public $perPage = 25;
    public $search = '';
    public $sortField = '';
    public $sortDirection = 'asc';

    protected $listeners = ['tableSelected'];

    public function mount()
    {
        $this->sandboxContextService = app(SandboxContextService::class);
        $this->setupSandboxDatabase();
    }

    private function setupSandboxDatabase()
    {
        $currentSandbox = $this->sandboxContextService->getCurrentSandbox();
        $sandboxDbPath = $this->sandboxContextService->getSandboxStoragePath() . '/100-domain-pms/100-common/200-Database/release.sqlite';

        if (file_exists($sandboxDbPath)) {
            Config::set('database.connections.sandbox_sqlite', [
                'driver' => 'sqlite',
                'database' => $sandboxDbPath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);
        }
    }

    private function getSandboxConnection()
    {
        try {
            $currentSandbox = $this->sandboxContextService->getCurrentSandbox();
        $sandboxDbPath = $this->sandboxContextService->getSandboxStoragePath() . '/100-domain-pms/100-common/200-Database/release.sqlite';

            // 파일 존재 확인
            if (!file_exists($sandboxDbPath)) {
                $this->addError('connection', "샌드박스 템플릿 데이터베이스 파일이 존재하지 않습니다.");
                return null;
            }

            // 파일 읽기 권한 확인
            if (!is_readable($sandboxDbPath)) {
                $this->addError('connection', "데이터베이스 파일에 읽기 권한이 없습니다.");
                return null;
            }

            // 연결 설정
            Config::set('database.connections.sandbox_sqlite', [
                'driver' => 'sqlite',
                'database' => $sandboxDbPath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);

            // 기존 연결 정리 후 새로 연결
            DB::purge('sandbox_sqlite');
            $connection = DB::connection('sandbox_sqlite');

            // 연결 테스트
            $connection->getPdo();

            return $connection;

        } catch (\Exception $e) {
            $this->addError('connection', '데이터베이스 연결 실패: ' . $e->getMessage());
            return null;
        }
    }

    private function createEmptyPaginator()
    {
        return new LengthAwarePaginator(
            [], // items
            0,  // total
            $this->perPage, // perPage
            1, // currentPage
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    public function render()
    {
        try {
            $tables = $this->getDatabaseTables();
            $paginatedData = $this->getPaginatedData();
            $selectedSandbox = Session::get('sandbox_storage', '1');

            // 안전한 기본값 보장
            return view('700-page-sandbox.705-livewire-database-manager', [
                'tables' => $tables ?? [],
                'paginatedData' => $paginatedData ?? $this->createEmptyPaginator(),
                'selectedSandbox' => $selectedSandbox ?? '1'
            ]);

        } catch (\Exception $e) {
            $this->addError('render', '페이지 로드 실패: ' . $e->getMessage());

            return view('700-page-sandbox.705-livewire-database-manager', [
                'tables' => [],
                'paginatedData' => $this->createEmptyPaginator(),
                'selectedSandbox' => Session::get('sandbox_storage', '1')
            ]);
        }
    }

    public function getDatabaseTables()
    {
        try {
            $connection = $this->getSandboxConnection();

            if (!$connection) {
                return [];
            }

            $driver = $connection->getDriverName();
            $tableNames = [];

            switch ($driver) {
                case 'sqlite':
                    $tables = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
                    foreach ($tables as $table) {
                        if (isset($table->name)) {
                            $tableNames[] = $table->name;
                        }
                    }
                    break;

                case 'mysql':
                    $tables = Schema::getAllTables();
                    foreach ($tables as $table) {
                        $tableName = $table->Tables_in_plobin_proto_v3 ??
                                    $table->{'Tables_in_' . config('database.connections.mysql.database')} ??
                                    array_values((array) $table)[0];
                        if ($tableName) {
                            $tableNames[] = $tableName;
                        }
                    }
                    break;

                case 'pgsql':
                    $tables = $connection->select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
                    foreach ($tables as $table) {
                        if (isset($table->tablename)) {
                            $tableNames[] = $table->tablename;
                        }
                    }
                    break;

                default:
                    $tables = $connection->select("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY table_name");
                    foreach ($tables as $table) {
                        if (isset($table->table_name)) {
                            $tableNames[] = $table->table_name;
                        }
                    }
                    break;
            }

            return array_values(array_unique($tableNames));

        } catch (\Exception $e) {
            $this->addError('tables', '테이블 목록 조회 실패: ' . $e->getMessage());
            return [];
        }
    }

    public function selectTable($tableName)
    {
        $this->selectedTable = $tableName;
        $this->loadTableData();
        $this->resetPage();
        $this->search = '';
        $this->sortField = '';
        $this->sortDirection = 'asc';
    }

    public function loadTableData()
    {
        if (!$this->selectedTable) {
            $this->columns = [];
            return;
        }

        try {
            $connection = $this->getSandboxConnection();

            if (!$connection) {
                $this->columns = [];
                return;
            }

            // 테이블 존재 확인
            $tableExists = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name = ?", [$this->selectedTable]);
            if (empty($tableExists)) {
                $this->columns = [];
                $this->addError('table', '선택한 테이블이 존재하지 않습니다.');
                return;
            }

            // 테이블 컬럼 정보 가져오기
            $this->columns = Schema::connection('sandbox_sqlite')->getColumnListing($this->selectedTable);

            // 컬럼이 없는 경우 처리
            if (empty($this->columns)) {
                $this->addError('columns', '테이블 컬럼 정보를 가져올 수 없습니다.');
            }

        } catch (\Exception $e) {
            $this->columns = [];
            $this->addError('table_data', '테이블 데이터 로드 실패: ' . $e->getMessage());
        }
    }

    public function getPaginatedData()
    {
        // 기본값 초기화
        if (!$this->selectedTable || empty($this->columns)) {
            return $this->createEmptyPaginator();
        }

        try {
            $connection = $this->getSandboxConnection();

            if (!$connection) {
                return $this->createEmptyPaginator();
            }

            // 테이블 존재 재확인
            $tableExists = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name = ?", [$this->selectedTable]);
            if (empty($tableExists)) {
                return $this->createEmptyPaginator();
            }

            $query = $connection->table($this->selectedTable);

            // 검색 기능
            if (!empty($this->search) && is_array($this->columns)) {
                $searchTerm = trim($this->search);
                if (strlen($searchTerm) > 0) {
                    $query->where(function($q) use ($searchTerm) {
                        foreach ($this->columns as $column) {
                            if (!empty($column)) {
                                $q->orWhere($column, 'LIKE', '%' . $searchTerm . '%');
                            }
                        }
                    });
                }
            }

            // 정렬 기능
            if (!empty($this->sortField) &&
                !empty($this->columns) &&
                in_array($this->sortField, $this->columns) &&
                in_array(strtolower($this->sortDirection), ['asc', 'desc'])) {
                $query->orderBy($this->sortField, $this->sortDirection);
            }

            // 페이지네이션
            return $query->paginate(max(1, min(100, $this->perPage)));

        } catch (\Exception $e) {
            $this->addError('pagination', '데이터 조회 실패: ' . $e->getMessage());
            return $this->createEmptyPaginator();
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }
}
