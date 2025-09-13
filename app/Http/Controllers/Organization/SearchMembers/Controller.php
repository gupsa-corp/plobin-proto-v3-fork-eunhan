<?php

namespace App\Http\Controllers\Organization\SearchMembers;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\Organization;
use App\Models\User;
use App\Models\OrganizationMember;
use App\Services\DynamicPermissionService;
use App\Exceptions\ApiException;

class Controller extends ApiController
{
    public function __invoke(Request $request, $organizationId)
    {
        // 조직 존재 여부 확인
        $organization = Organization::find($organizationId);
        if (!$organization) {
            throw ApiException::notFound('조직을 찾을 수 없습니다.');
        }

        // 현재 사용자의 권한 확인
        $currentUser = auth()->user();
        $currentMember = OrganizationMember::where('organization_id', $organizationId)
            ->where('user_id', $currentUser->id)
            ->first();

        if (!$currentMember || !$currentUser->can('manage members')) {
            throw ApiException::forbidden('멤버 관리 권한이 없습니다.');
        }

        $query = $request->get('query');
        $limit = $request->get('limit', 10);

        // 사용자 검색 (이메일 또는 이름으로)
        $users = User::where(function ($q) use ($query) {
            $q->where('email', 'LIKE', "%{$query}%")
              ->orWhere('name', 'LIKE', "%{$query}%")
              ->orWhere('nickname', 'LIKE', "%{$query}%");
        })
        ->limit($limit)
        ->get();

        // 각 사용자의 조직 내 상태 확인
        $results = $users->map(function ($user) use ($organizationId) {
            $membership = OrganizationMember::where('organization_id', $organizationId)
                ->where('user_id', $user->id)
                ->first();

            $status = 'available'; // 초대 가능
            $statusText = '초대 가능';
            $roleInfo = null;

            if ($membership) {
                switch ($membership->invitation_status) {
                    case 'pending':
                        $status = 'pending';
                        $statusText = '초대 대기 중';
                        break;
                    case 'accepted':
                        $status = 'member';
                        $statusText = '이미 멤버';
                        $userRoles = $user->getRoleNames();
                        $primaryRole = $userRoles->first() ?? 'user';
                        $roleInfo = $this->getRoleDisplayInfo($primaryRole);
                        break;
                    case 'declined':
                        $status = 'declined';
                        $statusText = '초대 거절';
                        break;
                }
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar_url ?? null,
                'status' => $status,
                'status_text' => $statusText,
                'role' => $roleInfo ? [
                    'name' => $roleInfo['name'],
                    'label' => $roleInfo['label'],
                    'badge_color' => $roleInfo['badge_color']
                ] : null,
                'joined_at' => $membership?->joined_at?->format('Y.m.d'),
                'invited_at' => $membership?->invited_at?->format('Y.m.d')
            ];
        });

        return $this->success([
            'users' => $results,
            'total' => $results->count(),
            'query' => $query
        ]);
    }

    /**
     * 역할별 표시 정보 반환
     */
    private function getRoleDisplayInfo($roleName)
    {
        return match($roleName) {
            'user' => [
                'name' => 'user',
                'label' => '사용자',
                'badge_color' => 'blue'
            ],
            'service_manager' => [
                'name' => 'service_manager',
                'label' => '서비스 매니저',
                'badge_color' => 'green'
            ],
            'organization_admin' => [
                'name' => 'organization_admin',
                'label' => '조직 관리자',
                'badge_color' => 'purple'
            ],
            'organization_owner' => [
                'name' => 'organization_owner',
                'label' => '조직 소유자',
                'badge_color' => 'red'
            ],
            'platform_admin' => [
                'name' => 'platform_admin',
                'label' => '플랫폼 관리자',
                'badge_color' => 'gray'
            ],
            default => [
                'name' => $roleName,
                'label' => $roleName,
                'badge_color' => 'indigo'
            ]
        };
    }
}
