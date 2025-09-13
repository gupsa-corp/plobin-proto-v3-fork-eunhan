<?php

namespace App\Http\Controllers\Billing\DownloadReceipt;

use App\Models\Organization;
use App\Models\BillingHistory;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function download(Organization $organization, BillingHistory $billingHistory)
    {
        if ($billingHistory->organization_id !== $organization->id) {
            abort(404);
        }

        if (!$billingHistory->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => '결제 완료된 내역만 영수증 다운로드가 가능합니다.'
            ], 400);
        }

        if ($billingHistory->receipt_url) {
            return redirect($billingHistory->receipt_url);
        }

        return response()->json([
            'success' => false,
            'message' => '영수증을 찾을 수 없습니다.'
        ], 404);
    }
}
