<?php

namespace App\Services\SmsVerification\GenerateAuthHeader;

use Illuminate\Support\Str;

class Service
{
    public function __invoke(): string
    {
        $apiKey = config('solapi.api_key');
        $apiSecret = config('solapi.api_secret');

        $salt = Str::random(32);
        $date = now()->toISOString();

        $data = $date . $salt;
        $signature = hash_hmac('sha256', $data, $apiSecret);

        return "HMAC-SHA256 apiKey={$apiKey}, date={$date}, salt={$salt}, signature={$signature}";
    }
}