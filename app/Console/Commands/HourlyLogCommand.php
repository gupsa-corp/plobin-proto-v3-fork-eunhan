<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HourlyLogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:hourly-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '1시간마다 시스템 상태를 로깅합니다';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        
        // 시스템 상태 정보 수집
        $systemInfo = [
            'timestamp' => $now->toDateTimeString(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'db_connection_count' => $this->getDatabaseConnectionCount(),
            'cache_status' => $this->getCacheStatus(),
        ];
        
        // 로그 기록
        Log::info('시간별 시스템 상태 체크', $systemInfo);
        
        // 데이터베이스에도 기록
        try {
            DB::table('system_logs')->insert([
                'type' => 'hourly_check',
                'message' => '시간별 시스템 상태 체크',
                'data' => json_encode($systemInfo),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } catch (\Exception $e) {
            Log::error('시스템 로그 DB 저장 실패: ' . $e->getMessage());
        }
        
        // 콘솔 출력
        $this->info('시간별 시스템 체크 완료: ' . $now->format('Y-m-d H:i:s'));
        $this->table(['항목', '값'], [
            ['메모리 사용량', $this->formatBytes($systemInfo['memory_usage'])],
            ['최대 메모리 사용량', $this->formatBytes($systemInfo['peak_memory'])],
            ['DB 연결 수', $systemInfo['db_connection_count']],
            ['캐시 상태', $systemInfo['cache_status']],
        ]);
        
        return Command::SUCCESS;
    }
    
    /**
     * 데이터베이스 연결 수 확인
     */
    private function getDatabaseConnectionCount(): int
    {
        try {
            $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");
            return (int) ($result[0]->Value ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * 캐시 상태 확인
     */
    private function getCacheStatus(): string
    {
        try {
            \Illuminate\Support\Facades\Cache::put('system_check', 'test', 60);
            $test = \Illuminate\Support\Facades\Cache::get('system_check');
            return $test === 'test' ? '정상' : '오류';
        } catch (\Exception $e) {
            return '오류: ' . $e->getMessage();
        }
    }
    
    /**
     * 바이트를 읽기 쉬운 형식으로 변환
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
