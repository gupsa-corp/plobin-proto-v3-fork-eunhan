<?php

namespace App\Services\View\FindViewDirectory;

use RuntimeException;

class Service
{
    public function __invoke(): string
    {
        $request = request();
        $path = '/' . ltrim($request->path(), '/');

        $routes = config('routes-web');

        // 정확한 매칭 먼저 시도
        if (isset($routes[$path])) {
            $config = $routes[$path];

            // 새로운 배열 구조 지원
            if (is_array($config)) {
                return $config['view'];
            }

            // 이전 문자열 구조도 지원 (하위 호환성)
            if (is_string($config)) {
                return $config;
            }
        }

        // 매개변수가 있는 라우트 패턴 매칭
        foreach ($routes as $routePattern => $config) {
            if (str_contains($routePattern, '{')) {
                // 라우트 패턴을 정규식으로 변환
                $pattern = preg_replace('/\{[^}]+\}/', '[^/]+', $routePattern);
                $pattern = '#^' . str_replace('/', '\/', $pattern) . '$#';

                if (preg_match($pattern, $path)) {
                    // 새로운 배열 구조 지원
                    if (is_array($config)) {
                        return $config['view'];
                    }

                    // 이전 문자열 구조도 지원 (하위 호환성)
                    if (is_string($config)) {
                        return $config;
                    }
                }
            }
        }

        throw new RuntimeException("Unable to determine view directory for path: {$path}");
    }
}