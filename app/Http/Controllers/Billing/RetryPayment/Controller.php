<?php

namespace App\Http\Controllers\Billing\RetryPayment;

use App\Models\Organization;
use App\Models\BillingHistory;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function retry(Organization $organization, BillingHistory $billingHistory)
    {
        if ($billingHistory->organization_id !== $organization->id) {
            abort(404);
        }

        if (!$billingHistory->isCanceled() && !$billingHistory->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => '재시도할 수 없는 결제 상태입니다.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => '결제 재시도를 시작합니다.'
        ]);
    }
}
