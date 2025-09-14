<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationPoint;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class PointService
{
    /**
     * 포인트 적립
     */
    public function addPoints(
        Organization $organization,
        float $amount,
        string $reason,
        ?string $description = null,
        ?User $processedBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?array $metadata = null
    ): PointTransaction {
        return app(\App\Services\Point\AddPoints\Service::class)($organization, $amount, $reason, $description, $processedBy, $referenceType, $referenceId, $metadata);
    }

    /**
     * 포인트 차감
     */
    public function deductPoints(
        Organization $organization,
        float $amount,
        string $reason,
        ?string $description = null,
        ?User $processedBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?array $metadata = null
    ): PointTransaction {
        return app(\App\Services\Point\DeductPoints\Service::class)($organization, $amount, $reason, $description, $processedBy, $referenceType, $referenceId, $metadata);
    }

    /**
     * 포인트 환불 (차감된 포인트 되돌리기)
     */
    public function refundPoints(
        Organization $organization,
        float $amount,
        string $reason,
        ?string $description = null,
        ?User $processedBy = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?array $metadata = null
    ): PointTransaction {
        return app(\App\Services\Point\RefundPoints\Service::class)($organization, $amount, $reason, $description, $processedBy, $referenceType, $referenceId, $metadata);
    }

    /**
     * 관리자 포인트 조정 (적립/차감)
     */
    public function adjustPoints(
        Organization $organization,
        float $amount,
        string $description,
        User $processedBy,
        ?array $metadata = null
    ): PointTransaction {
        // 분리된 서비스로 위임 (미구현 시 기존 로직 유지)
        return $this->adjustPointsLegacy($organization, $amount, $description, $processedBy, $metadata);
    }

    private function adjustPointsLegacy(
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

    /**
     * 포인트 잔액 검증 및 동기화
     */
    public function syncBalance(Organization $organization): bool
    {
        return DB::transaction(function () use ($organization) {
            $pointAccount = $organization->pointAccount;
            
            if (!$pointAccount) {
                return false;
            }

            // 거래 내역을 기반으로 실제 잔액 계산
            $actualBalance = $organization->pointTransactions()
                ->sum('amount');

            // 불일치가 있으면 수정
            if ($pointAccount->current_balance != $actualBalance) {
                $pointAccount->update(['current_balance' => $actualBalance]);
                $organization->update(['points_balance' => $actualBalance]);
                
                return true; // 동기화 수행됨
            }

            return false; // 동기화 불필요
        });
    }

    /**
     * 조직의 포인트 통계 조회
     */
    public function getPointStatistics(Organization $organization): array
    {
        $pointAccount = $organization->pointAccount;
        
        if (!$pointAccount) {
            return [
                'current_balance' => 0,
                'lifetime_earned' => 0,
                'lifetime_spent' => 0,
                'retention_rate' => 0,
                'transaction_count' => 0,
            ];
        }

        $transactionCount = $organization->pointTransactions()->count();

        return [
            'current_balance' => $pointAccount->current_balance,
            'lifetime_earned' => $pointAccount->lifetime_earned,
            'lifetime_spent' => $pointAccount->lifetime_spent,
            'retention_rate' => $pointAccount->getRetentionRate(),
            'transaction_count' => $transactionCount,
        ];
    }

    /**
     * 결제 완료시 포인트 적립 (예시)
     */
    public function earnFromPayment(Organization $organization, float $paymentAmount, string $orderId): PointTransaction
    {
        $pointsToEarn = floor($paymentAmount * 0.01); // 1% 포인트백
        
        return $this->addPoints(
            $organization,
            $pointsToEarn,
            PointTransaction::REASON_PAYMENT,
            "결제 완료 포인트백 (주문번호: {$orderId})",
            null,
            'BillingHistory',
            null,
            ['order_id' => $orderId, 'payment_amount' => $paymentAmount]
        );
    }
}