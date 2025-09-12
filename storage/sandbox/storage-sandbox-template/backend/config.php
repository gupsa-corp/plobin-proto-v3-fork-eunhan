<?php

/**
 * 파일 저장소 설정
 */
return [
    // 업로드 설정
    'upload' => [
        'max_file_size' => 50 * 1024 * 1024, // 50MB
        'max_files_per_upload' => 20,
        'max_total_size' => 500 * 1024 * 1024, // 500MB
        'allowed_extensions' => [
            // 이미지
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg',
            // 문서
            'pdf', 'doc', 'docx', 'txt', 'rtf', 'odt',
            // 스프레드시트
            'xls', 'xlsx', 'csv', 'ods',
            // 프레젠테이션
            'ppt', 'pptx', 'odp',
            // 압축파일
            'zip', 'rar', '7z', 'tar', 'gz',
            // 미디어
            'mp4', 'avi', 'mov', 'wmv', 'flv', 'mp3', 'wav', 'flac',
            // 기타
            'json', 'xml', 'html', 'css', 'js', 'php', 'py', 'java', 'c', 'cpp'
        ]
    ],

    // 경로 설정
    'paths' => [
        'upload' => __DIR__ . '/uploads/',
        'download' => __DIR__ . '/../downloads/',
        'temp' => __DIR__ . '/temp/'
    ],

    // 보안 설정
    'security' => [
        'scan_uploads' => true, // 업로드된 파일 스캔 여부
        'quarantine_suspicious' => true, // 의심스러운 파일 격리
        'hash_verification' => true // 파일 해시 검증
    ],

    // 성능 설정
    'performance' => [
        'chunk_size' => 8192, // 파일 읽기 청크 크기
        'cache_metadata' => true, // 메타데이터 캐싱
        'compress_metadata' => false // 메타데이터 압축
    ],

    // 로그 설정
    'logging' => [
        'enable' => true,
        'log_uploads' => true,
        'log_downloads' => true,
        'log_deletions' => true,
        'log_file' => __DIR__ . '/logs/file_operations.log'
    ],

    // API 설정
    'api' => [
        'rate_limit' => [
            'uploads_per_hour' => 100,
            'downloads_per_hour' => 1000
        ],
        'cors' => [
            'allow_origins' => ['*'],
            'allow_methods' => ['GET', 'POST', 'DELETE', 'OPTIONS'],
            'allow_headers' => ['Content-Type', 'Authorization', 'X-Requested-With']
        ]
    ],

    // AI 서버 설정
    'ai_server' => [
        'summary_request_url' => 'http://seoul.gupsa.net:8080/api/ai/summary/request',
        'summary_retrieve_url' => 'http://seoul.gupsa.net:8080/api/ai/summary/retrieve',
        'file_upload_url' => 'http://seoul.gupsa.net:8080/api/sandboxes/files/{sandboxId}/uploads',
        'timeout' => 30, // 초
        'retry_attempts' => 3
    ]
];