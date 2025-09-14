<?php

namespace App\Livewire\Sandbox\CustomScreens\Live;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class DashboardComponent extends Component
{
    public $stats = [];
    public $recentActivities = [];
    public $systemStatus = [];

    public function mount()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.sandbox.custom-screens.live.dashboard-component');
    }

    public function loadData()
    {
        // 샌드박스 환경에서는 데이터베이스 연결 대신 실제 작동하는 시뮬레이션 데이터 사용
        $this->stats = [
            'total_organizations' => rand(15, 25),
            'total_projects' => rand(45, 85),
            'total_users' => rand(120, 180)
        ];
        
        $this->recentActivities = [
            ['action' => '새 프로젝트 생성', 'project' => '웹사이트 리뉴얼 프로젝트', 'user' => '홍길동', 'time' => '2시간 전'],
            ['action' => '커스텀 화면 업데이트', 'project' => '모바일 앱 개발', 'user' => '김철수', 'time' => '5시간 전'],
            ['action' => '새 카드 추가', 'project' => 'API 플랫폼 구축', 'user' => '이영희', 'time' => '1일 전'],
            ['action' => '팀 멤버 초대', 'project' => 'ERP 시스템', 'user' => '박민수', 'time' => '2일 전'],
            ['action' => '마일스톤 달성', 'project' => '데이터 분석 도구', 'user' => '최유진', 'time' => '3일 전']
        ];
        
        $this->systemStatus = [
            ['name' => '서버 상태', 'status' => 'normal', 'color' => 'green'],
            ['name' => '데이터베이스', 'status' => 'normal', 'color' => 'green'], 
            ['name' => 'API 서비스', 'status' => 'normal', 'color' => 'green'],
            ['name' => '파일 저장소', 'status' => 'warning', 'color' => 'yellow']
        ];
    }

    public function refreshData()
    {
        $this->loadData();
        $this->dispatch('data-refreshed');
    }
}