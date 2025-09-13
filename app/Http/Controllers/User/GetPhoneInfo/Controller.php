<?php

namespace App\Http\Controllers\User\GetPhoneInfo;

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
        $isoCountryCode = PhoneNumberHelper::getCountryCodeForPhone($countryCode);
        $phoneType = PhoneNumberHelper::getPhoneType($phoneNumber, $countryCode);

        // 포맷팅된 번호들
        $formatted = [
            'national' => PhoneNumberHelper::formatNational($phoneNumber, $countryCode),
            'international' => PhoneNumberHelper::formatInternational($phoneNumber, $countryCode),
            'e164' => PhoneNumberHelper::formatE164($phoneNumber, $countryCode)
        ];

        // 국가명 매핑
        $countryNames = [
            'KR' => '한국',
            'US' => '미국',
            'GB' => '영국',
            'JP' => '일본',
            'CN' => '중국',
            'FR' => '프랑스',
            'DE' => '독일',
            'CA' => '캐나다',
            'AU' => '호주',
            'IN' => '인도'
        ];

        // 전화번호 타입 한국어 매핑
        $phoneTypes = [
            'mobile' => '휴대폰',
            'fixed_line' => '유선전화',
            'fixed_line_or_mobile' => '유선/휴대폰',
            'toll_free' => '무료통화',
            'premium_rate' => '유료통화',
            'shared_cost' => '공유비용',
            'voip' => '인터넷전화',
            'personal_number' => '개인번호',
            'pager' => '삐삐',
            'uan' => '통합번호',
            'voicemail' => '음성사서함',
            'unknown' => '알 수 없음'
        ];

        return $this->success([
            'phone_number' => $phoneNumber,
            'country_code' => $countryCode,
            'iso_country_code' => $isoCountryCode,
            'country_name' => $countryNames[$isoCountryCode] ?? $isoCountryCode,
            'valid' => $isValid,
            'type' => $phoneType,
            'type_korean' => $phoneTypes[$phoneType] ?? '알 수 없음',
            'formatted' => $formatted,
            'analysis' => [
                'is_mobile' => $phoneType === 'mobile',
                'is_landline' => $phoneType === 'fixed_line',
                'can_receive_sms' => in_array($phoneType, ['mobile', 'fixed_line_or_mobile']),
                'recommended_for_auth' => in_array($phoneType, ['mobile', 'fixed_line_or_mobile'])
            ]
        ], '전화번호 정보를 성공적으로 조회했습니다.');
    }
}
