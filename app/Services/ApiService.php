<?php

namespace App\Services;

use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

abstract class ApiService
{
    /**
     * 안전한 트랜잭션 실행
     */
    protected function safeTransaction(callable $callback): mixed
    {
        return app(\App\Services\Api\SafeTransaction\Service::class)($callback);
    }

    /**
     * 모델 존재 여부 확인
     */
    protected function findOrFail(string $modelClass, mixed $id): Model
    {
        return app(\App\Services\Api\FindOrFail\Service::class)($modelClass, $id);
    }

    /**
     * 중복 체크
     */
    protected function checkDuplicate(string $modelClass, string $field, mixed $value, mixed $exceptId = null): void
    {
        app(\App\Services\Api\CheckDuplicate\Service::class)($modelClass, $field, $value, $exceptId);
    }

    /**
     * 페이지네이션 데이터 변환
     */
    protected function formatPagination($paginator): array
    {
        return app(\App\Services\Api\FormatPagination\Service::class)($paginator);
    }
}