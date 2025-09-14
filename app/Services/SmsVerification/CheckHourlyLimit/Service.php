<?php

namespace App\Services\SmsVerification\CheckHourlyLimit;

use App\Models\SmsVerification;

class Service
{
    public function __invoke(): bool
    {
        $hourlyLimit = config('solapi.rate_limit.per_ip_hourly', 20);
        $hourlyAttempts = SmsVerification::getHourlyAttemptsByIp(request()->ip());

        return $hourlyAttempts < $hourlyLimit;
    }
}