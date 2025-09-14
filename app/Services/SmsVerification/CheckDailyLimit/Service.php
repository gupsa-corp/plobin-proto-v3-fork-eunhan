<?php

namespace App\Services\SmsVerification\CheckDailyLimit;

use App\Models\SmsVerification;

class Service
{
    public function __invoke(string $phoneNumber, string $countryCode): bool
    {
        $dailyLimit = config('solapi.rate_limit.per_phone_daily', 10);
        $todayAttempts = SmsVerification::getDailyAttempts($phoneNumber, $countryCode);

        return $todayAttempts < $dailyLimit;
    }
}