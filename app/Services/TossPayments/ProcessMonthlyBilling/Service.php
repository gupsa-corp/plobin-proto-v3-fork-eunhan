<?php

namespace App\Services\TossPayments\ProcessMonthlyBilling;

use App\Models\Organization;
use App\Models\BillingHistory;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(Organization $organization): ?BillingHistory
    {
        $subscription = $organization->activeSubscription;
        if (!$subscription) {
            Log::warning("No active subscription for organization {$organization->id}");
            return null;
        }

        $paymentMethod = $organization->defaultPaymentMethod;
        if (!$paymentMethod) {
            Log::warning("No default payment method for organization {$organization->id}");
            return null;
        }

        $orderId = 'subscription_' . $subscription->id . '_' . now()->format('YmdHis');
        $orderName = $subscription->plan_name . ' 플랜 월간 구독';

        try {
            $payWithBillingKeyService = app(\App\Services\TossPayments\PayWithBillingKey\Service::class);
            $result = $payWithBillingKeyService(
                $paymentMethod->billing_key,
                "org_{$organization->id}",
                $subscription->monthly_price,
                $orderId,
                $orderName
            );

            // 결제 내역 저장
            $billingHistory = BillingHistory::create([
                'organization_id' => $organization->id,
                'subscription_id' => $subscription->id,
                'payment_key' => $result['paymentKey'],
                'order_id' => $orderId,
                'description' => $orderName,
                'amount' => $subscription->monthly_price,
                'vat' => round($subscription->monthly_price / 11),
                'status' => $result['status'],
                'method' => $result['method']['type'] ?? 'card',
                'requested_at' => now(),
                'approved_at' => $result['approvedAt'] ? new \DateTime($result['approvedAt']) : null,
                'toss_response' => $result,
                'receipt_url' => $result['receipt']['url'] ?? null,
                'card_number' => $result['card']['number'] ?? null,
                'card_company' => $result['card']['company'] ?? null,
            ]);

            // 구독 정보 업데이트 (다음 결제일)
            $subscription->update([
                'next_billing_date' => $subscription->next_billing_date->addMonth(),
                'current_period_end' => $subscription->current_period_end->addMonth(),
            ]);

            return $billingHistory;

        } catch (\Exception $e) {
            Log::error("Monthly billing failed for organization {$organization->id}: " . $e->getMessage());

            // 실패한 결제 내역도 저장
            BillingHistory::create([
                'organization_id' => $organization->id,
                'subscription_id' => $subscription->id,
                'payment_key' => '',
                'order_id' => $orderId,
                'description' => $orderName,
                'amount' => $subscription->monthly_price,
                'status' => 'ABORTED',
                'method' => 'card',
                'requested_at' => now(),
                'toss_response' => ['error' => $e->getMessage()],
            ]);

            return null;
        }
    }
}