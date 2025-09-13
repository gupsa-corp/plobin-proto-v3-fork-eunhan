<?php

namespace App\Http\Controllers\User\GetProfile;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Exceptions\ApiException;

class Controller extends ApiController
{
    public function __invoke(\Illuminate\Http\Request $request)
    {
        $user = $request->user() ?: auth()->user();

        // 개발용 - 인증된 사용자가 없으면 테스트 사용자 사용
        if (!$user) {
            $user = \App\Models\User::where('email', 'test@example.com')->first();
        }

        if (!$user) {
            throw ApiException::unauthorized('인증된 사용자를 찾을 수 없습니다.');
        }

        return $this->success([
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'nickname' => $user->nickname,
            'full_name' => $user->full_name,
            'phone_number' => $user->phone_number,
            'country_code' => $user->country_code,
            'formatted_phone' => $user->formatted_phone,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ], '프로필 정보를 성공적으로 조회했습니다.');
    }
}
