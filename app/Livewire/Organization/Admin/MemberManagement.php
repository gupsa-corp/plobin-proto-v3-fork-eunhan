<?php

namespace App\Livewire\Organization\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Services\DynamicPermissionService;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrganizationInvitationMail;

class MemberManagement extends Component
{
    public $organizationId;
    public $organization;
    public $members = [];
    public $stats = [];
    public $searchTerm = '';
    public $permissionFilter = '';
    public $statusFilter = '';

    // 새 멤버 초대 관련 속성
    public $showInviteModal = false;
    public $inviteEmail = '';
    public $inviteRole = 'user'; // 기본 사용자 역할

    // 멤버 편집 관련 속성
    public $showEditModal = false;
    public $editingMemberId = null;
    public $editingMemberRole = '';
    public $editingMemberPermissions = [];

    public function mount($organizationId)
    {
        $this->organizationId = $organizationId;
        try {
            $this->loadData();
        } catch (\Exception $e) {
            logger()->error('MemberManagement mount error: ' . $e->getMessage());
            $this->organization = null;
            $this->members = [];
            $this->stats = ['total' => 0, 'active' => 0, 'pending' => 0, 'admin' => 0];
        }
    }

    public function loadData()
    {
        // 조직 정보 로드
        $this->organization = Organization::find($this->organizationId);

        // 실제 DB에서 조직 멤버 로드
        $this->members = $this->getActualMembers();

        // 통계 계산
        $this->calculateStats();
    }

    private function getActualMembers()
    {
        try {
            $members = OrganizationMember::with('user')
                ->where('organization_id', $this->organizationId)
                ->orderBy('role_name')
                ->orderBy('joined_at', 'desc')
                ->get();

            logger()->info('Found members for organization ' . $this->organizationId . ': ' . $members->count());

            return $members->map(function ($member) {
                try {
                    // 사용자의 역할 정보 가져오기
                    $userRoles = $member->user->getRoleNames();
                    $primaryRole = $userRoles->first() ?? 'user';
                    $role = Role::findByName($primaryRole);

                    $statusName = match($member->invitation_status) {
                        'pending' => '대기 중',
                        'accepted' => '활성',
                        'declined' => '거절됨',
                        default => '알 수 없음'
                    };

                    $lastActive = $member->invitation_status === 'accepted' ?
                        ($member->joined_at ? $member->joined_at->diffForHumans() : '알 수 없음') :
                        '-';

                    // 역할 기반 권한 정보 구성
                    $roleInfo = $this->getRoleDisplayInfo($primaryRole);

                    return [
                        'id' => $member->id,
                        'user_id' => $member->user->id,
                        'name' => $member->user->name ?? $member->user->email,
                        'email' => $member->user->email,
                        'role' => $primaryRole,
                        'permission' => [
                            'role' => $primaryRole,
                            'label' => $roleInfo['label'],
                            'short_label' => $roleInfo['short_label'],
                            'badge_color' => $roleInfo['badge_color'],
                            'level' => $roleInfo['level'],
                            'level_name' => $roleInfo['level_name'],
                            'permissions' => $member->user->getAllPermissions()->pluck('name')->toArray()
                        ],
                        'status' => $member->invitation_status,
                        'status_name' => $statusName,
                        'joined_at' => $member->joined_at ? $member->joined_at->format('Y.m.d') :
                                      ($member->invited_at ? $member->invited_at->format('Y.m.d') : '-'),
                        'last_active' => $lastActive,
                        'avatar_color' => $roleInfo['badge_color'],
                        'avatar_initial' => substr($member->user->name ?? $member->user->email, 0, 1)
                    ];
                } catch (\Exception $e) {
                    logger()->error('Error processing member ' . $member->id . ': ' . $e->getMessage());
                    return null;
                }
            })->filter()->values()->toArray();

        } catch (\Exception $e) {
            logger()->error('Error fetching members: ' . $e->getMessage());
            return [];
        }
    }

    private function calculateStats()
    {
        if (!is_array($this->members)) {
            $this->members = [];
        }

        $totalMembers = count($this->members);
        $activeMembers = count(array_filter($this->members, fn($member) => $member['status'] === 'active'));
        $pendingMembers = count(array_filter($this->members, fn($member) => $member['status'] === 'pending'));
        $adminMembers = count(array_filter($this->members, fn($member) =>
            in_array($member['role'], ['organization_admin', 'organization_owner', 'platform_admin'])
        ));

        $this->stats = [
            'total' => $totalMembers,
            'active' => $activeMembers,
            'pending' => $pendingMembers,
            'admin' => $adminMembers
        ];
    }

