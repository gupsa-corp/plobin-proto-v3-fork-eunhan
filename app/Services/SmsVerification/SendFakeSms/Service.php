<?php

namespace App\Services\SmsVerification\SendFakeSms;

use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(string $phoneNumber, string $countryCode, string $verificationCode): array
    {
        $message = str_replace(
            ['{app_name}', '{code}'],
            [config('solapi.app_name'), $verificationCode],
            config('solapi.verification.template')
        );

        Log::info('FAKE SMS sent (development mode)', [
            'phone_number' => $phoneNumber,
            'country_code' => $countryCode,
            'verification_code' => $verificationCode,
            'message' => $message
        ]);

        return ['success' => true];
    }
}