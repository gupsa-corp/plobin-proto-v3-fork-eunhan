<?php

namespace App\Livewire\Sandbox\CustomScreens\Live;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class KanbanBoardComponent extends Component
{
    public $columns = [];
    public $projects = [];
    public $stats = [];
    public $showAddModal = false;
    public $selectedColumn = '';
    public $newProject = [
        'name' => '',
        'description' => '',
        'status' => '',
        'organization_name' => ''
    ];

    protected $listeners = ['projectMoved' => 'handleProjectMove'];

    protected $rules = [
        'newProject.name' => 'required|string|max:255',
        'newProject.description' => 'nullable|string|max:1000',
        'newProject.status' => 'required|string',
        'newProject.organization_name' => 'nullable|string|max:255'
    ];

    protected $messages = [
        'newProject.name.required' => '프로젝트 이름은 필수입니다.',
        'newProject.name.max' => '프로젝트 이름은 최대 255자까지 입력 가능합니다.',
        'newProject.description.max' => '설명은 최대 1000자까지 입력 가능합니다.',
        'newProject.status.required' => '상태를 선택해주세요.',
        'newProject.organization_name.max' => '조직명은 최대 255자까지 입력 가능합니다.'
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.sandbox.custom-screens.live.kanban-board-component');
    }

    public function loadData()
    {
        try {
            $sandboxConnection = 'sandbox';
            
            // 칼럼 정의
            $this->columns = [
                ['id' => 'backlog', 'name' => '백로그', 'color' => 'gray'],
                ['id' => 'todo', 'name' => '할 일', 'color' => 'blue'],
                ['id' => 'in_progress', 'name' => '진행 중', 'color' => 'yellow'],
                ['id' => 'review', 'name' => '검토', 'color' => 'purple'],
                ['id' => 'done', 'name' => '완료', 'color' => 'green']
            ];

            // 프로젝트 데이터 로드
            $allProjects = DB::connection($sandboxConnection)
                ->table('projects')
                ->leftJoin('users', 'projects.created_by', '=', 'users.id')
                ->leftJoin('organizations', 'projects.organization_id', '=', 'organizations.id')
                ->select(
                    'projects.*',
                    'users.name as created_by_name',
                    'organizations.name as organization_name'
                )
                ->get();

            // 상태별로 프로젝트 그룹화
            $this->projects = [];
            foreach ($this->columns as $column) {
                $this->projects[$column['id']] = $allProjects->filter(function ($project) use ($column) {
                    return $this->mapStatusToColumn($project->status ?? 'backlog') === $column['id'];
                })->values()->toArray();
            }

            // 통계 데이터
            $this->stats = [
                'total_projects' => $allProjects->count(),
                'in_progress' => $allProjects->where('status', 'in_progress')->count(),
                'completed' => $allProjects->where('status', 'completed')->count(),
                'blocked' => $allProjects->where('status', 'blocked')->count()
            ];
            
        } catch (\Exception $e) {
            // 기본값 설정
            $this->columns = [
                ['id' => 'backlog', 'name' => '백로그', 'color' => 'gray'],
                ['id' => 'todo', 'name' => '할 일', 'color' => 'blue'],
                ['id' => 'in_progress', 'name' => '진행 중', 'color' => 'yellow'],
                ['id' => 'review', 'name' => '검토', 'color' => 'purple'],
                ['id' => 'done', 'name' => '완료', 'color' => 'green']
            ];

            $sampleProjects = [
                (object)[
                    'id' => 1,
                    'name' => '웹사이트 리뉴얼',
                    'description' => '기존 웹사이트의 전면적인 개편 작업',
                    'status' => 'in_progress',
                    'created_by_name' => '홍길동',
                    'organization_name' => '테크 스타트업',
                    'created_at' => now()->subDays(5)
                ],
                (object)[
                    'id' => 2,
                    'name' => '모바일 앱 개발',
                    'description' => 'iOS/Android 네이티브 앱 개발',
                    'status' => 'todo',
                    'created_by_name' => '김철수',
                    'organization_name' => '디지털 에이전시',
                    'created_at' => now()->subDays(3)
                ],
                (object)[
                    'id' => 3,
                    'name' => 'API 플랫폼 구축',
                    'description' => 'RESTful API 서버 구축',
                    'status' => 'done',
                    'created_by_name' => '이영희',
                    'organization_name' => '클라우드 솔루션',
                    'created_at' => now()->subDays(15)
                ],
                (object)[
                    'id' => 4,
                    'name' => '데이터베이스 최적화',
                    'description' => '쿼리 성능 개선 및 인덱스 최적화',
                    'status' => 'review',
                    'created_by_name' => '박민수',
                    'organization_name' => '테크 스타트업',
                    'created_at' => now()->subDays(7)
                ]
            ];

            $this->projects = [];
            foreach ($this->columns as $column) {
                $this->projects[$column['id']] = array_filter($sampleProjects, function ($project) use ($column) {
                    return $this->mapStatusToColumn($project->status) === $column['id'];
                });
            }

            $this->stats = [
                'total_projects' => 8,
                'in_progress' => 3,
                'completed' => 2,
                'blocked' => 1
            ];
        }
    }

