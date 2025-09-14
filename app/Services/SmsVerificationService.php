<?php

namespace App\Services;

use App\Models\SmsVerification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SmsVerificationService
{
    private string $apiKey;
    private string $apiSecret;
    private string $baseUrl;
    private string $version;
    private string $fromNumber;

    public function __construct()
    {
        $this->apiKey = config('solapi.api_key');
        $this->apiSecret = config('solapi.api_secret');
        $this->baseUrl = config('solapi.base_url');
        $this->version = config('solapi.version');
        $this->fromNumber = config('solapi.from');
    }

    /**
     * SMS 인증 코드 전송
     */
    public function sendVerificationCode(string $phoneNumber, string $countryCode = '+82'): array
    {
        return app(\App\Services\SmsVerification\SendVerificationCode\Service::class)($phoneNumber, $countryCode);
    }

    /**
     * 인증 코드 검증
     */
    public function verifyCode(string $phoneNumber, string $code, string $countryCode = '+82'): array
    {
        return app(\App\Services\SmsVerification\VerifyCode\Service::class)($phoneNumber, $code, $countryCode);
    }

}