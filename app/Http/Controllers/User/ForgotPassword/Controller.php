<?php

namespace App\Http\Controllers\User\ForgotPassword;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Controller extends ApiController
{
    public function __invoke(Request $request)
    {
        // 입력값 검증
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ], [
            'email.required' => '이메일 주소를 입력해주세요.',
            'email.email' => '올바른 이메일 주소 형식을 입력해주세요.',
            'email.max' => '이메일 주소가 너무 깁니다.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // 이메일 정규화
        $email = strtolower(trim($request->email));

        // 사용자 존재 여부 확인
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user) {
            // 보안상 사용자 존재 여부를 명시하지 않고 성공 응답
            return $this->success(null, '비밀번호 재설정 링크를 이메일로 전송했습니다. 이메일을 확인해 주세요.');
        }

        // Laravel의 내장 비밀번호 재설정 기능 사용
        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success(null, '비밀번호 재설정 링크를 이메일로 전송했습니다. 이메일을 확인해 주세요.');
        }

        // 실패한 경우에도 보안상 성공 응답 (이메일 존재 여부 노출 방지)
        return $this->success(null, '비밀번호 재설정 링크를 이메일로 전송했습니다. 이메일을 확인해 주세요.');
    }
}
