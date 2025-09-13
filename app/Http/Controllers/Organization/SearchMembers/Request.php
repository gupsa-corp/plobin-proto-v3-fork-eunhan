<?php

namespace App\Http\Controllers\Organization\SearchMembers;

use App\Http\Controllers\Api\Core\Request as ApiRequest;

class Request extends ApiRequest
{
    public function rules(): array
    {
        return [
            'query' => 'required|string|min:2|max:100',
            'limit' => 'sometimes|integer|min:1|max:20'
        ];
    }

    public function messages(): array
    {
        return [
            'query.required' => '검색어를 입력해주세요.',
            'query.string' => '검색어는 문자열이어야 합니다.',
            'query.min' => '검색어는 2글자 이상 입력해주세요.',
            'query.max' => '검색어는 100글자를 초과할 수 없습니다.',
            'limit.integer' => '제한 수는 숫자여야 합니다.',
            'limit.min' => '제한 수는 1 이상이어야 합니다.',
            'limit.max' => '제한 수는 20을 초과할 수 없습니다.'
        ];
    }
}
