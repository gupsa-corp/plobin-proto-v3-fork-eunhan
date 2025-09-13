<?php

namespace App\Http\Controllers\User\VerifyPassword;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Controller extends ApiController
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ], [
            'password.required' => '비밀번호는 필수 입력 항목입니다.',
        ]);

        if ($validator->fails()) {
            throw ApiException::validationError('입력 데이터에 오류가 있습니다.', $validator->errors()->toArray());
        }

        $user = $request->user() ?: auth()->user();

        if (!$user) {
            throw ApiException::unauthorized('인증된 사용자를 찾을 수 없습니다.');
        }

        // 비밀번호 확인
        if (!Hash::check($request->input('password'), $user->password)) {
            throw ApiException::validationError('비밀번호가 일치하지 않습니다.');
        }

        return $this->success(null, '비밀번호가 확인되었습니다.');
    }
}
