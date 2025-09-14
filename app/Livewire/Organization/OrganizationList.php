<?php

namespace App\Livewire\Organization;

use Livewire\Component;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

class OrganizationList extends Component
{
    public $organizations = [];
    public $isLoading = true;

    protected $listeners = ['organizationCreated' => 'loadOrganizations'];

    public function mount()
    {
        $this->loadOrganizations();
    }

    public function loadOrganizations()
    {
        // 조직 관련 페이지들에 조직 데이터 전달 (소유자/관리자 역할 포함)
        $this->organizations = Organization::select([
                'organizations.id',
                'organizations.name',
                'organizations.description',
                'organizations.user_id',
                'organization_members.role_name',
                'organizations.created_at'
            ])
            ->selectSub(function($query) {
                $query->from('organization_members')
                      ->whereColumn('organization_id', 'organizations.id')
                      ->where('invitation_status', 'accepted')
                      ->selectRaw('count(*)');
            }, 'members_count')
            ->join('organization_members', 'organizations.id', '=', 'organization_members.organization_id')
            ->where('organization_members.user_id', Auth::id())
            ->where('organization_members.invitation_status', 'accepted')
            ->orderBy('organizations.created_at', 'desc')
            ->get()
            ->map(function($org) {
                // 조직 소유자인지 확인
                if ($org->user_id == Auth::id()) {
                    $org->user_role = '소유자';
                } else {
                    // 역할에 따라 표시
                    switch ($org->role_name) {
                        case 'admin':
                            $org->user_role = '관리자';
                            break;
                        case 'pm':
                            $org->user_role = 'PM';
                            break;
                        case 'member':
                            $org->user_role = '사용자';
                            break;
                        case 'guest':
                        default:
                            $org->user_role = '권한없음';
                            break;
                    }
                }
                return $org;
            });
        $this->isLoading = false;
    }

    public function render()
    {
        return view('300-page-service.306-page-organizations-list.300-livewire-organization-list');
    }
}