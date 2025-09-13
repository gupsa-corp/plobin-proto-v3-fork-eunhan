<?php

namespace App\Http\Controllers\User\SignupPlobin;

use App\Http\Controllers\Api\Core\Request as ApiRequest;
use App\Services\PhoneNumberHelper;

class Request extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules(),
            'password' => $this->passwordRules(),
            'password_confirmation' => [
                'required',
                'string',
                'same:password'
            ],
            'country_code' => 'nullable|string|max:10|regex:/^\+\d{1,4}$/',
            'phone_number' => 'nullable|string|max:20',
            'nickname' => 'nullable|string|min:2|max:20|unique:users,nickname|regex:/^[가-힣a-zA-Z0-9_-]+$/',
            'first_name' => 'nullable|string|max:50|regex:/^[가-힣a-zA-Z\s]+$/',
            'last_name' => 'nullable|string|max:50|regex:/^[가-힣a-zA-Z\s]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '이름을 입력해주세요.',
            'name.regex' => '이름은 한글, 영문만 입력 가능합니다.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식을 입력해주세요.',
            'email.regex' => '유효한 이메일 형식을 입력해주세요.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.min' => '비밀번호는 최소 8자 이상이어야 합니다.',
            'password.regex' => '비밀번호는 소문자, 숫자, 특수문자를 포함해야 합니다.',
            'password_confirmation.required' => '비밀번호 확인을 입력해주세요.',
            'password_confirmation.same' => '비밀번호가 일치하지 않습니다.',
            'country_code.regex' => '올바른 국가번호 형식을 입력해주세요. (예: +82)',
            'phone_number.max' => '전화번호는 20자 이하로 입력해주세요.',
            'nickname.min' => '닉네임은 2글자 이상 입력해주세요.',
            'nickname.max' => '닉네임은 20글자 이하로 입력해주세요.',
            'nickname.unique' => '이미 사용중인 닉네임입니다.',
            'nickname.regex' => '닉네임은 한글, 영문, 숫자, _, - 만 사용 가능합니다.',
            'first_name.regex' => '성은 한글, 영문만 입력 가능합니다.',
            'last_name.regex' => '이름은 한글, 영문만 입력 가능합니다.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 전화번호와 국가번호가 함께 제공된 경우 검증
            if ($this->phone_number && $this->country_code) {
                if (!PhoneNumberHelper::isValid($this->phone_number, $this->country_code)) {
                    $validator->errors()->add('phone_number', '올바른 전화번호 형식을 입력해주세요.');
                }
            }
        });
    }
}
