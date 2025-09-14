<?php

namespace App\Services\Point\EarnFromPayment;

use App\Models\Organization;
use App\Models\PointTransaction;

class Service
{
    public function __invoke(Organization $organization, float $paymentAmount, string $orderId): PointTransaction
    {
        $pointsToEarn = floor($paymentAmount * 0.01); // 1% 포인트백

        return app(\App\Services\Point\AddPoints\Service::class)(
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