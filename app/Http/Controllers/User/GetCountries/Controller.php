<?php

namespace App\Http\Controllers\User\GetCountries;

use App\Http\Controllers\Api\Core\Controller as ApiController;
use App\Services\PhoneNumberHelper;

class Controller extends ApiController
{
    public function __invoke()
    {
        // PhoneNumberHelper를 사용해서 지원되는 국가 코드 가져오기
        $allCountries = PhoneNumberHelper::getSupportedCountryCodes();

        // 주요 국가들만 선별 (한국어 이름 추가)
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
            'IN' => '인도',
            'IT' => '이탈리아',
            'ES' => '스페인',
            'BR' => '브라질',
            'RU' => '러시아',
            'SG' => '싱가포르',
            'TH' => '태국',
            'VN' => '베트남',
            'MY' => '말레이시아',
            'PH' => '필리핀',
            'ID' => '인도네시아',
            'MX' => '멕시코',
            'TR' => '터키',
            'SA' => '사우디아라비아',
            'AE' => '아랍에미리트',
            'EG' => '이집트',
            'ZA' => '남아프리카공화국',
            'NG' => '나이지리아',
            'KE' => '케냐',
            'AR' => '아르헨티나',
            'CL' => '칠레',
            'CO' => '콜롬비아',
            'PE' => '페루',
            'VE' => '베네수엘라',
        ];

        // 선별된 국가들만 필터링하고 한국어 이름 추가
        $selectedCountries = [];

        foreach ($allCountries as $country) {
            $region = $country['region'];

            if (isset($countryNames[$region])) {
                $selectedCountries[] = [
                    'region' => $region,
                    'country_code' => $country['country_code'],
                    'country_code_number' => $country['country_code_number'],
                    'name' => $countryNames[$region],
                    'display_name' => $country['country_code'] . ' (' . $countryNames[$region] . ')'
                ];
            }
        }

        // 한국을 맨 앞에, 나머지는 국가 코드 순으로 정렬
        usort($selectedCountries, function($a, $b) {
            if ($a['region'] === 'KR') return -1;
            if ($b['region'] === 'KR') return 1;
            return $a['country_code_number'] <=> $b['country_code_number'];
        });

        return $this->success([
            'countries' => $selectedCountries,
            'total' => count($selectedCountries)
        ], '국가 목록을 성공적으로 조회했습니다.');
    }
}
