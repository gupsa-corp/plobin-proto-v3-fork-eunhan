<?php

namespace App\Services;

use RuntimeException;

class ViewService
{
    /**
     * 현재 요청 경로에 따른 뷰 디렉터리 찾기
     */
    public function findViewDirectory(): string
    {
        return app(\App\Services\View\FindViewDirectory\Service::class)();
    }

    /**
     * 공통 뷰 디렉터리 경로 생성
     */
    public function getCommonPath(): string
    {
        return app(\App\Services\View\GetCommonPath\Service::class)();
    }

    /**
     * 현재 뷰 경로 반환
     */
    public function getCurrentViewPath(): string
    {
        return app(\App\Services\View\GetCurrentViewPath\Service::class)();
    }
}