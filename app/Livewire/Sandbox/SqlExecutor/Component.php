<?php

namespace App\Livewire\Sandbox\SqlExecutor;

use Livewire\Component as LivewireComponent;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\SandboxSqlExecution;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Services\SandboxContextService;

class Component extends LivewireComponent
{
    use WithPagination;

    protected $sandboxContextService;
    public $sqlQuery = '';
    public $executionResult = null;
    public $isExecuting = false;
    public $showHistory = false;
    public $tables = [];
    public $selectedTable = null;

    protected $listeners = ['sqlExecuted'];

    public function mount()
    {
        $this->sandboxContextService = app(SandboxContextService::class);
        $this->setupSandboxDatabase();
        $this->loadTables();
    }

    private function setupSandboxDatabase()
    {
        $selectedSandbox = $this->getCurrentSandboxFallback();
        $sandboxDbPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . "/{$selectedSandbox}/backend/database/release.sqlite");

        if (file_exists($sandboxDbPath)) {
            Config::set('database.connections.sandbox_sqlite', [
                'driver' => 'sqlite',
                'database' => $sandboxDbPath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ]);
        }
    }

    private function loadTables()
    {
        try {
            $connection = $this->getSandboxConnection();
            if (!$connection) {
                $this->tables = [];
                return;
            }

            // SQLite에서 테이블 목록 조회
            $tables = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
            $this->tables = array_map(function($table) {
                return (array)$table;
            }, $tables);
        } catch (\Exception $e) {
            $this->addError('tables', '테이블 목록 로드 실패: ' . $e->getMessage());
            $this->tables = [];
        }
    }

    public function selectTable($tableName)
    {
        $this->selectedTable = $tableName;
        $this->sqlQuery = "SELECT * FROM {$tableName} LIMIT 100;";
        $this->dispatch('queryUpdated');
    }

    private function getSandboxConnection()
    {
        try {
            $selectedSandbox = $this->getCurrentSandboxFallback();
            $sandboxDbPath = storage_path(env('SANDBOX_STORAGE_PATH', 'sandbox') . "/{$selectedSandbox}/backend/database/release.sqlite");

            // 파일 존재 확인
            if (!file_exists($sandboxDbPath)) {
                $this->addError('connection', "선택된 샌드박스({$selectedSandbox})의 데이터베이스 파일이 존재하지 않습니다: {$sandboxDbPath}");
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

    public function executeSql()
    {
        // 입력 검증
        if (empty(trim($this->sqlQuery))) {
            $this->addError('query', 'SQL 쿼리를 입력해주세요.');
            return;
        }

        $this->isExecuting = true;
        $this->executionResult = null;
        $startTime = microtime(true);
        $selectedSandbox = $this->getCurrentSandboxFallback();

        try {
            $connection = $this->getSandboxConnection();

            if (!$connection) {
                $this->executionResult = [
                    'status' => 'error',
                    'error' => '데이터베이스 연결을 할 수 없습니다.',
                    'execution_time' => 0,
                    'query_type' => 'unknown'
                ];
                return;
            }

            $queryType = SandboxSqlExecution::getQueryType($this->sqlQuery);
            $sanitizedQuery = trim($this->sqlQuery);

            // SQL 실행
            if (stripos($sanitizedQuery, 'SELECT') === 0 ||
                stripos($sanitizedQuery, 'PRAGMA') === 0 ||
                stripos($sanitizedQuery, 'SHOW') === 0) {
                // 조회 쿼리
                $results = $connection->select($sanitizedQuery);
                $affectedRows = count($results);
                $resultData = [];

                foreach ($results as $row) {
                    $resultData[] = (array) $row;
                }
            } else {
                // 데이터 변경 쿼리
                $affectedRows = $connection->statement($sanitizedQuery);
                $resultData = [
                    'message' => "쿼리가 성공적으로 실행되었습니다.",
                    'affected_rows' => $affectedRows
                ];
            }

            $executionTime = round((microtime(true) - $startTime) * 1000);

            // 실행 결과 저장
            $this->executionResult = [
                'status' => 'success',
                'data' => $resultData,
                'affected_rows' => $affectedRows,
                'execution_time' => $executionTime,
                'query_type' => $queryType
            ];

            // 메인 DB에 실행 기록 저장 (try-catch로 래핑하여 로깅 실패가 주요 기능을 방해하지 않도록)
            try {
                SandboxSqlExecution::logExecution(
                    $selectedSandbox,
                    $this->sqlQuery,
                    $queryType,
                    'success',
                    $resultData,
                    null,
                    $affectedRows,
                    $executionTime
                );
            } catch (\Exception $logError) {
                // 로깅 실패는 무시하고 계속 진행
            }

            session()->flash('success', "SQL 실행 완료! ({$executionTime}ms, {$affectedRows}행 영향)");

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000);
            $queryType = SandboxSqlExecution::getQueryType($this->sqlQuery ?? '');

            // 에러 결과 저장
            $this->executionResult = [
                'status' => 'error',
                'error' => $e->getMessage(),
                'execution_time' => $executionTime,
                'query_type' => $queryType
            ];

            // 메인 DB에 에러 기록 저장
            try {
                SandboxSqlExecution::logExecution(
                    $selectedSandbox,
                    $this->sqlQuery,
                    $queryType,
                    'error',
                    null,
                    $e->getMessage(),
                    null,
                    $executionTime
                );
            } catch (\Exception $logError) {
                // 로깅 실패는 무시하고 계속 진행
            }

            $this->addError('execution', '실행 오류: ' . $e->getMessage());
        } finally {
            $this->isExecuting = false;
        }
    }

    public function clearQuery()
    {
        $this->sqlQuery = '';
        $this->executionResult = null;
        $this->selectedTable = null;
        $this->resetPage();
        $this->dispatch('queryCleared');
    }

    public function toggleHistory()
    {
        $this->showHistory = !$this->showHistory;
        $this->resetPage();
    }

    public function loadExample($example)
    {
        $examples = [
            'select' => 'SELECT * FROM users LIMIT 5;',
            'insert' => "INSERT INTO users (name, email) VALUES ('테스트 사용자', 'test@example.com');",
            'update' => "UPDATE users SET name = '수정된 이름' WHERE id = 1;",
            'create' => "CREATE TABLE test_new_table (id INTEGER PRIMARY KEY, name TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP);",
            'drop' => 'DROP TABLE test_new_table;'
        ];

        $this->sqlQuery = $examples[$example] ?? '';
    }

    public function getExecutionHistory()
    {
        try {
            $selectedSandbox = $this->getCurrentSandboxFallback();

            return SandboxSqlExecution::where('sandbox_folder', $selectedSandbox)
                ->where('user_session_id', session()->getId())
                ->orderBy('created_at', 'desc')
                ->paginate(10);

        } catch (\Exception $e) {
            $this->addError('history', '실행 기록 조회 실패: ' . $e->getMessage());
            return new LengthAwarePaginator([], 0, 10, 1, [
                'path' => request()->url(),
                'pageName' => 'page'
            ]);
        }
    }

    public function render()
    {
        try {
            $selectedSandbox = $this->getCurrentSandboxFallback();
            $executionHistory = $this->getExecutionHistory();

            return view('700-page-sandbox.702-livewire-sql-executor', [
                'selectedSandbox' => $selectedSandbox,
                'executionHistory' => $executionHistory ?? new LengthAwarePaginator([], 0, 10, 1, [
                    'path' => request()->url(),
                    'pageName' => 'page'
                ])
            ]);

        } catch (\Exception $e) {
            $this->addError('render', '페이지 로드 실패: ' . $e->getMessage());

            return view('700-page-sandbox.702-livewire-sql-executor', [
                'selectedSandbox' => $this->getCurrentSandboxFallback(),
                'executionHistory' => new LengthAwarePaginator([], 0, 10, 1, [
                    'path' => request()->url(),
                    'pageName' => 'page'
                ])
            ]);
        }
    }

    /**
     * Helper method to get current sandbox with fallback
     */
    private function getCurrentSandboxFallback()
    {
        return $this->sandboxContextService->getCurrentSandbox();
    }
}
