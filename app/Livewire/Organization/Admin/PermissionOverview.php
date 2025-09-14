<?php

namespace App\Livewire\Organization\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Organization;
use App\Services\DynamicPermissionService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Collection;

class PermissionOverview extends Component
{
    public $organizationId;
    public $organization;
    
    // Enhanced properties
    public $activeView = 'overview';
    public $showQuickActions = false;
    public $searchTerm = '';
    public $filterRole = '';
    public $selectedMembers = [];
    public $bulkAction = '';
    public $bulkRole = '';

    public function mount($organizationId = 1)
    {
        $this->organizationId = $organizationId;
        $this->loadData();
    }

    public function loadData()
    {
        $this->organization = Organization::with(['users.roles', 'users.permissions'])->find($this->organizationId);
    }

    /**
     * 조직 멤버 데이터 조회
     */
    public function getMembersProperty()
    {
        if (!$this->organization) {
            return [];
        }

        return $this->organization->users->map(function ($user) {
            $roles = $user->roles->pluck('name')->toArray();
            $permissions = $user->getAllPermissions()->pluck('name')->toArray();
            $primaryRole = $roles[0] ?? 'member';
            $roleInfo = $this->getRoleDisplayInfo($primaryRole);
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles,
                'primary_role' => $primaryRole,
                'role_label' => $roleInfo['label'],
                'role_color' => $roleInfo['color'],
                'permission_count' => count($permissions),
                'joined_at' => $user->pivot->created_at->format('Y-m-d'),
                'last_activity' => $user->last_login_at ? $user->last_login_at->diffForHumans() : '로그인 기록 없음',
                'direct_permissions' => $user->getDirectPermissions()->pluck('name')->toArray(),
                'permissions' => [
                    'member' => in_array('manage_organization_members', $permissions),
                    'project' => in_array('manage_organization_projects', $permissions),
                    'billing' => in_array('manage_organization_billing', $permissions),
                    'organization' => in_array('manage_organization_settings', $permissions),
                ]
            ];
        })->toArray();
    }

    /**
     * 통계 데이터 조회
     */
    public function getStatsProperty()
    {
        if (!$this->organization) {
            return [
                'totalMembers' => 0,
                'activeMembers' => 0,
                'totalRoles' => Role::count(),
                'totalPermissions' => Permission::count(),
                'averagePermissions' => 0,
                'permissionCoverage' => 0,
                'recentActivity' => 0,
                'roleDistribution' => []
            ];
        }

        $users = $this->organization->users;
        $roles = Role::all();
        $permissions = Permission::all();

        // 활성 멤버 수 계산 (최근 30일 내 로그인한 사용자)
        $activeMembers = $users->filter(function ($user) {
            return $user->last_login_at && $user->last_login_at->gt(now()->subDays(30));
        })->count();

        // 평균 권한 수 계산
        $totalUserPermissions = $users->sum(function ($user) {
            return $user->getAllPermissions()->count();
        });
        $averagePermissions = $users->count() > 0 ? round($totalUserPermissions / $users->count(), 1) : 0;

        // 권한 커버리지 계산 (사용된 권한 / 전체 권한 * 100)
        $usedPermissions = $users->flatMap(function ($user) {
            return $user->getAllPermissions()->pluck('name');
        })->unique();
        $permissionCoverage = $permissions->count() > 0 ? round(($usedPermissions->count() / $permissions->count()) * 100, 1) : 0;

        // 최근 활동 수 (임시로 랜덤 값, 실제로는 activity log 테이블에서 가져와야 함)
        $recentActivity = rand(5, 25);

        // 역할 분포 계산
        $roleDistribution = [];
        foreach ($roles as $role) {
            $count = $users->filter(function ($user) use ($role) {
                return $user->hasRole($role->name);
            })->count();
            
            if ($count > 0) {
                $roleDistribution[$role->name] = [
                    'count' => $count,
                    'label' => $role->name,
                    'color' => $this->getRoleColor($role->name)
                ];
            }
        }

        return [
            'totalMembers' => $users->count(),
            'activeMembers' => $activeMembers,
            'totalRoles' => $roles->count(),
            'totalPermissions' => $permissions->count(),
            'averagePermissions' => $averagePermissions,
            'permissionCoverage' => $permissionCoverage,
            'recentActivity' => $recentActivity,
            'roleDistribution' => $roleDistribution
        ];
    }
    
    /**
     * 사용 가능한 역할 조회
     */
    public function getAvailableRolesProperty()
    {
        return Role::all();
    }
    
    /**
     * 뷰 전환
     */
    public function switchView($view)
    {
        $this->activeView = $view;
    }
    
    /**
     * 빠른 작업 패널 토글
     */
    public function toggleQuickActions()
    {
        $this->showQuickActions = !$this->showQuickActions;
    }
    
    /**
     * 멤버 선택 토글
     */
    public function toggleMemberSelection($memberId)
    {
        if (in_array($memberId, $this->selectedMembers)) {
            $this->selectedMembers = array_filter($this->selectedMembers, function($id) use ($memberId) {
                return $id != $memberId;
            });
        } else {
            $this->selectedMembers[] = $memberId;
        }
    }
    
    /**
     * 일괄 작업 적용
     */
    public function applyBulkAction()
    {
        if (empty($this->selectedMembers) || empty($this->bulkAction)) {
            session()->flash('error', '멤버를 선택하고 작업을 지정해주세요.');
            return;
        }
        
        foreach ($this->selectedMembers as $memberId) {
            $user = User::find($memberId);
            if (!$user) continue;
            
            if ($this->bulkAction === 'assign_role' && $this->bulkRole) {
                $user->assignRole($this->bulkRole);
            } elseif ($this->bulkAction === 'remove_role' && $this->bulkRole) {
                $user->removeRole($this->bulkRole);
            }
        }
        
        $this->selectedMembers = [];
        $this->bulkAction = '';
        $this->bulkRole = '';
        $this->loadData();
        
        session()->flash('success', '일괄 작업이 완료되었습니다.');
    }
    
    /**
     * 사용자에서 역할 제거
     */
    public function removeRoleFromUser($userId, $roleName)
    {
        $user = User::find($userId);
        if ($user) {
            $user->removeRole($roleName);
            $this->loadData();
            session()->flash('success', "'{$roleName}' 역할이 제거되었습니다.");
        }
    }
    
    /**
     * 빠른 역할 할당
     */
    public function quickAssignRole($userId, $roleName)
    {
        $user = User::find($userId);
        if ($user) {
            $user->assignRole($roleName);
            $this->loadData();
            session()->flash('success', "'{$roleName}' 역할이 할당되었습니다.");
        }
    }

    /**
     * 역할 색상 반환
     */
    public function getRoleColor($roleName)
    {
        $colors = [
            'admin' => 'red',
            'manager' => 'blue',
            'member' => 'green',
            'viewer' => 'gray',
            'moderator' => 'purple',
            'editor' => 'yellow',
        ];
        
        return $colors[$roleName] ?? 'indigo';
    }

    /**
     * 역할 표시 정보 반환
     */
    public function getRoleDisplayInfo($roleName)
    {
        $roleInfo = [
            'admin' => ['label' => '관리자', 'color' => 'red'],
            'manager' => ['label' => '매니저', 'color' => 'blue'],
            'member' => ['label' => '멤버', 'color' => 'green'],
            'viewer' => ['label' => '뷰어', 'color' => 'gray'],
            'moderator' => ['label' => '모더레이터', 'color' => 'purple'],
            'editor' => ['label' => '에디터', 'color' => 'yellow'],
        ];
        
        return $roleInfo[$roleName] ?? ['label' => $roleName, 'color' => 'indigo'];
    }

    /**
     * 권한 매트릭스 데이터
     */
    public function getPermissionMatrixProperty()
    {
        $roles = Role::with('permissions')->get();
        $matrix = [];
        
        foreach ($roles as $role) {
            $roleInfo = $this->getRoleDisplayInfo($role->name);
            $permissions = $role->permissions->pluck('name')->toArray();
            
            // 권한을 카테고리별로 분류
            $categories = [
                '멤버 관리' => ['manage_organization_members'],
                '프로젝트 관리' => ['manage_organization_projects'],
                '결제 관리' => ['manage_organization_billing'],
                '조직 설정' => ['manage_organization_settings'],
                '권한 관리' => ['manage_roles', 'manage_permissions'],
                '기타' => []
            ];
            
            $categoryData = [];
            foreach ($categories as $categoryName => $categoryPermissions) {
                if ($categoryName === '기타') {
                    // 기타는 다른 카테고리에 속하지 않는 모든 권한
                    $otherPermissions = collect($permissions)->diff(
                        collect($categories)->except('기타')->flatten()->toArray()
                    );
                    $count = $otherPermissions->count();
                    $total = Permission::whereNotIn('name', 
                        collect($categories)->except('기타')->flatten()->toArray()
                    )->count();
                } else {
                    $count = collect($permissions)->intersect($categoryPermissions)->count();
                    $total = count($categoryPermissions);
                }
                
                $categoryData[$categoryName] = [
                    'count' => $count,
                    'total' => max($total, 1), // Avoid division by zero
                    'percentage' => $total > 0 ? ($count / $total) * 100 : 0
                ];
            }
            
            $matrix[$role->name] = [
                'info' => $roleInfo,
                'categories' => $categoryData
            ];
        }
        
        return $matrix;
    }

    /**
     * 최근 활동 데이터
     */
    public function getRecentActivityProperty()
    {
        // 실제 구현에서는 activity log 테이블에서 데이터를 가져와야 합니다
        // 여기서는 샘플 데이터를 반환합니다
        return [
            [
                'type' => 'success',
                'details' => '새로운 멤버가 조직에 가입했습니다',
                'user' => 'System',
                'timestamp' => now()->subHours(2)->toISOString()
            ],
            [
                'type' => 'info',
                'details' => '권한 설정이 업데이트되었습니다',
                'user' => 'Admin',
                'timestamp' => now()->subHours(5)->toISOString()
            ],
            [
                'type' => 'warning',
                'details' => '비정상적인 권한 사용이 감지되었습니다',
                'user' => 'System',
                'timestamp' => now()->subDay()->toISOString()
            ],
            [
                'type' => 'success',
                'details' => '일괄 역할 할당이 완료되었습니다',
                'user' => 'Manager',
                'timestamp' => now()->subDays(2)->toISOString()
            ]
        ];
    }

    public function render()
    {
        return view('livewire.organization.admin.302-permission-overview', [
            'members' => $this->getMembersProperty(),
            'stats' => $this->getStatsProperty(),
            'permissionMatrix' => $this->getPermissionMatrixProperty(),
            'recentActivity' => $this->getRecentActivityProperty(),
            'availableRoles' => $this->getAvailableRolesProperty()
        ]);
    }
}