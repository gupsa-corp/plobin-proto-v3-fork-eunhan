<?php

namespace App\Services\Point\RefundPoints;

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
                throw new Exception('환불 포인트는 0보다 커야 합니다.');
            }

            // 포인트 계정 조회 또는 생성
            $pointAccount = $organization->getOrCreatePointAccount();
            $balanceBefore = $pointAccount->current_balance;
            $balanceAfter = $balanceBefore + $amount;

            // 포인트 계정 업데이트 (환불은 사용된 포인트에서 차감)
            $pointAccount->update([
                'current_balance' => $balanceAfter,
                'lifetime_spent' => max(0, $pointAccount->lifetime_spent - $amount),
            ]);

            // 조직 테이블의 포인트 잔액도 업데이트
            $organization->update(['points_balance' => $balanceAfter]);

            // 거래 내역 생성
            return PointTransaction::create([
                'organization_id' => $organization->id,
                'transaction_type' => PointTransaction::TYPE_REFUND,
                'amount' => $amount,
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