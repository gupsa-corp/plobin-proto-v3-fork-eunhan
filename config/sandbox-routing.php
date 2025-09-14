<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 샌드박스 라우팅 설정
    |--------------------------------------------------------------------------
    |
    | 샌드박스 컨테이너의 동적 라우팅 설정
    |
    */

    /**
     * 샌드박스 기본 경로 설정
     */
    'base_path' => env('SANDBOX_BASE_PATH', 'sandbox/container'),
    
    /**
     * 기본 템플릿 폴더명
     */
    'default_template' => env('SANDBOX_DEFAULT_TEMPLATE', 'storage-sandbox-template'),
    
    /**
     * 라우팅 패턴 설정
     */
    'patterns' => [
        // 도메인 폴더 패턴: {숫자}-domain-{이름}
        'domain' => '/^(\d+)-domain-(.+)$/',
        
        // 화면 폴더 패턴: {숫자}-screen-{이름}  
        'screen' => '/^(\d+)-screen-(.+)$/',
        
        // 뷰 파일명
        'view_file' => 'index.blade.php',
    ],
    
    /**
     * 라우트 생성 규칙
     */
    'route_generation' => [
        // 라우트 경로 패턴
        'path_pattern' => '/sandbox/{sandbox}/{domain}/{screen}',
        
        // 라우트 이름 패턴
        'name_pattern' => 'sandbox.{sandbox}.{domain}.{screen}',
        
        // 뷰 경로 패턴
        'view_pattern' => 'sandbox.container.{sandbox}.{domain}.{screen}',
    ],
    
    /**
     * 캐시 설정
     */
    'cache' => [
        // 캐시 활성화
        'enabled' => env('SANDBOX_CACHE_ENABLED', true),
        
        // 캐시 TTL (초)
        'ttl' => env('SANDBOX_CACHE_TTL', 300),
        
        // 캐시 키 프리픽스
        'prefix' => 'sandbox_routing',
    ],
    
    /**
     * 로깅 설정
     */
    'logging' => [
        // 로깅 활성화
        'enabled' => env('SANDBOX_LOGGING_ENABLED', true),
        
        // 로그 채널
        'channel' => env('SANDBOX_LOG_CHANNEL', 'single'),
        
        // 디버그 모드에서 상세 로그
        'debug_mode' => env('APP_DEBUG', false),
    ],
    
    /**
     * 보안 설정
     */
    'security' => [
        // 허용되는 샌드박스 이름 패턴
        'allowed_sandbox_pattern' => '/^[a-zA-Z0-9\-_]+$/',
        
        // 허용되는 도메인 이름 패턴
        'allowed_domain_pattern' => '/^\d+-domain-[a-zA-Z0-9\-_]+$/',
        
        // 허용되는 화면 이름 패턴
        'allowed_screen_pattern' => '/^\d+-screen-[a-zA-Z0-9\-_]+$/',
        
        // 접근 제한 IP (옵션)
        'ip_whitelist' => env('SANDBOX_IP_WHITELIST', null),
        
        // 디렉토리 트래버설 방지
        'prevent_directory_traversal' => true,
    ],
    
    /**
     * 성능 최적화 설정
     */
    'performance' => [
        // 파일 시스템 스캔 최적화
        'scan_optimization' => true,
        
        // 병렬 디렉토리 스캔
        'parallel_scan' => false,
        
        // 최대 스캔 깊이
        'max_scan_depth' => 3,
        
        // 스캔 결과 최대 개수
        'max_results' => 1000,
    ],
    
    /**
     * 개발/디버깅 설정
     */
    'development' => [
        // 개발 모드 활성화
        'enabled' => env('APP_DEBUG', false),
        
        // 라우트 정보 표시
        'show_route_info' => env('SANDBOX_SHOW_ROUTE_INFO', false),
        
        // 디버깅 헤더 추가
        'debug_headers' => env('SANDBOX_DEBUG_HEADERS', false),
        
        // 자동 캐시 새로고침
        'auto_cache_refresh' => env('SANDBOX_AUTO_CACHE_REFRESH', false),
    ],
    
    /**
     * 에러 처리 설정
     */
    'error_handling' => [
        // 사용자 정의 404 페이지
        'custom_404_view' => 'sandbox.errors.not-found',
        
        // 사용자 정의 500 페이지
        'custom_500_view' => 'sandbox.errors.server-error',
        
        // 에러 시 대체 뷰
        'fallback_view' => null,
        
        // 에러 알림 활성화
        'notify_on_error' => false,
    ],
    
    /**
     * API 설정
     */
    'api' => [
        // API 활성화
        'enabled' => true,
        
        // API 라우트 프리픽스
        'prefix' => 'api/sandbox',
        
        // API 미들웨어
        'middleware' => ['api'],
        
        // 인증 필요 여부
        'require_auth' => false,
    ],
    
    /**
     * 뷰 설정
     */
    'views' => [
        // 기본 레이아웃
        'default_layout' => 'layouts.sandbox',
        
        // 뷰 네임스페이스
        'namespace' => 'sandbox',
        
        // 뷰 컴파일 캐시
        'compile_cache' => true,
        
        // 뷰 공유 데이터
        'shared_data' => [
            'app_name' => env('APP_NAME', 'Laravel'),
            'app_version' => '1.0.0',
        ],
    ],
];