<?php

namespace App\Http\Controllers\Api\Sandbox\LoadScreenContent;

use Illuminate\Foundation\Http\FormRequest;

class Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'domain' => 'required|string',
            'screen' => 'required|string',
            'sandbox' => 'sometimes|string'
        ];
    }

    public function messages(): array
    {
        return [
            'domain.required' => '도메인 정보가 필요합니다',
            'screen.required' => '화면 정보가 필요합니다',
        ];
    }
}