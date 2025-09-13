<?php

namespace App\Http\Controllers\Billing\PaymentDetail;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\BillingHistory;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function show(Request $request, Organization $organization, BillingHistory $billingHistory)
    {
        if ($billingHistory->organization_id !== $organization->id) {
            abort(404);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $billingHistory->load(['subscription'])
            ]);
        }

        return view('800-page-organization-admin.803-page-billing.306-billing-payment-detail', [
            'billingHistory' => $billingHistory,
            'organization' => $organization
        ]);
    }
}