    public function getFilteredMembersProperty()
    {
        $members = $this->members ?? [];

        // 검색어 필터
        if ($this->searchTerm) {
            $members = array_filter($members, function ($member) {
                return stripos($member['name'], $this->searchTerm) !== false ||
                       stripos($member['email'], $this->searchTerm) !== false;
            });
        }

        // 권한 필터
        if ($this->permissionFilter && $this->permissionFilter !== '') {
            $members = array_filter($members, function ($member) {
                return $member['role'] === $this->permissionFilter;
            });
        }

        // 상태 필터
        if ($this->statusFilter && $this->statusFilter !== '') {
            $members = array_filter($members, function ($member) {
                return $member['status'] === $this->statusFilter;
            });
        }

        return $members;
    }

    public function changeRole($memberId, $newRoleName)
    {
        try {
            $member = OrganizationMember::findOrFail($memberId);
            $user = $member->user;

            // 조직 소유자의 권한을 낮추려고 하는 경우 방지
            if ($user->hasRole('organization_owner') && $newRoleName !== 'organization_owner') {
                $this->dispatch('error', [
                    'message' => '조직 소유자의 권한은 낮출 수 없습니다. 소유자 권한은 보호됩니다.'
                ]);
                return;
            }

            // 현재 사용자가 로그인되어 있고 멤버 관리 권한이 있는지 체크
            if (!auth()->check() || !auth()->user()->can('manage members')) {
                $this->dispatch('error', [
                    'message' => '멤버 관리 권한이 없습니다.'
                ]);
                return;
            }

            // 기존 역할 제거 후 새 역할 할당
            $user->syncRoles([$newRoleName]);

            // 동적 권한 서비스를 통한 기본 권한 할당
            app(DynamicPermissionService::class)->assignBasicPermissionsByRole($user, $newRoleName);

            $this->loadData(); // 데이터 새로고침

            $roleInfo = $this->getRoleDisplayInfo($newRoleName);
            $this->dispatch('permissionChanged', [
                'memberId' => $memberId,
                'newPermission' => $roleInfo['label'],
                'message' => '역할이 성공적으로 변경되었습니다.'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('error', [
                'message' => '역할 변경 중 오류가 발생했습니다: ' . $e->getMessage()
            ]);
        }
    }

    public function removeMember($memberId)
    {
        try {
            $member = OrganizationMember::findOrFail($memberId);
            $userName = $member->user->name ?? $member->user->email;

            // 조직 소유자는 삭제할 수 없도록 보호
            if ($member->user->hasRole('organization_owner')) {
                $this->dispatch('error', [
                    'message' => '조직 소유자는 삭제할 수 없습니다.'
                ]);
                return;
            }

            $member->delete();

            // 조직의 멤버 수 업데이트
            $this->organization->update([
                'members_count' => $this->organization->members()->count()
            ]);

            $this->loadData(); // 데이터 새로고침

            $this->dispatch('memberRemoved', [
                'memberId' => $memberId,
                'message' => "{$userName} 님이 조직에서 제거되었습니다."
            ]);

        } catch (\Exception $e) {
            $this->dispatch('error', [
                'message' => '멤버 제거 중 오류가 발생했습니다: ' . $e->getMessage()
            ]);
        }
    }

    public function resendInvitation($memberId)
    {
        try {
            $member = OrganizationMember::findOrFail($memberId);

            // 초대 상태가 아닌 경우 에러
            if ($member->invitation_status !== 'pending') {
                $this->dispatch('error', [
                    'message' => '대기 중인 초대만 재전송할 수 있습니다.'
                ]);
                return;
            }

            // 초대 재전송 시간 업데이트
            $member->update([
                'invited_at' => now()
            ]);

            // 이메일 재전송
            try {
                Mail::to($member->user->email)->send(new OrganizationInvitationMail($member));
                $this->dispatch('invitationResent', [
                    'memberId' => $memberId,
                    'message' => $member->user->email . '로 초대가 재전송되었습니다.'
                ]);
            } catch (\Exception $e) {
                logger()->error('Failed to resend invitation email: ' . $e->getMessage());
                $this->dispatch('error', [
                    'message' => '초대 재전송 중 오류가 발생했습니다: ' . $e->getMessage()
                ]);
                return;
            }

        } catch (\Exception $e) {
            $this->dispatch('error', [
                'message' => '초대 재전송 중 오류가 발생했습니다: ' . $e->getMessage()
            ]);
        }
    }

    public function updatingSearchTerm()
    {
        // 검색어가 변경될 때 실행
    }

    public function resetFilters()
    {
        $this->searchTerm = '';
        $this->permissionFilter = '';
        $this->statusFilter = '';
    }

    public function openInviteModal()
    {
        logger()->info('Opening invite modal - method called');
        $this->showInviteModal = true;
        $this->inviteEmail = '';
        $this->inviteRole = 'user';
        logger()->info('Invite modal opened, showInviteModal: ' . ($this->showInviteModal ? 'true' : 'false'));

        // JavaScript 콘솔에도 로그 출력
        $this->dispatch('console-log', [
            'message' => 'openInviteModal called, showInviteModal: ' . ($this->showInviteModal ? 'true' : 'false')
        ]);
    }

    public function closeInviteModal()
    {
        $this->showInviteModal = false;
        $this->inviteEmail = '';
        $this->inviteRole = 'user';
    }

    public function inviteMember()
    {
        $this->validate([
            'inviteEmail' => 'required|email',
            'inviteRole' => 'required|string|exists:roles,name'
        ]);

        try {
            // 이미 초대된 사용자인지 확인
            $existingMember = OrganizationMember::where('organization_id', $this->organizationId)
                ->whereHas('user', function($query) {
                    $query->where('email', $this->inviteEmail);
                })
                ->first();

            if ($existingMember) {
                $this->dispatch('error', [
                    'message' => '이미 조직에 초대된 사용자입니다.'
                ]);
                return;
            }

            // 사용자 찾기 또는 생성
            $user = User::firstOrCreate([
                'email' => $this->inviteEmail
            ], [
                'password' => bcrypt('temporary_password'),
                'first_name' => explode('@', $this->inviteEmail)[0],
                'country_code' => 'KR'
            ]);

            // 사용자에게 역할 할당
            $user->assignRole($this->inviteRole);

            // 동적 권한 서비스를 통한 기본 권한 할당
            app(DynamicPermissionService::class)->assignBasicPermissionsByRole($user, $this->inviteRole);

            // 조직 멤버 추가
            $member = OrganizationMember::create([
                'organization_id' => $this->organizationId,
                'user_id' => $user->id,
                'role_name' => $this->inviteRole,
                'invitation_status' => 'pending',
                'invited_at' => now()
            ]);

            // 조직의 멤버 수 업데이트
            $this->organization->update([
                'members_count' => $this->organization->members()->count()
            ]);

            // 이메일 전송
            try {
                Mail::to($user->email)->send(new OrganizationInvitationMail($member));
            } catch (\Exception $e) {
                logger()->error('Failed to send invitation email: ' . $e->getMessage());
                // 이메일 전송 실패해도 초대는 성공으로 처리
            }

            $this->loadData(); // 데이터 새로고침
            $this->closeInviteModal();

            $roleInfo = $this->getRoleDisplayInfo($this->inviteRole);
            $this->dispatch('memberInvited', [
                'email' => $this->inviteEmail,
                'role' => $roleInfo['label'],
                'message' => "{$this->inviteEmail}로 초대를 전송했습니다."
            ]);

        } catch (\Exception $e) {
            $this->dispatch('error', [
                'message' => '멤버 초대 중 오류가 발생했습니다: ' . $e->getMessage()
            ]);
        }
    }

    public function editMember($userId)
    {
        try {
            // 멤버 정보 가져오기
            $member = OrganizationMember::with('user')
                ->where('organization_id', $this->organizationId)
                ->where('user_id', $userId)
                ->firstOrFail();

            // 편집 가능한 멤버인지 확인 (조직 소유자는 편집 불가)
            if ($member->user->hasRole('organization_owner')) {
                $this->dispatch('error', [
                    'message' => '조직 소유자의 정보는 편집할 수 없습니다.'
                ]);
                return;
            }

            // 현재 사용자가 로그인되어 있고 멤버 관리 권한이 있는지 체크
            if (!auth()->check() || !auth()->user()->can('manage members')) {
                $this->dispatch('error', [
                    'message' => '멤버 편집 권한이 없습니다.'
                ]);
                return;
            }

            // 편집 모달 데이터 설정
            $this->editingMemberId = $member->id;
            
            // 현재 역할 가져오기
            $userRoles = $member->user->getRoleNames();
            $this->editingMemberRole = $userRoles->first() ?? 'user';
            
            // 현재 권한 목록 가져오기
            $this->editingMemberPermissions = $member->user->getAllPermissions()->pluck('name')->toArray();
            
            $this->showEditModal = true;

        } catch (\Exception $e) {
            $this->dispatch('error', [
                'message' => '멤버 정보를 불러오는 중 오류가 발생했습니다: ' . $e->getMessage()
            ]);
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingMemberId = null;
        $this->editingMemberRole = '';
        $this->editingMemberPermissions = [];
    }

    public function updateMember()
    {
        $this->validate([
            'editingMemberRole' => 'required|string|exists:roles,name'
        ]);

        try {
            if (!$this->editingMemberId) {
                $this->dispatch('error', [
                    'message' => '편집할 멤버 정보가 없습니다.'
                ]);
                return;
            }

            $member = OrganizationMember::with('user')->findOrFail($this->editingMemberId);
            $user = $member->user;

            // 조직 소유자의 권한을 낮추려고 하는 경우 방지
            if ($user->hasRole('organization_owner') && $this->editingMemberRole !== 'organization_owner') {
                $this->dispatch('error', [
                    'message' => '조직 소유자의 권한은 낮출 수 없습니다.'
                ]);
                return;
            }

            // 역할 변경
            $user->syncRoles([$this->editingMemberRole]);

            // 동적 권한 서비스를 통한 기본 권한 할당
            app(DynamicPermissionService::class)->assignBasicPermissionsByRole($user, $this->editingMemberRole);

            // role_name 업데이트
            $member->update([
                'role_name' => $this->editingMemberRole
            ]);

            $this->loadData(); // 데이터 새로고침
            $this->closeEditModal();

            $roleInfo = $this->getRoleDisplayInfo($this->editingMemberRole);
            $this->dispatch('memberUpdated', [
                'memberId' => $member->id,
                'newRole' => $roleInfo['label'],
                'message' => '멤버 정보가 성공적으로 업데이트되었습니다.'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('error', [
                'message' => '멤버 정보 업데이트 중 오류가 발생했습니다: ' . $e->getMessage()
            ]);
        }
    }

    public function togglePermission($permissionName)
    {
        try {
            if (!$this->editingMemberId) {
                return;
            }

            $member = OrganizationMember::with('user')->findOrFail($this->editingMemberId);
            $user = $member->user;

            if ($user->hasPermissionTo($permissionName)) {
                $user->revokePermissionTo($permissionName);
                $this->editingMemberPermissions = array_diff($this->editingMemberPermissions, [$permissionName]);
            } else {
                $user->givePermissionTo($permissionName);
                $this->editingMemberPermissions[] = $permissionName;
            }

            $this->editingMemberPermissions = array_values(array_unique($this->editingMemberPermissions));

        } catch (\Exception $e) {
            $this->dispatch('error', [
                'message' => '권한 변경 중 오류가 발생했습니다: ' . $e->getMessage()
            ]);
        }
    }

    public function getAvailablePermissionsProperty()
    {
        // 조직에서 관리 가능한 권한 목록 반환
        return [
            'manage members' => '멤버 관리',
            'manage projects' => '프로젝트 관리',
            'manage services' => '서비스 관리',
            'view reports' => '보고서 조회',
            'manage settings' => '설정 관리',
            'access api' => 'API 접근',
            'manage billing' => '결제 관리'
        ];
    }

    public function getEditingMemberProperty()
    {
        if (!$this->editingMemberId) {
            return null;
        }

        return OrganizationMember::with('user')->find($this->editingMemberId);
    }

    public function getAvailableRolesProperty()
    {
        return Role::all()->map(function ($role) {
            $info = $this->getRoleDisplayInfo($role->name);
            return [
                'name' => $role->name,
                'display_name' => $info['label']
            ];
        })->toArray();
    }

    /**
     * 역할별 표시 정보 반환
     */
    private function getRoleDisplayInfo($roleName)
    {
        return match($roleName) {
            'user' => [
                'label' => '사용자',
                'short_label' => '사용자',
                'badge_color' => 'blue',
                'level' => 1,
                'level_name' => '사용자'
            ],
            'service_manager' => [
                'label' => '서비스 매니저',
                'short_label' => '매니저',
                'badge_color' => 'green',
                'level' => 2,
                'level_name' => '서비스 매니저'
            ],
            'organization_admin' => [
                'label' => '조직 관리자',
                'short_label' => '관리자',
                'badge_color' => 'purple',
                'level' => 3,
                'level_name' => '조직 관리자'
            ],
            'organization_owner' => [
                'label' => '조직 소유자',
                'short_label' => '소유자',
                'badge_color' => 'red',
                'level' => 4,
                'level_name' => '조직 소유자'
            ],
            'platform_admin' => [
                'label' => '플랫폼 관리자',
                'short_label' => '최고관리자',
                'badge_color' => 'gray',
                'level' => 5,
                'level_name' => '플랫폼 관리자'
            ],
            default => [
                'label' => '게스트',
                'short_label' => '게스트',
                'badge_color' => 'yellow',
                'level' => 0,
                'level_name' => '게스트'
            ]
        };
    }


    public function render()
    {
        return view('livewire.organization.admin.300-member-management', [
            'filteredMembers' => $this->getFilteredMembersProperty(),
            'availableRoles' => $this->getAvailableRolesProperty(),
            'availablePermissions' => $this->getAvailablePermissionsProperty()
        ]);
    }
}
