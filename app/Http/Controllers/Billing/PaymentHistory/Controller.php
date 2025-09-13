<?php

namespace App\Http\Controllers\Billing\PaymentHistory;

use Illuminate\Http\Request;
use App\Models\Organization;
use Carbon\Carbon;
use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{
    public function billing(Request $request, Organization $organization)
    {
        // Get organizations for sidebar (same logic as before)
        $organizations = Organization::select(['organizations.id', 'organizations.name'])
            ->join('organization_members', 'organizations.id', '=', 'organization_members.organization_id')
            ->where('organization_members.user_id', auth()->id())
            ->whereIn('organization_members.role_name', ['organization_admin', 'organization_owner', 'platform_admin'])
            ->orderBy('organizations.created_at', 'desc')
            ->get();

        // Get billing histories using the existing logic
        $query = $organization->billingHistories()->with(['subscription']);

        // Apply default filters for main page
        $period = $request->get('period', '6months');
        if ($period !== 'all') {
            $startDate = match($period) {
                '6months' => Carbon::now()->subMonths(6),
                '1year' => Carbon::now()->subYear(),
                default => Carbon::now()->subMonths(6)
            };
            $query->where('approved_at', '>=', $startDate);
        }

        $query->orderBy('approved_at', 'desc')->orderBy('requested_at', 'desc');
        $billingHistories = $query->paginate(10);

        return view('800-page-organization-admin.803-page-billing.300-billing', [
            'id' => $organization->id,
            'organizations' => $organizations,
            'billingHistories' => $billingHistories,
            'filters' => [
                'period' => $period,
                'status' => $request->get('status', 'all'),
                'search' => $request->get('search', '')
            ]
        ]);
    }

    public function index(Request $request, Organization $organization)
    {
        $query = $organization->billingHistories()->with(['subscription']);

        if ($request->filled('period')) {
            $period = $request->period;
            $startDate = match($period) {
                '6months' => Carbon::now()->subMonths(6),
                '1year' => Carbon::now()->subYear(),
                'all' => null,
                default => Carbon::now()->subMonths(6)
            };

            if ($startDate) {
                $query->where('approved_at', '>=', $startDate);
            }
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            $statusMap = [
                'completed' => 'DONE',
                'failed' => ['CANCELED', 'PARTIAL_CANCELED', 'ABORTED', 'EXPIRED'],
                'refunded' => 'PARTIAL_CANCELED',
                'pending' => ['READY', 'IN_PROGRESS', 'WAITING_FOR_DEPOSIT']
            ];

            if (isset($statusMap[$status])) {
                if (is_array($statusMap[$status])) {
                    $query->whereIn('status', $statusMap[$status]);
                } else {
                    $query->where('status', $statusMap[$status]);
                }
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('order_id', 'LIKE', "%{$search}%")
                  ->orWhere('card_number', 'LIKE', "%{$search}%");
            });
        }

        $query->orderBy('approved_at', 'desc')->orderBy('requested_at', 'desc');

        $perPage = $request->get('per_page', 10);
        $billingHistories = $query->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'billingHistories' => $billingHistories->items(),
                    'pagination' => [
                        'current_page' => $billingHistories->currentPage(),
                        'last_page' => $billingHistories->lastPage(),
                        'per_page' => $billingHistories->perPage(),
                        'total' => $billingHistories->total(),
                        'from' => $billingHistories->firstItem(),
                        'to' => $billingHistories->lastItem()
                    ]
                ]
            ]);
        }

        return view('800-page-organization-admin.803-page-billing.305-billing-payment-history', [
            'billingHistories' => $billingHistories,
            'organization' => $organization,
            'filters' => [
                'period' => $request->get('period', '6months'),
                'status' => $request->get('status', 'all'),
                'search' => $request->get('search', '')
            ]
        ]);
    }
}