    private function mapStatusToColumn($status)
    {
        $mapping = [
            'backlog' => 'backlog',
            'todo' => 'todo',
            'active' => 'todo',
            'in_progress' => 'in_progress',
            'review' => 'review',
            'testing' => 'review',
            'completed' => 'done',
            'done' => 'done'
        ];

        return $mapping[$status] ?? 'backlog';
    }

    public function handleProjectMove($projectId, $fromColumn, $toColumn)
    {
        // 실제 구현에서는 데이터베이스 업데이트 로직 추가
        $this->dispatch('project-moved', [
            'projectId' => $projectId,
            'fromColumn' => $fromColumn,
            'toColumn' => $toColumn
        ]);
        
        $this->loadData();
    }

    public function refreshData()
    {
        $this->loadData();
        $this->dispatch('data-refreshed');
    }

    public function openAddProjectModal($columnId)
    {
        $this->selectedColumn = $columnId;
        $this->newProject['status'] = $columnId;
        $this->showAddModal = true;
        $this->resetErrorBag();
    }

    public function closeAddProjectModal()
    {
        $this->showAddModal = false;
        $this->selectedColumn = '';
        $this->newProject = [
            'name' => '',
            'description' => '',
            'status' => '',
            'organization_name' => ''
        ];
        $this->resetErrorBag();
    }

    public function addProject()
    {
        $this->validate();

        try {
            $sandboxConnection = 'sandbox';
            
            // 데이터베이스에 프로젝트 추가
            $projectData = [
                'name' => $this->newProject['name'],
                'description' => $this->newProject['description'],
                'status' => $this->mapColumnToStatus($this->newProject['status']),
                'created_by' => 1, // 임시 사용자 ID (실제로는 Auth::id() 사용)
                'organization_id' => null, // 조직 연결 로직은 필요시 구현
                'created_at' => now(),
                'updated_at' => now()
            ];

            DB::connection($sandboxConnection)
                ->table('projects')
                ->insert($projectData);

            $this->dispatch('project-added', [
                'message' => '프로젝트가 성공적으로 추가되었습니다.',
                'column' => $this->selectedColumn
            ]);

            // 데이터 새로고침
            $this->loadData();
            
            // 모달 닫기
            $this->closeAddProjectModal();
            
        } catch (\Exception $e) {
            // 샘플 데이터로 임시 추가 (실제 DB 연결 실패시)
            $newProjectObj = (object)[
                'id' => rand(1000, 9999),
                'name' => $this->newProject['name'],
                'description' => $this->newProject['description'],
                'status' => $this->newProject['status'],
                'created_by_name' => '현재 사용자',
                'organization_name' => $this->newProject['organization_name'] ?: '기본 조직',
                'created_at' => now()
            ];

            // 해당 컬럼에 프로젝트 추가
            if (!isset($this->projects[$this->selectedColumn])) {
                $this->projects[$this->selectedColumn] = [];
            }
            
            $this->projects[$this->selectedColumn][] = $newProjectObj;

            // 통계 업데이트
            $this->stats['total_projects']++;

            $this->dispatch('project-added', [
                'message' => '프로젝트가 임시로 추가되었습니다. (샘플 데이터)',
                'column' => $this->selectedColumn
            ]);

            // 모달 닫기
            $this->closeAddProjectModal();
        }
    }

    private function mapColumnToStatus($columnId)
    {
        $mapping = [
            'backlog' => 'backlog',
            'todo' => 'todo',
            'in_progress' => 'in_progress',
            'review' => 'review',
            'done' => 'completed'
        ];

        return $mapping[$columnId] ?? 'backlog';
    }
}