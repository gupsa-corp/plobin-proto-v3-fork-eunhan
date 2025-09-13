<?php

namespace App\Http\Controllers\Organization\InviteMembers;

use App\Http\Controllers\Api\Core\Request as ApiRequest;

class Request extends ApiRequest
{
    public function rules(): array
    {
        return [
            'invitations' => 'required|array|min:1|max:50',
            'invitations.*.email' => 'required|email|distinct',
            'invitations.*.role' => 'required|string|exists:roles,name',
            'invitations.*.message' => 'sometimes|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'invitations.required' => '초대할 멤버 정보를 입력해주세요.',
            'invitations.array' => '초대 정보는 배열 형태여야 합니다.',
            'invitations.min' => '최소 1명 이상 초대해야 합니다.',
            'invitations.max' => '한 번에 최대 50명까지만 초대할 수 있습니다.',
            'invitations.*.email.required' => '이메일을 입력해주세요.',
            'invitations.*.email.email' => '올바른 이메일 형식을 입력해주세요.',
            'invitations.*.email.distinct' => '중복된 이메일이 있습니다.',
            'invitations.*.role.required' => '역할을 선택해주세요.',
            'invitations.*.role.string' => '역할은 문자열이어야 합니다.',
            'invitations.*.role.exists' => '유효하지 않은 역할입니다.',
            'invitations.*.message.string' => '메시지는 문자열이어야 합니다.',
            'invitations.*.message.max' => '메시지는 500자를 초과할 수 없습니다.'
        ];
    }
}
