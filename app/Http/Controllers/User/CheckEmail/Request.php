<?php

namespace App\Http\Controllers\User\CheckEmail;

use App\Http\Controllers\Api\Core\Request as ApiRequest;

class Request extends ApiRequest
{
    public function rules(): array
    {
        return [
            'email' => $this->emailRules()
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식을 입력해주세요.',
            'email.regex' => '유효한 이메일 형식을 입력해주세요.'
        ];
    }
}
