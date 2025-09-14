<?php

namespace App\Services\SmsVerification\VerifyCode;

use App\Models\SmsVerification;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(string $phoneNumber, string $code, string $countryCode = '+82'): array
    {
        try {
            $verification = SmsVerification::getValidVerification($phoneNumber, $countryCode);

            if (!$verification) {
                return [
                    'success' => false,
                    'message' => '유효한 인증번호가 없습니다.'
                ];
            }

            // 최대 시도 횟수 확인
            if ($verification->attempt_count >= config('solapi.verification.max_attempts', 3)) {
                return [
                    'success' => false,
                    'message' => '인증 시도 횟수를 초과했습니다. 새로운 인증번호를 요청해주세요.'
                ];
            }

            // 인증 코드 검증
            if ($verification->verify($code)) {
                Log::info('SMS verification successful', [
                    'phone_number' => $phoneNumber,
                    'verification_id' => $verification->id
                ]);

                return [
                    'success' => true,
                    'message' => '인증이 완료되었습니다.',
                    'verification_id' => $verification->id
                ];
            } else {
                $verification->incrementAttempts();

                Log::info('SMS verification failed', [
                    'phone_number' => $phoneNumber,
                    'verification_id' => $verification->id,
                    'attempt_count' => $verification->attempt_count
                ]);

                return [
                    'success' => false,
                    'message' => '인증번호가 올바르지 않습니다.'
                ];
            }

        } catch (\Exception $e) {
            Log::error('SMS verification check failed', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => '인증 확인 중 오류가 발생했습니다.'
            ];
        }
    }
}