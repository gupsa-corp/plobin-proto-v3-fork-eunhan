<?php

namespace App\Livewire\Sandbox\CustomScreens\Live;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GanttChartComponent extends Component
{
    public $projects = [];
    public $stats = [];
    public $currentMonth;
    public $currentYear;
    public $monthDays = [];
    public $viewMode = 'month'; // month, quarter, year

    public function mount()
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
        $this->generateMonthDays();
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.sandbox.custom-screens.live.gantt-chart-component');
    }

    public function loadData()
    {
        try {
            $sandboxConnection = 'sandbox';
            
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

            $this->projects = $allProjects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'status' => $project->status ?? 'active',
                    'created_by_name' => $project->created_by_name ?? '-',
                    'organization_name' => $project->organization_name ?? '-',
                    'start_date' => $project->start_date ?? $project->created_at,
                    'end_date' => $project->end_date ?? Carbon::parse($project->created_at ?? now())->addDays(30),
                    'progress' => $project->progress ?? $this->calculateProgress($project->status ?? 'active'),
                    'created_at' => $project->created_at
                ];
            })->toArray();

            // 통계 데이터
            $this->stats = [
                'total_projects' => $allProjects->count(),
                'on_track' => $allProjects->where('status', 'active')->count(),
                'delayed' => $allProjects->where('status', 'blocked')->count(),
                'completed' => $allProjects->where('status', 'completed')->count()
            ];
            
        } catch (\Exception $e) {
            // 기본값 설정
            $this->projects = [
                [
                    'id' => 1,
                    'name' => '웹사이트 리뉴얼 프로젝트',
                    'description' => '기존 웹사이트의 전면적인 개편',
                    'status' => 'active',
                    'created_by_name' => '홍길동',
                    'organization_name' => '테크 스타트업',
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->endOfMonth(),
                    'progress' => 65,
                    'created_at' => now()->subDays(5)
                ],
                [
                    'id' => 2,
                    'name' => '모바일 앱 개발',
                    'description' => 'iOS/Android 네이티브 앱 개발',
                    'status' => 'in_progress',
                    'created_by_name' => '김철수',
                    'organization_name' => '디지털 에이전시',
                    'start_date' => now()->subDays(10),
                    'end_date' => now()->addDays(20),
                    'progress' => 35,
                    'created_at' => now()->subDays(10)
                ],
                [
                    'id' => 3,
                    'name' => 'API 플랫폼 구축',
                    'description' => 'RESTful API 서버 구축 및 문서화',
                    'status' => 'completed',
                    'created_by_name' => '이영희',
                    'organization_name' => '클라우드 솔루션',
                    'start_date' => now()->subDays(45),
                    'end_date' => now()->subDays(15),
                    'progress' => 100,
                    'created_at' => now()->subDays(45)
                ],
                [
                    'id' => 4,
                    'name' => '데이터베이스 마이그레이션',
                    'description' => '기존 DB를 새로운 스키마로 마이그레이션',
                    'status' => 'blocked',
                    'created_by_name' => '박민수',
                    'organization_name' => '테크 스타트업',
                    'start_date' => now()->addDays(5),
                    'end_date' => now()->addDays(35),
                    'progress' => 15,
                    'created_at' => now()->subDays(2)
                ]
            ];

            $this->stats = [
                'total_projects' => 8,
                'on_track' => 4,
                'delayed' => 2,
                'completed' => 2
            ];
        }
    }

    private function calculateProgress($status)
    {
        switch ($status) {
            case 'completed':
                return 100;
            case 'active':
            case 'in_progress':
                return rand(30, 80);
            case 'blocked':
                return rand(10, 30);
            default:
                return rand(0, 50);
        }
    }

    private function generateMonthDays()
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        $this->monthDays = [];
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $this->monthDays[] = $date->copy();
        }
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->generateMonthDays();
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->generateMonthDays();
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        if ($mode === 'month') {
            $this->generateMonthDays();
        } elseif ($mode === 'quarter') {
            $this->generateQuarterDays();
        } elseif ($mode === 'year') {
            $this->generateYearDays();
        }
    }

    private function generateQuarterDays()
    {
        $startOfQuarter = Carbon::create($this->currentYear, (ceil($this->currentMonth / 3) - 1) * 3 + 1, 1);
        $endOfQuarter = $startOfQuarter->copy()->addMonths(2)->endOfMonth();
        
        $this->monthDays = [];
        for ($date = $startOfQuarter->copy(); $date->lte($endOfQuarter); $date->addWeek()) {
            $this->monthDays[] = $date->copy();
        }
    }

    private function generateYearDays()
    {
        $startOfYear = Carbon::create($this->currentYear, 1, 1);
        $endOfYear = $startOfYear->copy()->endOfYear();
        
        $this->monthDays = [];
        for ($date = $startOfYear->copy(); $date->lte($endOfYear); $date->addMonth()) {
            $this->monthDays[] = $date->copy();
        }
    }

    public function refreshData()
    {
        $this->loadData();
        $this->dispatch('data-refreshed');
    }
}