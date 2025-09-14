<?php

namespace App\Services\Api\FindOrFail;

use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Model;

class Service
{
    public function __invoke(string $modelClass, mixed $id): Model
    {
        $model = $modelClass::find($id);

        if (!$model) {
            throw ApiException::notFound('요청한 리소스를 찾을 수 없습니다.');
        }

        return $model;
    }
}