<?php

namespace App\Services\Point\DeductPoints;

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
        string $reason,
        ?string $description = null,
        ?User $processedBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?array $metadata = null
    ): PointTransaction {
        return DB::transaction(function () use (
            $organization, $amount, $reason, $description, $processedBy,
            $referenceType, $referenceId, $metadata
        ) {
            if ($amount <= 0) {
                throw new Exception('차감 포인트는 0보다 커야 합니다.');
            }

            // 포인트 계정 조회
            $pointAccount = $organization->getOrCreatePointAccount();
            $balanceBefore = $pointAccount->current_balance;

            if ($balanceBefore < $amount) {
                throw new Exception('포인트 잔액이 부족합니다.');
            }

            $balanceAfter = $balanceBefore - $amount;

            // 포인트 계정 업데이트
            $pointAccount->update([
                'current_balance' => $balanceAfter,
                'lifetime_spent' => $pointAccount->lifetime_spent + $amount,
            ]);

            // 조직 테이블의 포인트 잔액도 업데이트
            $organization->update(['points_balance' => $balanceAfter]);

            // 거래 내역 생성 (음수로 저장)
            return PointTransaction::create([
                'organization_id' => $organization->id,
                'transaction_type' => PointTransaction::TYPE_SPEND,
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reason' => $reason,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'processed_by' => $processedBy?->id,
                'metadata' => $metadata,
            ]);
        });
    }
}