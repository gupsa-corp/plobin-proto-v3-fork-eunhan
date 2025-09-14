<?php

namespace App\Services\Api\SafeTransaction;

use App\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;
use Exception;

class Service
{
    public function __invoke(callable $callback): mixed
    {
        try {
            return DB::transaction($callback);
        } catch (Exception $e) {
            throw ApiException::serverError('작업 처리 중 오류가 발생했습니다.');
        }
    }
}