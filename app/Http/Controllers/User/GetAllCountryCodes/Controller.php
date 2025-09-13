<?php

namespace App\Http\Controllers\User\GetAllCountryCodes;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Services\PhoneNumberHelper;

class Controller extends ApiController
{
    public function __invoke()
    {
        // PhoneNumberHelper를 사용해서 모든 지원되는 국가 코드 가져오기
        $allCountryCodes = PhoneNumberHelper::getSupportedCountryCodes();

        // 추가 정보 포함해서 반환
        $enrichedCountryCodes = array_map(function($country) {
            return [
                'region' => $country['region'],
                'country_code' => $country['country_code'],
                'country_code_number' => $country['country_code_number'],
                'display_name' => $country['country_code'] . ' (' . $country['region'] . ')'
            ];
        }, $allCountryCodes);

        return $this->success([
            'country_codes' => $enrichedCountryCodes,
            'total' => count($enrichedCountryCodes),
            'popular_countries' => $this->getPopularCountries($enrichedCountryCodes)
        ], '모든 지원 국가 코드를 성공적으로 조회했습니다.');
    }

    private function getPopularCountries($allCountries)
    {
        $popularRegions = ['KR', 'US', 'GB', 'JP', 'CN', 'FR', 'DE', 'CA', 'AU', 'IN'];

        return array_filter($allCountries, function($country) use ($popularRegions) {
            return in_array($country['region'], $popularRegions);
        });
    }
}
