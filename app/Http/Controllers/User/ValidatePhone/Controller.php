<?php

namespace App\Http\Controllers\User\ValidatePhone;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Services\PhoneNumberHelper;

class Controller extends ApiController
{
    public function __invoke(Request $request)
    {
        $phoneNumber = $request->phone_number;
        $countryCode = $request->country_code;

        // 전화번호 검증
        $isValid = PhoneNumberHelper::isValid($phoneNumber, $countryCode);

        if (!$isValid) {
            return $this->error([
                'message' => '올바른 전화번호 형식을 입력해주세요.',
                'phone_number' => $phoneNumber,
                'country_code' => $countryCode
            ], 422);
        }

        // 전화번호 정보 반환
        $isoCountryCode = PhoneNumberHelper::getCountryCodeForPhone($countryCode);
        $phoneType = PhoneNumberHelper::getPhoneType($phoneNumber, $countryCode);

        return $this->success([
            'valid' => true,
            'message' => '유효한 전화번호입니다.',
            'phone_number' => $phoneNumber,
            'country_code' => $countryCode,
            'iso_country_code' => $isoCountryCode,
            'type' => $phoneType,
            'formatted' => [
                'national' => PhoneNumberHelper::formatNational($phoneNumber, $countryCode),
                'international' => PhoneNumberHelper::formatInternational($phoneNumber, $countryCode),
                'e164' => PhoneNumberHelper::formatE164($phoneNumber, $countryCode)
            ]
        ]);
    }
}
