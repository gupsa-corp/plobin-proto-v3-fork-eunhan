<?php

namespace App\Http\Controllers\UserAccount\Delete;

use App\Http\Controllers\Controller as BaseController;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Controller extends BaseController
{
    /**
     * 회원 탈퇴 페이지 표시
     */
    public function show()
    {
        $user = Auth::user();

        // 사용자가 소속된 조직들 조회
        $organizations = Organization::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['members' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])->get();

        // 소유하고 있는 조직들 찾기
        $ownedOrganizations = $organizations->filter(function ($org) use ($user) {
            $member = $org->members->first();
            return $member && $member->role_name === 'organization_owner';
        });

        return view('300-page-service.305-page-mypage-delete.000-index', [
            'user' => $user,
            'organizations' => $organizations,
            'ownedOrganizations' => $ownedOrganizations,
            'canDelete' => $ownedOrganizations->isEmpty() // 소유한 조직이 없으면 탈퇴 가능
        ]);
    }

    /**
     * 회원 탈퇴 처리
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        // 유효성 검사
        $request->validate([
            'reason' => 'required|string|in:not-using,privacy,functionality,alternative,other',
            'other_reason' => 'required_if:reason,other|string|max:1000',
            'password' => 'required|string',
            'confirmation' => 'required|string|in:계정삭제'
        ], [
            'reason.required' => '탈퇴 사유를 선택해주세요.',
            'other_reason.required_if' => '기타 사유를 입력해주세요.',
            'password.required' => '비밀번호를 입력해주세요.',
            'confirmation.in' => '확인 텍스트가 올바르지 않습니다.'
        ]);

        // 비밀번호 확인
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => '비밀번호가 올바르지 않습니다.'
            ]);
        }

        // 소유하고 있는 조직이 있는지 다시 확인
        $ownedOrganizations = Organization::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('role_name', 'organization_owner');
        })->get();

        if ($ownedOrganizations->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => '소유하고 있는 조직이 있습니다. 조직을 먼저 삭제하거나 다른 사용자에게 양도한 후 탈퇴해주세요.',
                'organizations' => $ownedOrganizations->pluck('name')->toArray()
            ], 400);
        }

        try {
            // 조직 멤버십 제거 (소유자가 아닌 멤버십만)
            $user->organizationMembers()
                ->where('role_name', '!=', 'organization_owner')
                ->delete();

            // 사용자 계정 소프트 삭제 또는 완전 삭제
            // 먼저 소프트 삭제를 시도하고, 필요시 완전 삭제도 가능
            $user->delete();

            // 세션 무효화
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => '회원 탈퇴가 완료되었습니다. 그동안 서비스를 이용해주셔서 감사합니다.',
                'redirect' => '/'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '회원 탈퇴 처리 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.'
            ], 500);
        }
    }

    /**
     * 조직 소속 상태 확인 API
     */
    public function checkOrganizationStatus()
    {
        $user = Auth::user();

        $organizations = Organization::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['members' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])->get();

        $ownedOrganizations = $organizations->filter(function ($org) use ($user) {
            $member = $org->members->first();
            return $member && $member->role_name === 'organization_owner';
        });

        return response()->json([
            'canDelete' => $ownedOrganizations->isEmpty(),
            'totalOrganizations' => $organizations->count(),
            'ownedOrganizations' => $ownedOrganizations->map(function ($org) {
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'members_count' => $org->members()->count()
                ];
            })->toArray(),
            'memberOrganizations' => $organizations->filter(function ($org) use ($user) {
                $member = $org->members->first();
                return $member && $member->role_name !== 'organization_owner';
            })->map(function ($org) {
                $member = $org->members->first();
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'role' => $member->role_name ?? 'member'
                ];
            })->values()->toArray()
        ]);
    }
}
