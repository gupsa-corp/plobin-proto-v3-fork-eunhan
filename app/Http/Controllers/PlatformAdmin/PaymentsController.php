<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\BillingHistory;
use App\Models\Organization;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentsController extends Controller
{
    public function list(Request $request)
    {
        return $this->history($request); // 기본적으로 히스토리 페이지로
    }

    public function history(Request $request)
    {
        $query = BillingHistory::with(['organization', 'subscription']);

        // 기간 필터
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

        // 상태 필터
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

        // 조직 필터
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        // 검색
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('order_id', 'LIKE', "%{$search}%")
                  ->orWhere('card_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('organization', function($orgQuery) use ($search) {
                      $orgQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $query->orderBy('approved_at', 'desc')->orderBy('requested_at', 'desc');

        $billingHistories = $query->paginate($request->get('per_page', 15));

        // 조직 목록 (필터용)
        $organizations = Organization::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('900-page-platform-admin.904-payments.000-history.000-index', [
            'billingHistories' => $billingHistories,
            'organizations' => $organizations,
            'filters' => [
                'period' => $request->get('period', '6months'),
                'status' => $request->get('status', 'all'),
                'organization_id' => $request->get('organization_id', ''),
                'search' => $request->get('search', '')
            ]
        ]);
    }

    public function details(BillingHistory $billingHistory)
    {
        $billingHistory->load(['organization', 'subscription']);

        return view('900-page-platform-admin.904-payments.200-details.000-index', [
            'billingHistory' => $billingHistory
        ]);
    }

    public function refunds(Request $request)
    {
        $query = BillingHistory::with(['organization'])
            ->where('status', 'PARTIAL_CANCELED');

        // 기간 필터
        if ($request->filled('period')) {
            $period = $request->period;
            $startDate = match($period) {
                '3months' => Carbon::now()->subMonths(3),
                '6months' => Carbon::now()->subMonths(6),
                '1year' => Carbon::now()->subYear(),
                'all' => null,
                default => Carbon::now()->subMonths(3)
            };

            if ($startDate) {
                $query->where('approved_at', '>=', $startDate);
            }
        }

        // 검색
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('order_id', 'LIKE', "%{$search}%")
                  ->orWhereHas('organization', function($orgQuery) use ($search) {
                      $orgQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $refunds = $query->latest('approved_at')
            ->paginate($request->get('per_page', 15));

        return view('900-page-platform-admin.904-payments.300-refunds.000-index', [
            'refunds' => $refunds,
            'filters' => [
                'period' => $request->get('period', '3months'),
                'search' => $request->get('search', '')
            ]
        ]);
    }

    public function cancel(Request $request, BillingHistory $billingHistory)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'cancel_amount' => 'nullable|numeric|min:0|max:' . $billingHistory->amount,
        ]);

        try {
            // 여기에 실제 결제 취소 로직 구현
            // 예: 토스페이먼츠 API 호출

            $billingHistory->update([
                'status' => $request->cancel_amount == $billingHistory->amount ? 'CANCELED' : 'PARTIAL_CANCELED',
                // 취소 관련 메타데이터 추가
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '결제가 성공적으로 취소되었습니다.'
                ]);
            }

            return redirect()->back()->with('success', '결제가 성공적으로 취소되었습니다.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }

            return redirect()->back()->with('error', '결제 취소 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    public function refund(Request $request, BillingHistory $billingHistory)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'refund_amount' => 'required|numeric|min:0|max:' . $billingHistory->amount,
        ]);

        try {
            // 여기에 실제 환불 로직 구현
            // 예: 토스페이먼츠 환불 API 호출

            $billingHistory->update([
                'status' => 'PARTIAL_CANCELED',
                // 환불 관련 메타데이터 추가
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '환불이 성공적으로 처리되었습니다.'
                ]);
            }

            return redirect()->back()->with('success', '환불이 성공적으로 처리되었습니다.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }

            return redirect()->back()->with('error', '환불 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}