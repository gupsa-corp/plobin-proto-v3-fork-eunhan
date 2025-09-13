<?php

namespace App\Http\Controllers\User\ValidatePhone;

use App\Http\Controllers\Api\Core\Request as ApiRequest;

class Request extends ApiRequest
{
    public function rules(): array
    {
        return [
            'phone_number' => 'required|string|max:20',
            'country_code' => 'required|string|max:10|regex:/^\+\d{1,4}$/'
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.required' => '전화번호를 입력해주세요.',
            'phone_number.string' => '전화번호는 문자열이어야 합니다.',
            'phone_number.max' => '전화번호는 20자 이하로 입력해주세요.',
            'country_code.required' => '국가번호를 입력해주세요.',
            'country_code.string' => '국가번호는 문자열이어야 합니다.',
            'country_code.max' => '국가번호는 10자 이하로 입력해주세요.',
            'country_code.regex' => '올바른 국가번호 형식을 입력해주세요. (예: +82)'
        ];
    }
}
