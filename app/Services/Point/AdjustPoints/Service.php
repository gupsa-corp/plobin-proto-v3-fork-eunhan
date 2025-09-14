<?php

namespace App\Services\Point\AdjustPoints;

use App\Models\Organization;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class Service
{
    public function __invoke(
        Organization $organization,
        float $amount,
        string $description,
        User $processedBy,
        ?array $metadata = null
    ): PointTransaction {
        return DB::transaction(function () use ($organization, $amount, $description, $processedBy, $metadata) {
            // 포인트 계정 조회 또는 생성
            $pointAccount = $organization->getOrCreatePointAccount();
            $balanceBefore = $pointAccount->current_balance;
            $balanceAfter = $balanceBefore + $amount;

            if ($balanceAfter < 0) {
                throw new Exception('조정 후 잔액이 음수가 될 수 없습니다.');
            }

            // 포인트 계정 업데이트
            if ($amount > 0) {
                $pointAccount->update([
                    'current_balance' => $balanceAfter,
                    'lifetime_earned' => $pointAccount->lifetime_earned + $amount,
                ]);
            } else {
                $pointAccount->update([
                    'current_balance' => $balanceAfter,
                    'lifetime_spent' => $pointAccount->lifetime_spent + abs($amount),
                ]);
            }

            // 조직 테이블의 포인트 잔액도 업데이트
            $organization->update(['points_balance' => $balanceAfter]);

            // 거래 내역 생성
            return PointTransaction::create([
                'organization_id' => $organization->id,
                'transaction_type' => PointTransaction::TYPE_ADMIN_ADJUST,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => PointTransaction::REASON_ADMIN_ADJUSTMENT,
                'description' => $description,
                'processed_by' => $processedBy->id,
                'metadata' => $metadata,
            ]);
        });
    }
}