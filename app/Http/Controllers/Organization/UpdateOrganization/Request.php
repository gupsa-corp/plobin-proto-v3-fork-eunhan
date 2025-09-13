<?php

namespace App\Http\Controllers\Organization\UpdateOrganization;

use App\Http\Controllers\Api\Core\Request as ApiRequest;
use Illuminate\Validation\Rule;

class Request extends ApiRequest
{
    public function rules(): array
    {
        $organizationId = $this->route('organization');

        return [
            'name' => [
                'required',
                'string',
                'max:25',
                'min:1'
            ],
            'url' => [
                'required',
                'string',
                'min:3',
                'max:12',
                'regex:/^[a-zA-Z]+$/', // 영문자만 허용
                Rule::unique('organizations', 'url')->ignore($organizationId) // 자기 자신 제외하고 중복 체크
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '조직명을 입력해주세요.',
            'name.max' => '조직명은 최대 25자까지 입력 가능합니다.',
            'name.min' => '조직명을 입력해주세요.',
            'url.required' => '조직 URL을 입력해주세요.',
            'url.min' => '조직 URL은 최소 3자 이상 입력해주세요.',
            'url.max' => '조직 URL은 최대 12자까지 입력 가능합니다.',
            'url.regex' => '조직 URL은 영문자만 입력 가능합니다.',
            'url.unique' => '이미 사용중인 조직 URL입니다.'
        ];
    }
}
