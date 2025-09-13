<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SystemLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 지난 24시간 동안의 더미 로그 데이터 생성
        $logs = [];
        $now = Carbon::now();
        
        // 24시간 동안 매시간 로그 생성
        for ($i = 23; $i >= 0; $i--) {
            $timestamp = $now->copy()->subHours($i);
            
            $logs[] = [
                'type' => 'hourly_check',
                'message' => '시간별 시스템 상태 체크',
                'data' => json_encode([
                    'timestamp' => $timestamp->toDateTimeString(),
                    'memory_usage' => rand(50000000, 100000000), // 50MB ~ 100MB
                    'peak_memory' => rand(100000000, 150000000), // 100MB ~ 150MB  
                    'db_connection_count' => rand(1, 10),
                    'cache_status' => '정상',
                ]),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }
        
        // 추가 시스템 이벤트들
        $additionalLogs = [
            [
                'type' => 'system_startup',
                'message' => '시스템 시작',
                'data' => json_encode([
                    'version' => '1.0.0',
                    'environment' => config('app.env'),
                    'php_version' => phpversion(),
                ]),
                'created_at' => $now->subHours(12),
                'updated_at' => $now->subHours(12),
            ],
            [
                'type' => 'maintenance',
                'message' => '정기 유지보수',
                'data' => json_encode([
                    'type' => 'cache_clear',
                    'duration' => '5 minutes',
                    'status' => 'completed',
                ]),
                'created_at' => $now->subHours(6),
                'updated_at' => $now->subHours(6),
            ],
            [
                'type' => 'security_check',
                'message' => '보안 점검',
                'data' => json_encode([
                    'scan_type' => 'vulnerability',
                    'threats_found' => 0,
                    'status' => 'safe',
                ]),
                'created_at' => $now->subHours(3),
                'updated_at' => $now->subHours(3),
            ]
        ];
        
        $logs = array_merge($logs, $additionalLogs);
        
        DB::table('system_logs')->insert($logs);
        
        $this->command->info('시스템 로그 ' . count($logs) . '개가 성공적으로 시딩되었습니다.');
    }
}
