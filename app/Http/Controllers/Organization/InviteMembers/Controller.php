<?php

namespace App\Http\Controllers\Organization\InviteMembers;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\Organization;
use App\Models\User;
use App\Models\OrganizationMember;
use App\Services\DynamicPermissionService;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

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

        if (!$currentMember || !$currentUser->can('invite members')) {
            throw ApiException::forbidden('멤버 초대 권한이 없습니다.');
        }

        $invitations = $request->get('invitations');
        $results = [
            'successful' => [],
            'failed' => [],
            'already_exists' => []
        ];

        DB::beginTransaction();

        try {
            foreach ($invitations as $invitation) {
                $email = $invitation['email'];
                $roleName = $invitation['role'] ?? 'user';
                $message = $invitation['message'] ?? null;

                // 역할 유효성 확인
                $role = Role::where('name', $roleName)->first();
                if (!$role) {
                    $results['failed'][] = [
                        'email' => $email,
                        'reason' => '유효하지 않은 역할입니다.'
                    ];
                    continue;
                }

                // 역할 부여 가능 여부 확인
                if (!$this->canGrantRole($currentUser, $roleName)) {
                    $results['failed'][] = [
                        'email' => $email,
                        'reason' => '해당 역할을 부여할 수 있는 권한이 없습니다.'
                    ];
                    continue;
                }

                // 사용자 찾기
                $user = User::where('email', $email)->first();

                if (!$user) {
                    $results['failed'][] = [
                        'email' => $email,
                        'reason' => '등록되지 않은 사용자입니다. 먼저 회원가입을 진행해주세요.'
                    ];
                    continue;
                }

                // 이미 조직 멤버인지 확인
                $existingMember = OrganizationMember::where('organization_id', $organizationId)
                    ->where('user_id', $user->id)
                    ->first();

                if ($existingMember) {
                    $statusText = match($existingMember->invitation_status) {
                        'pending' => '이미 초대된 사용자입니다',
                        'accepted' => '이미 조직의 멤버입니다',
                        'declined' => '초대를 거절한 사용자입니다',
                        default => '이미 관련 기록이 있는 사용자입니다'
                    };

                    $results['already_exists'][] = [
                        'email' => $email,
                        'name' => $user->name,
                        'status' => $existingMember->invitation_status,
                        'reason' => $statusText
                    ];
                    continue;
                }

                // 사용자에게 역할 할당
                $user->assignRole($roleName);

                // 동적 권한 서비스를 통한 기본 권한 할당
                app(DynamicPermissionService::class)->assignBasicPermissions($user, $roleName);

                // 멤버 초대 생성
                OrganizationMember::create([
                    'organization_id' => $organizationId,
                    'user_id' => $user->id,
                    'role_name' => $roleName,
                    'invitation_status' => 'pending',
                    'invited_at' => now()
                ]);

                $results['successful'][] = [
                    'email' => $email,
                    'name' => $user->name,
                    'role' => [
                        'name' => $roleName,
                        'label' => $this->getRoleDisplayInfo($roleName)['label']
                    ]
                ];
            }

            // 멤버 수 업데이트
            $organization->update([
                'members_count' => $organization->members()->count()
            ]);

            DB::commit();

            return $this->success([
                'total_processed' => count($invitations),
                'successful_count' => count($results['successful']),
                'failed_count' => count($results['failed']),
                'already_exists_count' => count($results['already_exists']),
                'results' => $results
            ], '초대 처리가 완료되었습니다.');

        } catch (\Exception $e) {
            DB::rollback();
            throw ApiException::internalError('초대 처리 중 오류가 발생했습니다.');
        }
    }

    /**
     * 역할 부여 가능 여부 확인
     */
    private function canGrantRole(User $currentUser, string $targetRole): bool
    {
        // 플랫폼 관리자만이 플랫폼 관리자 역할을 부여할 수 있음
        if ($targetRole === 'platform_admin') {
            return $currentUser->hasRole('platform_admin');
        }

        // 조직 소유자만이 조직 소유자 역할을 부여할 수 있음
        if ($targetRole === 'organization_owner') {
            return $currentUser->hasRole('organization_owner') || $currentUser->hasRole('platform_admin');
        }

        // 조직 관리자 이상은 조직 관리자까지 부여 가능
        if ($targetRole === 'organization_admin') {
            return $currentUser->hasAnyRole(['organization_admin', 'organization_owner', 'platform_admin']);
        }

        // 서비스 매니저 이상은 서비스 매니저까지 부여 가능
        if ($targetRole === 'service_manager') {
            return $currentUser->hasAnyRole(['service_manager', 'organization_admin', 'organization_owner', 'platform_admin']);
        }

        // 사용자 역할은 서비스 매니저 이상이 부여 가능
        if ($targetRole === 'user') {
            return $currentUser->hasAnyRole(['service_manager', 'organization_admin', 'organization_owner', 'platform_admin']);
        }

        return false;
    }


    /**
     * 역할별 표시 정보 반환
     */
    private function getRoleDisplayInfo($roleName)
    {
        return match($roleName) {
            'user' => ['label' => '사용자'],
            'service_manager' => ['label' => '서비스 매니저'],
            'organization_admin' => ['label' => '조직 관리자'],
            'organization_owner' => ['label' => '조직 소유자'],
            'platform_admin' => ['label' => '플랫폼 관리자'],
            default => ['label' => $roleName]
        };
    }
}
