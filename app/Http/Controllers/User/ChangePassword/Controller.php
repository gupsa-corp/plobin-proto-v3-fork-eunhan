<?php

namespace App\Http\Controllers\User\ChangePassword;

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
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                'confirmed'
            ],
        ], [
            'current_password.required' => '현재 비밀번호는 필수 입력 항목입니다.',
            'new_password.required' => '새 비밀번호는 필수 입력 항목입니다.',
            'new_password.min' => '새 비밀번호는 최소 8자 이상이어야 합니다.',
            'new_password.regex' => '새 비밀번호는 영문, 숫자, 특수문자를 포함해야 합니다.',
            'new_password.confirmed' => '새 비밀번호 확인이 일치하지 않습니다.',
        ]);

        if ($validator->fails()) {
            throw ApiException::validationError('입력 데이터에 오류가 있습니다.', $validator->errors()->toArray());
        }

        $user = $request->user() ?: auth()->user();

        if (!$user) {
            throw ApiException::unauthorized('인증된 사용자를 찾을 수 없습니다.');
        }

        // 현재 비밀번호 확인
        if (!Hash::check($request->input('current_password'), $user->password)) {
            throw ApiException::validationError('현재 비밀번호가 올바르지 않습니다.');
        }

        // 새 비밀번호가 현재 비밀번호와 같은지 확인
        if (Hash::check($request->input('new_password'), $user->password)) {
            throw ApiException::validationError('새 비밀번호는 현재 비밀번호와 달라야 합니다.');
        }

        // 비밀번호 업데이트
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return $this->success(null, '비밀번호가 성공적으로 변경되었습니다.');
    }
}
