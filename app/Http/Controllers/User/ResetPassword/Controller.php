<?php

namespace App\Http\Controllers\User\ResetPassword;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class Controller extends ApiController
{
    public function __invoke(Request $request)
    {
        // 입력값 검증
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'token.required' => '재설정 토큰이 필요합니다.',
            'email.required' => '이메일 주소를 입력해주세요.',
            'email.email' => '올바른 이메일 주소 형식을 입력해주세요.',
            'email.max' => '이메일 주소가 너무 깁니다.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.confirmed' => '비밀번호가 일치하지 않습니다.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // 이메일 정규화
        $email = strtolower(trim($request->email));

        // Laravel의 내장 비밀번호 재설정 기능 사용
        $status = Password::reset([
            'email' => $email,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
            'token' => $request->token,
        ], function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return $this->success(null, '비밀번호가 성공적으로 재설정되었습니다.');
        }

        // 실패 상태에 따른 에러 메시지
        $errorMessage = match($status) {
            Password::INVALID_USER => '입력하신 이메일 주소로 등록된 계정을 찾을 수 없습니다.',
            Password::INVALID_TOKEN => '재설정 링크가 유효하지 않거나 만료되었습니다.',
            default => '비밀번호 재설정에 실패했습니다. 다시 시도해주세요.',
        };

        throw \App\Exceptions\ApiException::badRequest($errorMessage);
    }
}
