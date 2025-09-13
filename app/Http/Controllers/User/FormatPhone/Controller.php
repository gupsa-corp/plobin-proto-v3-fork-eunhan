<?php

namespace App\Http\Controllers\User\FormatPhone;

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
                'message' => '유효하지 않은 전화번호입니다.',
                'phone_number' => $phoneNumber,
                'country_code' => $countryCode
            ], 422);
        }

        // 다양한 형식으로 포맷팅
        $formatted = [
            'national' => PhoneNumberHelper::formatNational($phoneNumber, $countryCode),
            'international' => PhoneNumberHelper::formatInternational($phoneNumber, $countryCode),
            'e164' => PhoneNumberHelper::formatE164($phoneNumber, $countryCode)
        ];

        $isoCountryCode = PhoneNumberHelper::getCountryCodeForPhone($countryCode);
        $phoneType = PhoneNumberHelper::getPhoneType($phoneNumber, $countryCode);

        return $this->success([
            'original' => $phoneNumber,
            'country_code' => $countryCode,
            'iso_country_code' => $isoCountryCode,
            'valid' => true,
            'type' => $phoneType,
            'formatted' => $formatted
        ], '전화번호 포맷팅이 완료되었습니다.');
    }
}
