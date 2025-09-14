<?php

namespace App\Services\SmsVerification\SendSms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(string $phoneNumber, string $countryCode, string $verificationCode): array
    {
        // 실제 SMS 전송 강제 여부 확인
        $forceRealSms = config('solapi.force_real_sms', false);

        // 개발 환경에서는 가짜 SMS 서비스 사용 (단, force_real_sms가 true이면 실제 전송)
        $apiKey = config('solapi.api_key');
        if (!$forceRealSms && (app()->environment(['local', 'testing']) || !$apiKey || $apiKey === 'your_solapi_api_key_here')) {
            $sendFakeSmsService = app(\App\Services\SmsVerification\SendFakeSms\Service::class);
            return $sendFakeSmsService($phoneNumber, $countryCode, $verificationCode);
        }

        try {
            $formatPhoneNumberService = app(\App\Services\SmsVerification\FormatPhoneNumber\Service::class);
            $to = $formatPhoneNumberService($phoneNumber, $countryCode);

            $message = str_replace(
                ['{app_name}', '{code}'],
                [config('solapi.app_name'), $verificationCode],
                config('solapi.verification.template')
            );

            // SOLAPI v4 API 엔드포인트
            $url = 'https://api.solapi.com/messages/v4/send';

            // SOLAPI v4 올바른 페이로드 구조 (message 필드 필수)
            $payload = [
                'message' => [
                    'to' => $to,
                    'from' => config('solapi.from'),
                    'text' => $message
                ]
            ];

            Log::info('SOLAPI SMS request', [
                'url' => $url,
                'to' => $to,
                'from' => config('solapi.from'),
                'message_length' => strlen($message)
            ]);

            $generateAuthHeaderService = app(\App\Services\SmsVerification\GenerateAuthHeader\Service::class);
            $authHeader = $generateAuthHeaderService();

            $response = Http::withHeaders([
                'Authorization' => $authHeader,
                'Content-Type' => 'application/json'
            ])->post($url, $payload);

            if ($response->successful()) {
                $result = $response->json();

                Log::info('SOLAPI response received', [
                    'response' => $result,
                    'status_code' => $response->status()
                ]);

                // SOLAPI v4의 성공 응답 확인
                if (isset($result['statusCode']) && $result['statusCode'] === '2000') {
                    return ['success' => true];
                } elseif (isset($result['groupId']) || isset($result['messageId'])) {
                    // 또는 groupId나 messageId가 있으면 성공으로 간주
                    return ['success' => true];
                } else {
                    Log::error('SOLAPI SMS send failed', [
                        'response' => $result,
                        'status_code' => $response->status()
                    ]);

                    return [
                        'success' => false,
                        'message' => 'SMS 전송에 실패했습니다: ' . ($result['message'] ?? 'Unknown error')
                    ];
                }
            } else {
                Log::error('SOLAPI HTTP request failed', [
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'SMS 서비스에 연결할 수 없습니다. (HTTP ' . $response->status() . ')'
                ];
            }

        } catch (\Exception $e) {
            Log::error('SMS send exception', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'SMS 전송 중 오류가 발생했습니다.'
            ];
        }
    }
}