<?php

namespace App\Http\Controllers\User\LoginPlobin;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Controller extends ApiController
{
    public function __invoke(Request $request)
    {
        // 이메일 정규화
        $email = strtolower(trim($request->email));

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw \App\Exceptions\ApiException::unauthorized('이메일 또는 비밀번호가 올바르지 않습니다.');
        }

        // 기존 토큰들 삭제하고 새 토큰 생성
        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'user' => [
                'id' => $user->id,
                'nickname' => $user->nickname ?: ($user->first_name . ' ' . $user->last_name) ?: $user->email,
                'email' => $user->email,
            ],
            'token' => $token,
            'redirect_url' => '/dashboard'
        ], '로그인이 완료되었습니다.');
    }
}
