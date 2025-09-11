<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MonitorLogs extends Command
{
    protected $signature = 'monitor:sms-logs';
    protected $description = 'SMS 관련 로그를 실시간으로 모니터링';

    public function handle()
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (!file_exists($logPath)) {
            $this->error("로그 파일을 찾을 수 없습니다: {$logPath}");
            return 1;
        }
        
        $this->info("🔍 SMS 로그 모니터링 시작 (Ctrl+C로 종료)");
        $this->info("📁 로그 파일: {$logPath}");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        $lastSize = filesize($logPath);
        
        while (true) {
            clearstatcache();
            $currentSize = filesize($logPath);
            
            if ($currentSize > $lastSize) {
                $handle = fopen($logPath, 'r');
                fseek($handle, $lastSize);
                
                while (($line = fgets($handle)) !== false) {
                    $line = trim($line);
                    
                    // SMS 관련 로그만 필터링
                    if ($this->isSmsRelated($line)) {
                        $this->displayLogLine($line);
                    }
                }
                
                fclose($handle);
                $lastSize = $currentSize;
            }
            
            usleep(500000); // 0.5초 대기
        }
        
        return 0;
    }
    
    private function isSmsRelated($line): bool
    {
        $keywords = [
            'SMS',
            'sms',
            'verification',
            'FAKE SMS',
            'SOLAPI',
            'solapi',
            'verification_code',
            'phone_number',
            'sendVerificationCode',
            'verifyCode'
        ];
        
        foreach ($keywords as $keyword) {
            if (strpos($line, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    private function displayLogLine($line)
    {
        $timestamp = date('H:i:s');
        
        // 로그 타입별 색상 구분
        if (strpos($line, 'ERROR') !== false || strpos($line, 'error') !== false) {
            $this->line("<fg=red>[{$timestamp}] {$line}</>");
        } elseif (strpos($line, 'FAKE SMS sent') !== false) {
            $this->line("<fg=yellow>[{$timestamp}] 🔶 FAKE SMS: {$line}</>");
            
            // 인증번호 추출 시도
            if (preg_match('/verification_code.*?(\d{6})/', $line, $matches)) {
                $this->line("<fg=bright-yellow>    🔢 인증번호: {$matches[1]}</>");
            }
        } elseif (strpos($line, 'SMS verification code sent') !== false) {
            $this->line("<fg=green>[{$timestamp}] ✅ SMS 전송: {$line}</>");
        } elseif (strpos($line, 'INFO') !== false) {
            $this->line("<fg=blue>[{$timestamp}] ℹ️ {$line}</>");
        } else {
            $this->line("[{$timestamp}] {$line}");
        }
    }
}