<?php

namespace App\Services;

use App\Models\SmsVerification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SmsVerificationService
{
    private string $apiKey;
    private string $apiSecret;
    private string $baseUrl;
    private string $version;
    private string $fromNumber;

    public function __construct()
    {
        $this->apiKey = config('solapi.api_key');
        $this->apiSecret = config('solapi.api_secret');
        $this->baseUrl = config('solapi.base_url');
        $this->version = config('solapi.version');
        $this->fromNumber = config('solapi.from');
    }

    /**
     * SMS 인증 코드 전송
     */
    public function sendVerificationCode(string $phoneNumber, string $countryCode = '+82'): array
    {
        try {
            // 전화번호 형식 검증
            if (!$this->validatePhoneNumber($phoneNumber, $countryCode)) {
                return [
                    'success' => false,
                    'message' => '올바르지 않은 전화번호 형식입니다.'
                ];
            }

            // 일일 전송 한도 확인
            if (!$this->checkDailyLimit($phoneNumber, $countryCode)) {
                return [
                    'success' => false,
                    'message' => '일일 인증 시도 횟수를 초과했습니다. 내일 다시 시도해주세요.'
                ];
            }

            // IP별 시간당 한도 확인
            if (!$this->checkHourlyLimit()) {
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
            $verificationCode = $this->generateVerificationCode();
            
            // SMS 전송
            $smsResult = $this->sendSms($phoneNumber, $countryCode, $verificationCode);
            
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

    /**
     * 인증 코드 검증
     */
    public function verifyCode(string $phoneNumber, string $code, string $countryCode = '+82'): array
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

    /**
     * SOLAPI를 통한 SMS 전송
     */
    private function sendSms(string $phoneNumber, string $countryCode, string $verificationCode): array
    {
        // 실제 SMS 전송 강제 여부 확인
        $forceRealSms = config('solapi.force_real_sms', false);
        
        // 개발 환경에서는 가짜 SMS 서비스 사용 (단, force_real_sms가 true이면 실제 전송)
        if (!$forceRealSms && (app()->environment(['local', 'testing']) || !$this->apiKey || $this->apiKey === 'your_solapi_api_key_here')) {
            return $this->sendFakeSms($phoneNumber, $countryCode, $verificationCode);
        }

        try {
            $to = $this->formatPhoneNumber($phoneNumber, $countryCode);
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
                    'from' => $this->fromNumber,
                    'text' => $message
                ]
            ];

            Log::info('SOLAPI SMS request', [
                'url' => $url,
                'to' => $to,
                'from' => $this->fromNumber,
                'message_length' => strlen($message)
            ]);

            $response = Http::withHeaders([
                'Authorization' => $this->generateAuthHeader(),
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

    /**
     * 개발 환경용 가짜 SMS 전송
     */
    private function sendFakeSms(string $phoneNumber, string $countryCode, string $verificationCode): array
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

    /**
     * SOLAPI 인증 헤더 생성
     */
    private function generateAuthHeader(): string
    {
        $salt = Str::random(32);
        $date = now()->toISOString();
        
        $data = $date . $salt;
        $signature = hash_hmac('sha256', $data, $this->apiSecret);
        
        return "HMAC-SHA256 apiKey={$this->apiKey}, date={$date}, salt={$salt}, signature={$signature}";
    }

    /**
     * 인증 코드 생성
     */
    private function generateVerificationCode(): string
    {
        $length = config('solapi.verification.code_length', 6);
        return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * 전화번호 형식 검증
     */
    private function validatePhoneNumber(string $phoneNumber, string $countryCode): bool
    {
        // 기본적인 형식 검증
        if ($countryCode === '+82') {
            // 한국 전화번호 검증 (01X-XXXX-XXXX 형식)
            return preg_match('/^01[0-9]-?[0-9]{4}-?[0-9]{4}$/', $phoneNumber);
        }
        
        // 다른 국가는 기본 검증
        return preg_match('/^[0-9\-\s\+]{8,15}$/', $phoneNumber);
    }

    /**
     * 전화번호 포맷팅
     */
    private function formatPhoneNumber(string $phoneNumber, string $countryCode): string
    {
        // 하이픈 제거
        $phoneNumber = str_replace('-', '', $phoneNumber);
        
        if ($countryCode === '+82' && substr($phoneNumber, 0, 1) === '0') {
            // 한국 번호의 경우 맨 앞 0 제거 후 +82 추가
            return '+82' . substr($phoneNumber, 1);
        }
        
        return $countryCode . $phoneNumber;
    }

    /**
     * 일일 전송 한도 확인
     */
    private function checkDailyLimit(string $phoneNumber, string $countryCode): bool
    {
        $dailyLimit = config('solapi.rate_limit.per_phone_daily', 10);
        $todayAttempts = SmsVerification::getDailyAttempts($phoneNumber, $countryCode);
        
        return $todayAttempts < $dailyLimit;
    }

    /**
     * 시간당 IP 한도 확인
     */
    private function checkHourlyLimit(): bool
    {
        $hourlyLimit = config('solapi.rate_limit.per_ip_hourly', 20);
        $hourlyAttempts = SmsVerification::getHourlyAttemptsByIp(request()->ip());
        
        return $hourlyAttempts < $hourlyLimit;
    }
}