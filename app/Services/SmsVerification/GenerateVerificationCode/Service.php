<?php

namespace App\Services\SmsVerification\GenerateVerificationCode;

class Service
{
    public function __invoke(): string
    {
        $length = config('solapi.verification.code_length', 6);
        return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }
}