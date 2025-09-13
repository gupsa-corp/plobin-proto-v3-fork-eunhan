<?php

namespace App\Http\Controllers\PlatformAdmin\Pricing;


use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class Controller extends \App\Http\Controllers\Controller
{
    public function overview()
    {
        return view('900-page-platform-admin.906-pricing.000-overview.000-index');
    }

    public function subscriptions()
    {
        return view('900-page-platform-admin.906-pricing.200-subscriptions.000-index');
    }

    public function analytics()
    {
        return view('900-page-platform-admin.906-pricing.300-analytics.000-index');
    }

    // API Methods
    public function getPlans(): JsonResponse
    {
        // 플랜 목록 조회 API
        $plans = [
            ['id' => 1, 'name' => 'Basic', 'price' => 9.99, 'features' => ['Feature 1', 'Feature 2']],
            ['id' => 2, 'name' => 'Pro', 'price' => 19.99, 'features' => ['Feature 1', 'Feature 2', 'Feature 3']],
            ['id' => 3, 'name' => 'Enterprise', 'price' => 99.99, 'features' => ['All Features']],
        ];
        
        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    public function createPlan(Request $request): JsonResponse
    {
        // 새 플랜 생성 API
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'features' => 'array'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan created successfully',
            'data' => ['id' => 4, 'name' => $request->name, 'price' => $request->price]
        ], 201);
    }

    public function showPlan($id): JsonResponse
    {
        // 특정 플랜 조회 API
        $plan = ['id' => $id, 'name' => 'Plan ' . $id, 'price' => 9.99 * $id];
        
        return response()->json([
            'success' => true,
            'data' => $plan
        ]);
    }

    public function updatePlan(Request $request, $id): JsonResponse
    {
        // 플랜 업데이트 API
        $request->validate([
            'name' => 'string|max:255',
            'price' => 'numeric|min:0',
            'features' => 'array'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan updated successfully',
            'data' => ['id' => $id, 'name' => $request->name ?? 'Updated Plan']
        ]);
    }

    public function deletePlan($id): JsonResponse
    {
        // 플랜 삭제 API
        return response()->json([
            'success' => true,
            'message' => 'Plan deleted successfully'
        ]);
    }

    public function getSubscriptions(): JsonResponse
    {
        // 구독 목록 조회 API
        $subscriptions = [
            ['id' => 1, 'user_id' => 1, 'plan' => 'Basic', 'status' => 'active', 'created_at' => '2024-01-01'],
            ['id' => 2, 'user_id' => 2, 'plan' => 'Pro', 'status' => 'cancelled', 'created_at' => '2024-01-15'],
            ['id' => 3, 'user_id' => 3, 'plan' => 'Enterprise', 'status' => 'active', 'created_at' => '2024-02-01'],
        ];
        
        return response()->json([
            'success' => true,
            'data' => $subscriptions
        ]);
    }

    public function updateSubscription(Request $request, $id): JsonResponse
    {
        // 구독 업데이트 API
        $request->validate([
            'status' => 'string|in:active,cancelled,suspended'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription updated successfully',
            'data' => ['id' => $id, 'status' => $request->status ?? 'active']
        ]);
    }
}