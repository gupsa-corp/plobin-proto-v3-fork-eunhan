<?php

namespace App\Services\View\GetCurrentViewPath;

class Service
{
    public function __invoke(): string
    {
        $folder = app(\App\Services\View\FindViewDirectory\Service::class)();

        // 새로운 형식: 100-page-landing.101-page-landing-home.000-index -> 100-page-landing.101-page-landing-home.200-content-main
        if (preg_match('/^(.+)\.000-index$/', $folder, $matches)) {
            return $matches[1] . '.200-content-main';
        }

        // 이전 형식 지원
        return $folder . '.body';
    }
}