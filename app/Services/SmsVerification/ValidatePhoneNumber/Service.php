<?php

namespace App\Services\SmsVerification\ValidatePhoneNumber;

class Service
{
    public function __invoke(string $phoneNumber, string $countryCode): bool
    {
        // 기본적인 형식 검증
        if ($countryCode === '+82') {
            // 한국 전화번호 검증 (01X-XXXX-XXXX 형식)
            return preg_match('/^01[0-9]-?[0-9]{4}-?[0-9]{4}$/', $phoneNumber);
        }

        // 다른 국가는 기본 검증
        return preg_match('/^[0-9\-\s\+]{8,15}$/', $phoneNumber);
    }
}