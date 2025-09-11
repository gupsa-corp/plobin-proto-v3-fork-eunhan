<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SOLAPI Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains settings for SOLAPI SMS service
    | integration. You need to obtain API credentials from SOLAPI console.
    |
    */

    'api_key' => env('SOLAPI_API_KEY'),
    'api_secret' => env('SOLAPI_API_SECRET'),
    
    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    */
    
    'base_url' => env('SOLAPI_BASE_URL', 'https://api.coolsms.co.kr'),
    'version' => env('SOLAPI_VERSION', 'v4'),
    
    /*
    |--------------------------------------------------------------------------
    | SMS Settings
    |--------------------------------------------------------------------------
    */
    
    'from' => env('SOLAPI_FROM_NUMBER'),
    'app_name' => env('APP_NAME', 'Plobin'),
    
    /*
    |--------------------------------------------------------------------------
    | Force Real SMS in Development
    |--------------------------------------------------------------------------
    | 
    | 개발환경에서도 실제 SMS를 전송하려면 true로 설정
    |
    */
    
    'force_real_sms' => env('SOLAPI_FORCE_REAL_SMS', false),
    
    /*
    |--------------------------------------------------------------------------
    | Verification Settings
    |--------------------------------------------------------------------------
    */
    
    'verification' => [
        'code_length' => 6,
        'expire_minutes' => 5,
        'max_attempts' => 3,
        'resend_cooldown_seconds' => 60,
        'template' => '[{app_name}] 인증번호는 {code}입니다. 5분 이내에 입력해주세요.',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    
    'rate_limit' => [
        'per_phone_daily' => 10,
        'per_ip_hourly' => 20,
    ],
];