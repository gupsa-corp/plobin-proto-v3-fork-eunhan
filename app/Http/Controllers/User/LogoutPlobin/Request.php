<?php

namespace App\Http\Controllers\User\LogoutPlobin;

use App\Http\Controllers\Api\Core\Request as ApiRequest;

class Request extends ApiRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
        ];
    }
}
