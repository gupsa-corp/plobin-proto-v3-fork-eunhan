<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationsController extends Controller
{
    protected $pointService;

    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
    }

    public function list(Request $request)
    {
        $query = Organization::with(['owner', 'pointAccount']);

        // 검색
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhereHas('owner', function($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%")
                               ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        // 상태 필터
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 포인트 범위 필터
        if ($request->filled('points_min')) {
            $query->where('points_balance', '>=', $request->points_min);
        }
        if ($request->filled('points_max')) {
            $query->where('points_balance', '<=', $request->points_max);
        }

        $organizations = $query->latest()
            ->paginate($request->get('per_page', 15));

        return view('900-page-platform-admin.902-organizations.000-list.000-index', [
            'organizations' => $organizations,
            'filters' => [
                'search' => $request->get('search', ''),
                'status' => $request->get('status', 'all'),
                'points_min' => $request->get('points_min', ''),
                'points_max' => $request->get('points_max', ''),
            ]
        ]);
    }

    public function details(Organization $organization)
    {
        $organization->load([
            'owner',
            'members.user',
            'projects',
            'billingHistories' => function($query) {
                $query->latest()->take(10);
            },
            'pointAccount',
            'pointTransactions' => function($query) {
                $query->latest()->take(20);
            }
        ]);

        $pointStats = $this->pointService->getPointStatistics($organization);

        return view('900-page-platform-admin.902-organizations.100-details.000-index', [
            'organization' => $organization,
            'pointStats' => $pointStats
        ]);
    }

    public function points(Request $request)
    {
        $query = Organization::with(['pointAccount']);

        // 포인트 잔액순 정렬
        $sortBy = $request->get('sort', 'points_balance');
        $sortOrder = $request->get('order', 'desc');
        
        if ($sortBy === 'points_balance') {
            $query->orderBy('points_balance', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // 검색
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // 포인트 범위 필터
        if ($request->filled('points_min')) {
            $query->where('points_balance', '>=', $request->points_min);
        }
        if ($request->filled('points_max')) {
            $query->where('points_balance', '<=', $request->points_max);
        }

        $organizations = $query->paginate($request->get('per_page', 20));

        return view('900-page-platform-admin.902-organizations.200-points.000-index', [
            'organizations' => $organizations,
            'filters' => [
                'search' => $request->get('search', ''),
                'points_min' => $request->get('points_min', ''),
                'points_max' => $request->get('points_max', ''),
                'sort' => $sortBy,
                'order' => $sortOrder
            ]
        ]);
    }

    public function pointsDetail(Organization $organization)
    {
        $organization->load(['pointAccount']);
        
        // 포인트 거래 내역
        $transactions = $organization->pointTransactions()
            ->latest()
            ->paginate(20);

        $pointStats = $this->pointService->getPointStatistics($organization);

        return view('900-page-platform-admin.902-organizations.200-points.100-detail.000-index', [
            'organization' => $organization,
            'transactions' => $transactions,
            'pointStats' => $pointStats
        ]);
    }

    public function adjustPoints(Request $request, Organization $organization)
    {
        $request->validate([
            'amount' => 'required|numeric|min:-999999|max:999999',
            'description' => 'required|string|max:255',
        ]);

        try {
            $this->pointService->adjustPoints(
                $organization,
                $request->amount,
                $request->description,
                Auth::user(),
                ['admin_note' => $request->get('admin_note', '')]
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '포인트가 성공적으로 조정되었습니다.',
                    'new_balance' => $organization->fresh()->getFormattedPointsBalance()
                ]);
            }

            return redirect()->back()->with('success', '포인트가 성공적으로 조정되었습니다.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}