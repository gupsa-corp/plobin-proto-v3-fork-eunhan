<?php

namespace App\Services\SmsVerification\FormatPhoneNumber;

class Service
{
    public function __invoke(string $phoneNumber, string $countryCode): string
    {
        // 하이픈 제거
        $phoneNumber = str_replace('-', '', $phoneNumber);

        if ($countryCode === '+82' && substr($phoneNumber, 0, 1) === '0') {
            // 한국 번호의 경우 맨 앞 0 제거 후 +82 추가
            return '+82' . substr($phoneNumber, 1);
        }

        return $countryCode . $phoneNumber;
    }
}