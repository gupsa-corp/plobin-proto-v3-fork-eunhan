<?php

namespace App\Http\Controllers\Organization\CreateOrganization;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\Organization;
use App\Models\OrganizationMember;
use Illuminate\Http\Request;

class Controller extends ApiController
{
    public function __invoke(Request $request)
    {
        // 간단한 토큰 인증 로직
        if ($request->hasHeader('Authorization')) {
            $token = str_replace('Bearer ', '', $request->header('Authorization'));
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable) {
                auth()->setUser($accessToken->tokenable);
            }
        }

        \Log::info('CreateOrganization Controller called', [
            'request_data' => $request->all(),
            'user_id' => auth()->id(),
            'user_exists' => auth()->check()
        ]);

        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => '인증이 필요합니다.'], 401);
        }

        try {
            // 유효성 검사
            $validated = $request->validate([
                'name' => 'required|string|min:1|max:25',
            ], [
                'name.required' => '조직 이름을 입력해주세요.',
                'name.min' => '조직 이름은 1자 이상이어야 합니다.',
                'name.max' => '조직 이름은 25자 이하여야 합니다.',
            ]);

            \Log::info('Validation passed', ['validated_data' => $validated]);

            $organization = Organization::create([
                'name' => $validated['name'],
                'user_id' => auth()->id()
            ]);

            \Log::info('Organization created', ['organization_id' => $organization->id]);

            // 생성자를 조직의 소유자로 추가
            OrganizationMember::create([
                'user_id' => auth()->id(),
                'organization_id' => $organization->id,
                'role_name' => 'owner',
                'invitation_status' => 'accepted',
                'joined_at' => now()
            ]);

            \Log::info('Organization owner added', ['user_id' => auth()->id(), 'organization_id' => $organization->id]);

            return $this->created([
                'id' => $organization->id,
                'name' => $organization->name,
            ], '조직이 생성되었습니다.');
        } catch (\Exception $e) {
            \Log::error('CreateOrganization error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
