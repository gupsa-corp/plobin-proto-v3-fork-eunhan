<?php

namespace App\Http\Controllers\User\LogoutPlobin;

use App\Http\Controllers\Api\Core\Controller as ApiController;

class Controller extends ApiController
{
    public function __invoke(Request $request)
    {
        // 사용자는 SimpleAuth 미들웨어에서 이미 검증됨
        $user = $request->user();

        if ($user) {
            // API 토큰 로그아웃: 현재 토큰 삭제
            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }
            // 웹 세션 로그아웃
            auth()->logout();
        }

        return $this->success(null, '로그아웃이 완료되었습니다.');
    }
}
