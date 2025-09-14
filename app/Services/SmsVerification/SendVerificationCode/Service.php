<?php

namespace App\Services\SmsVerification\SendVerificationCode;

use App\Models\SmsVerification;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(string $phoneNumber, string $countryCode = '+82'): array
    {
        try {
            // 전화번호 형식 검증
            $validatePhoneNumberService = app(\App\Services\SmsVerification\ValidatePhoneNumber\Service::class);
            if (!$validatePhoneNumberService($phoneNumber, $countryCode)) {
                return [
                    'success' => false,
                    'message' => '올바르지 않은 전화번호 형식입니다.'
                ];
            }

            // 일일 전송 한도 확인
            $checkDailyLimitService = app(\App\Services\SmsVerification\CheckDailyLimit\Service::class);
            if (!$checkDailyLimitService($phoneNumber, $countryCode)) {
                return [
                    'success' => false,
                    'message' => '일일 인증 시도 횟수를 초과했습니다. 내일 다시 시도해주세요.'
                ];
            }

            // IP별 시간당 한도 확인
            $checkHourlyLimitService = app(\App\Services\SmsVerification\CheckHourlyLimit\Service::class);
            if (!$checkHourlyLimitService()) {
                return [
                    'success' => false,
                    'message' => '너무 많은 요청이 발생했습니다. 잠시 후 다시 시도해주세요.'
                ];
            }

            // 기존 유효한 인증 코드가 있는지 확인
            $existingVerification = SmsVerification::getValidVerification($phoneNumber, $countryCode);
            if ($existingVerification && $existingVerification->created_at->diffInSeconds(now()) < config('solapi.verification.resend_cooldown_seconds', 60)) {
                return [
                    'success' => false,
                    'message' => '인증번호 재전송은 1분 후 가능합니다.'
                ];
            }

            // 인증 코드 생성
            $generateVerificationCodeService = app(\App\Services\SmsVerification\GenerateVerificationCode\Service::class);
            $verificationCode = $generateVerificationCodeService();

            // SMS 전송
            $sendSmsService = app(\App\Services\SmsVerification\SendSms\Service::class);
            $smsResult = $sendSmsService($phoneNumber, $countryCode, $verificationCode);

            if (!$smsResult['success']) {
                return $smsResult;
            }

            // 데이터베이스에 인증 정보 저장
            $verification = SmsVerification::create([
                'phone_number' => $phoneNumber,
                'country_code' => $countryCode,
                'verification_code' => $verificationCode,
                'expires_at' => now()->addMinutes(config('solapi.verification.expire_minutes', 5)),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Log::info('SMS verification code sent', [
                'phone_number' => $phoneNumber,
                'country_code' => $countryCode,
                'verification_id' => $verification->id
            ]);

            return [
                'success' => true,
                'message' => '인증번호가 전송되었습니다.',
                'verification_id' => $verification->id,
                'expires_at' => $verification->expires_at->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error('SMS verification send failed', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'SMS 전송 중 오류가 발생했습니다.'
            ];
        }
    }
}