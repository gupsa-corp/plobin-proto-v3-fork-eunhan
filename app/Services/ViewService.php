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

    /**
     * 공통 뷰 디렉터리 경로 생성
     */
    public function getCommonPath(): string
    {
        $folder = $this->findViewDirectory();
        
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

    /**
     * 현재 뷰 경로 반환
     */
    public function getCurrentViewPath(): string
    {
        $folder = $this->findViewDirectory();
        
        // 새로운 형식: 100-page-landing.101-page-landing-home.000-index -> 100-page-landing.101-page-landing-home.200-content-main
        if (preg_match('/^(.+)\.000-index$/', $folder, $matches)) {
            return $matches[1] . '.200-content-main';
        }
        
        // 이전 형식 지원
        return $folder . '.body';
    }
}