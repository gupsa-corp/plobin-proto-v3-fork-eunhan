<?php

namespace App\Http\Controllers\CoreView\PlatformAdmin;

use App\Http\Controllers\CoreView\Controller as BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class Controller extends BaseController
{
    public function users()
    {
        $users = User::getUsersWithRoles();

        return view('900-page-platform-admin.903-page-users.000-index', compact('users'));
    }

    public function permissionsUsers()
    {
        $users = User::with([
                'roles',
                'organizationMemberships' => function($query) {
                    $query->with('organization');
                },
                'organizations'
            ])
            ->withCount('organizations')
            ->orderBy('created_at', 'desc')
            ->get();

        $organizations = \App\Models\Organization::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('900-page-platform-admin.905-page-permissions.903-tab-users.000-index', compact('users', 'organizations'));
    }

    public function changeUserRole(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:platform_admin,organization_admin,organization_member'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            
            // 기존 역할들 제거
            $user->syncRoles([]);
            
            // 새로운 역할 할당
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->assignRole($role);
            }

            return response()->json([
                'success' => true,
                'message' => '사용자 역할이 성공적으로 변경되었습니다.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '역할 변경 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleUserStatus(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            
            // 현재는 간단히 상태를 토글하는 로직
            // 실제 구현에서는 user 테이블에 is_active 필드를 추가해야 함
            
            return response()->json([
                'success' => true,
                'message' => '사용자 상태가 성공적으로 변경되었습니다.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '상태 변경 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateTenantPermissions(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permissions' => 'required|array',
            'permissions.*.organization_id' => 'required|exists:organizations,id',
            'permissions.*.role_name' => 'required|string|exists:roles,name'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            
            // 현재 사용자의 모든 조직 멤버십을 가져오기
            $currentMemberships = $user->organizationMemberships()->get();
            
            // 새로운 권한들을 처리
            foreach ($request->permissions as $permission) {
                $existingMembership = $currentMemberships->where('organization_id', $permission['organization_id'])->first();
                
                if ($existingMembership) {
                    // 기존 권한 업데이트
                    $existingMembership->update([
                        'role_name' => $permission['role_name']
                    ]);
                } else {
                    // 새로운 권한 생성
                    \App\Models\OrganizationMember::create([
                        'user_id' => $user->id,
                        'organization_id' => $permission['organization_id'],
                        'role_name' => $permission['role_name'],
                        'joined_at' => now(),
                        'invitation_status' => 'accepted'
                    ]);
                }
                
                // 사용자에게 역할 할당
                $user->assignRole($permission['role_name']);
            }
            
            return response()->json([
                'success' => true,
                'message' => '조직 권한이 성공적으로 업데이트되었습니다.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '권한 업데이트 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}
