<?php

namespace App\Http\Controllers\ProjectSandbox\Manage;

use Illuminate\Foundation\Http\FormRequest;

class Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeAction = $this->route()->getActionMethod();

        return match ($routeAction) {
            'create' => [
                'name' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/|max:50',
                'description' => 'nullable|string|max:255',
                'type' => 'required|in:development,testing,staging,demo'
            ],
            'delete', 'toggleStatus' => [
                'sandbox_id' => 'required|exists:project_sandboxes,id',
            ],
            default => []
        };
    }

    public function messages(): array
    {
        return [
            'name.required' => '샌드박스 이름을 입력해주세요.',
            'name.regex' => '영문자, 숫자, 하이픈(-), 언더스코어(_)만 사용 가능합니다.',
            'name.max' => '샌드박스 이름은 50자를 초과할 수 없습니다.',
            'type.required' => '샌드박스 타입을 선택해주세요.',
            'type.in' => '유효하지 않은 샌드박스 타입입니다.',
            'sandbox_id.required' => '샌드박스 ID가 필요합니다.',
            'sandbox_id.exists' => '존재하지 않는 샌드박스입니다.',
        ];
    }
}