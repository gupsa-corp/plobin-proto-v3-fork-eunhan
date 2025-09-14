<?php

namespace App\Services\Api\CheckDuplicate;

use App\Exceptions\ApiException;

class Service
{
    public function __invoke(string $modelClass, string $field, mixed $value, mixed $exceptId = null): void
    {
        $query = $modelClass::where($field, $value);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ApiException::conflict("이미 존재하는 {$field}입니다.");
        }
    }
}