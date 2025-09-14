<?php

namespace App\Services\View\GetCommonPath;

use RuntimeException;

class Service
{
    public function __invoke(): string
    {
        $folder = app(\App\Services\View\FindViewDirectory\Service::class)();

        // 900-page-platform-admin 형식 지원
        if (preg_match('/^(900-page-platform-admin)\./', $folder)) {
            return '900-page-platform-admin.900-common';
        }

        // 800-page-organization-admin 형식 지원
        if (preg_match('/^(800-page-organization-admin)\./', $folder)) {
            return '800-page-organization-admin.800-common';
        }

        // 700-page-sandbox 형식 지원
        if (preg_match('/^(700-page-sandbox)\./', $folder)) {
            return '700-page-sandbox.700-common';
        }

        // 새로운 형식: 100-page-landing.101-page-landing-home.000-index
        if (preg_match('/^(\d)00-([^\.]+)\./', $folder, $matches)) {
            return $matches[1] . '00-' . $matches[2] . '.' . $matches[1] . '00-common';
        }

        // 이전 형식: 101-landing-home
        if (preg_match('/^(\d)(\d{2})-([^-]+)/', $folder, $matches)) {
            return $matches[1] . '00-' . $matches[3] . '-common';
        }

        throw new RuntimeException("Unable to parse view directory pattern: {$folder}");
    }
}