<?php

namespace App\Http\Controllers\PlatformAdmin\Pricing;

use App\Http\Controllers\Controller as BaseController;
use App\Models\PricingPlan;
use App\Models\Subscription;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class Controller extends BaseController
{
    /**
     * 요금제 통계 데이터 조회
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $totalPlans = PricingPlan::count();

            // 활성 구독 수 (Subscription 테이블에서 계산)
            $activeSubscriptions = Subscription::where('status', 'active')->count();

            // 월 수익 계산 (활성 구독의 monthly_price 합계)
            $monthlyRevenue = Subscription::where('status', 'active')->sum('monthly_price');

            $activePlans = PricingPlan::where('is_active', true)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_plans' => $totalPlans,
                    'active_subscriptions' => $activeSubscriptions,
                    'monthly_revenue' => $monthlyRevenue ?? 0,
                    'active_plans' => $activePlans
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '통계 데이터를 불러올 수 없습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 요금제 목록 조회
     */
    public function getPlans(Request $request): JsonResponse
    {
        try {
            $query = PricingPlan::query();

            // 검색
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // 상태 필터
            if ($request->has('status') && $request->input('status') !== '') {
                $query->where('is_active', (bool) $request->input('status'));
            }

            // 타입 필터
            if ($request->has('type') && $request->input('type') !== '') {
                $query->where('type', $request->input('type'));
            }

            // 구독 수 포함
            $plans = $query->withCount([
                'subscriptions' => function($query) {
                    $query->where('status', 'active');
                }
            ])->orderBy('sort_order')->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $plans
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '요금제 목록을 불러올 수 없습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 요금제 생성
     */
    public function createPlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pricing_plans,slug',
            'description' => 'nullable|string',
            'type' => 'required|in:monthly,usage_based',
            'monthly_price' => 'nullable|numeric|min:0',
            'max_members' => 'nullable|integer|min:1',
            'max_projects' => 'nullable|integer|min:1',
            'max_sheets' => 'nullable|integer|min:1',
            'max_storage_gb' => 'nullable|integer|min:1',
            'price_per_member' => 'nullable|numeric|min:0',
            'price_per_project' => 'nullable|numeric|min:0',
            'price_per_gb' => 'nullable|numeric|min:0',
            'price_per_sheet' => 'nullable|numeric|min:0',
            'free_members' => 'nullable|integer|min:0',
            'free_projects' => 'nullable|integer|min:0',
            'free_storage_gb' => 'nullable|integer|min:0',
            'free_sheets' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean'
        ]);

        try {
            $plan = PricingPlan::create($validated);

            return response()->json([
                'success' => true,
                'message' => '요금제가 성공적으로 생성되었습니다.',
                'data' => $plan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '요금제 생성 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 요금제 수정
     */
    public function updatePlan(Request $request, $id): JsonResponse
    {
        $plan = PricingPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('pricing_plans')->ignore($id)],
            'description' => 'nullable|string',
            'type' => 'required|in:monthly,usage_based',
            'monthly_price' => 'nullable|numeric|min:0',
            'max_members' => 'nullable|integer|min:1',
            'max_projects' => 'nullable|integer|min:1',
            'max_sheets' => 'nullable|integer|min:1',
            'max_storage_gb' => 'nullable|integer|min:1',
            'price_per_member' => 'nullable|numeric|min:0',
            'price_per_project' => 'nullable|numeric|min:0',
            'price_per_gb' => 'nullable|numeric|min:0',
            'price_per_sheet' => 'nullable|numeric|min:0',
            'free_members' => 'nullable|integer|min:0',
            'free_projects' => 'nullable|integer|min:0',
            'free_storage_gb' => 'nullable|integer|min:0',
            'free_sheets' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean'
        ]);

        try {
            $plan->update($validated);

            return response()->json([
                'success' => true,
                'message' => '요금제가 성공적으로 수정되었습니다.',
                'data' => $plan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '요금제 수정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 요금제 삭제
     */
    public function deletePlan($id): JsonResponse
    {
        try {
            $plan = PricingPlan::findOrFail($id);

            // 구독중인 플랜이 있는지 확인
            $subscriptionsCount = Subscription::where('plan_name', $plan->slug)
                ->where('status', 'active')
                ->count();

            if ($subscriptionsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '구독중인 조직이 있어 삭제할 수 없습니다. 먼저 모든 구독을 취소하세요.'
                ], 400);
            }

            $plan->delete();

            return response()->json([
                'success' => true,
                'message' => '요금제가 성공적으로 삭제되었습니다.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '요금제 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 요금제 상세 조회
     */
    public function showPlan($id): JsonResponse
    {
        try {
            $plan = PricingPlan::withCount([
                'subscriptions' => function($query) {
                    $query->where('status', 'active');
                }
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $plan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '요금제를 찾을 수 없습니다: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 구독 목록 조회
     */
    public function getSubscriptions(Request $request): JsonResponse
    {
        try {
            $query = Subscription::with(['organization', 'pricingPlan']);

            // 검색
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->whereHas('organization', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            // 상태 필터
            if ($request->has('status') && $request->input('status') !== '') {
                $query->where('status', $request->input('status'));
            }

            // 플랜 필터
            if ($request->has('plan') && $request->input('plan') !== '') {
                $query->where('plan_name', $request->input('plan'));
            }

            $subscriptions = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $subscriptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '구독 목록을 불러올 수 없습니다: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 구독 상태 업데이트
     */
    public function updateSubscription(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,cancelled,suspended,pending',
            'next_payment_date' => 'nullable|date'
        ]);

        try {
            $subscription = Subscription::findOrFail($id);
            $subscription->update($validated);

            return response()->json([
                'success' => true,
                'message' => '구독 상태가 성공적으로 업데이트되었습니다.',
                'data' => $subscription->load(['organization', 'pricingPlan'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '구독 업데이트 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}
