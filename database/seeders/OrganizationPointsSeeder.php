<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\OrganizationPoint;
use App\Models\PointTransaction;
use Carbon\Carbon;

class OrganizationPointsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 조직 ID 1이 없으면 생성
        $organization = Organization::find(1);
        if (!$organization) {
            $organization = Organization::create([
                'id' => 1,
                'name' => '테스트 조직',
                'description' => '시딩용 테스트 조직입니다.',
                'user_id' => 2, // 관리자 테스트 사용자
                'status' => 'active',
                'points_balance' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // 조직 포인트 정보 생성/업데이트
        OrganizationPoint::updateOrCreate(
            ['organization_id' => 1],
            [
                'current_balance' => 7500,
                'lifetime_earned' => 10000,
                'lifetime_spent' => 2500,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        // 조직의 points_balance 업데이트
        $organization->update(['points_balance' => 7500]);

        // 포인트 거래 내역 생성
        $currentBalance = 0;
        $transactions = [
            [
                'organization_id' => 1,
                'transaction_type' => PointTransaction::TYPE_EARN,
                'amount' => 5000,
                'balance_before' => $currentBalance,
                'balance_after' => $currentBalance + 5000,
                'reason' => PointTransaction::REASON_BONUS,
                'description' => '월간 기본 포인트 지급',
                'reference_type' => null,
                'reference_id' => null,
                'processed_by' => 2,
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(30),
            ],
            [
                'organization_id' => 1,
                'transaction_type' => PointTransaction::TYPE_EARN,
                'amount' => 3000,
                'balance_before' => 5000,
                'balance_after' => 8000,
                'reason' => PointTransaction::REASON_BONUS,
                'description' => '프로젝트 완료 보너스',
                'reference_type' => null,
                'reference_id' => null,
                'processed_by' => 2,
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(20),
            ],
            [
                'organization_id' => 1,
                'transaction_type' => PointTransaction::TYPE_EARN,
                'amount' => 2000,
                'balance_before' => 8000,
                'balance_after' => 10000,
                'reason' => PointTransaction::REASON_PAYMENT,
                'description' => '추가 포인트 구매',
                'reference_type' => null,
                'reference_id' => null,
                'processed_by' => 2,
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(10),
            ],
            [
                'organization_id' => 1,
                'transaction_type' => PointTransaction::TYPE_SPEND,
                'amount' => -1500,
                'balance_before' => 10000,
                'balance_after' => 8500,
                'reason' => PointTransaction::REASON_SUBSCRIPTION,
                'description' => '샌드박스 사용료',
                'reference_type' => null,
                'reference_id' => null,
                'processed_by' => 2,
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(7),
            ],
            [
                'organization_id' => 1,
                'transaction_type' => PointTransaction::TYPE_SPEND,
                'amount' => -1000,
                'balance_before' => 8500,
                'balance_after' => 7500,
                'reason' => PointTransaction::REASON_SUBSCRIPTION,
                'description' => 'API 호출 비용',
                'reference_type' => null,
                'reference_id' => null,
                'processed_by' => 2,
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($transactions as $transaction) {
            PointTransaction::create($transaction);
        }

        $this->command->info('조직 ID 1에 대한 포인트 시딩 데이터가 생성되었습니다.');
        $this->command->info('- 총 포인트: 10,000');
        $this->command->info('- 사용된 포인트: 2,500');
        $this->command->info('- 잔여 포인트: 7,500');
        $this->command->info('- 거래 내역: 5건');
    }
}
