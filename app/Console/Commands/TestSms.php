<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmsVerificationService;
use Illuminate\Support\Facades\Log;

class TestSms extends Command
{
    protected $signature = 'test:sms {phone_number} {--country-code=+82} {--force}';
    protected $description = 'SMS 전송 테스트 명령어';

    public function handle()
    {
        $phoneNumber = $this->argument('phone_number');
        $countryCode = $this->option('country-code');
        $force = $this->option('force');
        
        $this->info("🔧 SMS 전송 테스트 시작");
        $this->info("📱 전화번호: {$countryCode} {$phoneNumber}");
        $this->info("🌍 환경: " . app()->environment());
        
        // 환경 확인
        $apiKey = config('solapi.api_key');
        $apiSecret = config('solapi.api_secret');
        $fromNumber = config('solapi.from');
        $forceRealSms = config('solapi.force_real_sms', false);
        
        $this->info("\n📋 설정 확인:");
        $this->info("API Key: " . ($apiKey ? substr($apiKey, 0, 8) . '***' : '❌ 없음'));
        $this->info("API Secret: " . ($apiSecret ? substr($apiSecret, 0, 8) . '***' : '❌ 없음'));
        $this->info("발신번호: " . ($fromNumber ?: '❌ 없음'));
        $this->info("실제 SMS 전송: " . ($forceRealSms ? '✅ 활성화됨' : '❌ 비활성화됨 (개발모드)'));
        
        if ($forceRealSms) {
            $this->info("\n📱 실제 SMS 전송 모드: 인증번호가 실제 휴대폰으로 전송됩니다!");
        } elseif (!$force && (!$apiKey || $apiKey === 'your_solapi_api_key_here')) {
            $this->warn("\n⚠️ 개발환경 모드: 실제 SMS가 전송되지 않고 로그에만 기록됩니다.");
            $this->warn("실제 전송을 원하시면 --force 옵션을 사용하거나 SOLAPI_FORCE_REAL_SMS=true로 설정하세요.");
        }
        
        if (!$this->confirm("\n계속 진행하시겠습니까?")) {
            $this->info("❌ 취소되었습니다.");
            return 0;
        }
        
        try {
            $this->info("\n🚀 SMS 전송 중...");
            
            $smsService = new SmsVerificationService();
            $result = $smsService->sendVerificationCode($phoneNumber, $countryCode);
            
            if ($result['success']) {
                $this->info("✅ SMS 전송 성공!");
                $this->info("📝 메시지: " . $result['message']);
                
                if (isset($result['verification_id'])) {
                    $this->info("🔑 인증 ID: " . $result['verification_id']);
                }
                
                // 로그에서 인증번호 찾기 시도
                $this->info("\n🔍 로그에서 인증번호 찾는 중...");
                $this->findVerificationCodeInLogs();
                
            } else {
                $this->error("❌ SMS 전송 실패!");
                $this->error("💬 오류: " . $result['message']);
            }
            
        } catch (\Exception $e) {
            $this->error("🚨 예외 발생: " . $e->getMessage());
            $this->error("📍 파일: " . $e->getFile() . ':' . $e->getLine());
        }
        
        return 0;
    }
    
    private function findVerificationCodeInLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (!file_exists($logPath)) {
            $this->warn("📁 로그 파일을 찾을 수 없습니다.");
            return;
        }
        
        // 최근 로그 내용 읽기
        $logContent = file_get_contents($logPath);
        $lines = explode("\n", $logContent);
        $recentLines = array_slice($lines, -50); // 최근 50줄
        
        foreach (array_reverse($recentLines) as $line) {
            // FAKE SMS 로그 찾기
            if (strpos($line, 'FAKE SMS sent') !== false) {
                $this->info("📋 로그 발견: " . trim($line));
                
                // 인증번호 추출 시도
                if (preg_match('/verification_code.*?(\d{6})/', $line, $matches)) {
                    $this->info("🔢 인증번호: " . $matches[1]);
                }
                break;
            }
            
            // SMS 관련 로그 찾기
            if (strpos($line, 'SMS verification code sent') !== false || 
                strpos($line, 'verification_code') !== false) {
                $this->info("📋 SMS 로그: " . trim($line));
            }
        }
    }
}