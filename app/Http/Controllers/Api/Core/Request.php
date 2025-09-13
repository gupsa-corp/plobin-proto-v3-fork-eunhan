<?php

namespace App\Http\Controllers\Api\Core;

use App\Exceptions\ApiException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

abstract class Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ApiException::validation(
            "입력값 검증에 실패했습니다.",
            $validator->errors()->toArray()
        );
    }

    protected function emailRules(): array
    {
        return [
            "required",
            "string",
            "email:rfc",
            "max:255",
            "min:5"
        ];
    }

    protected function passwordRules(): array
    {
        return [
            "required",
            "string",
            "min:8",
            "max:100",
            "regex:/^(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[a-z\d@$!%*?&]/"
        ];
    }

    protected function nameRules(): array
    {
        return [
            "required",
            "string",
            "min:2",
            "max:50",
            "regex:/^[가-힣a-zA-Z\s]+$/"
        ];
    }

    protected function phoneRules(): array
    {
        return [
            "required",
            "string",
            "regex:/^01[016789]-?[0-9]{3,4}-?[0-9]{4}$/"
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->sanitizeInputs();
    }

    private function sanitizeInputs(): void
    {
        $inputs = [];

        foreach ($this->all() as $key => $value) {
            if (is_string($value)) {
                $inputs[$key] = trim($value);
            }
        }

        if (!empty($inputs)) {
            $this->merge($inputs);
        }
    }
}
