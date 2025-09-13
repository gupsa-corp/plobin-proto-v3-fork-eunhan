<?php

namespace App\Http\Controllers\User\UpdateProfile;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Controller extends ApiController
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:5',
        ], [
            'first_name.string' => '이름은 문자열이어야 합니다.',
            'first_name.max' => '이름은 255자를 초과할 수 없습니다.',
            'last_name.string' => '성은 문자열이어야 합니다.',
            'last_name.max' => '성은 255자를 초과할 수 없습니다.',
            'nickname.string' => '닉네임은 문자열이어야 합니다.',
            'nickname.max' => '닉네임은 255자를 초과할 수 없습니다.',
            'phone_number.string' => '전화번호는 문자열이어야 합니다.',
            'phone_number.max' => '전화번호는 20자를 초과할 수 없습니다.',
            'country_code.string' => '국가코드는 문자열이어야 합니다.',
            'country_code.max' => '국가코드는 5자를 초과할 수 없습니다.',
        ]);

        if ($validator->fails()) {
            throw ApiException::validationError('입력 데이터에 오류가 있습니다.', $validator->errors()->toArray());
        }

        $user = $request->user() ?: auth()->user();

        if (!$user) {
            throw ApiException::unauthorized('인증된 사용자를 찾을 수 없습니다.');
        }

        // 사용자 정보 업데이트
        if ($request->has('first_name')) {
            $user->first_name = $request->input('first_name');
        }
        if ($request->has('last_name')) {
            $user->last_name = $request->input('last_name');
        }
        if ($request->has('nickname')) {
            $user->nickname = $request->input('nickname');
        }
        if ($request->has('phone_number')) {
            $user->phone_number = $request->input('phone_number');
        }
        if ($request->has('country_code')) {
            $user->country_code = $request->input('country_code');
        }

        $user->save();

        return $this->updated([
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'nickname' => $user->nickname,
            'full_name' => $user->full_name,
            'phone_number' => $user->phone_number,
            'country_code' => $user->country_code,
            'formatted_phone' => $user->formatted_phone,
        ], '프로필이 성공적으로 업데이트되었습니다.');
    }
}
