<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\BillingHistory;
use App\Models\PointTransaction;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function statistics()
    {
        $monthlyStats = $this->getMonthlyStatistics();
        
        return view('900-page-platform-admin.901-dashboard.100-statistics.000-index', [
            'monthlyStats' => $monthlyStats
        ]);
    }

    public function recentActivities()
    {
        $activities = $this->getRecentActivities();
        
        return view('900-page-platform-admin.901-dashboard.200-activities.000-index', [
            'activities' => $activities
        ]);
    }

    private function getOverviewStats(): array
    {
        // 기본 통계
        $totalOrganizations = Organization::count();
        $totalUsers = User::count();
        $activeOrganizations = Organization::where('status', 'active')->count();
        
        // 이번 달 신규 가입
        $newOrganizationsThisMonth = Organization::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
            
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // 결제 통계
        $totalRevenue = BillingHistory::where('status', 'DONE')->sum('amount');
        $revenueThisMonth = BillingHistory::where('status', 'DONE')
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->sum('amount');

        // 포인트 통계
        $totalPointsIssued = PointTransaction::where('transaction_type', 'earn')
            ->sum('amount');
        $totalPointsUsed = PointTransaction::where('transaction_type', 'spend')
            ->sum('amount');

        return [
            'organizations' => [
                'total' => $totalOrganizations,
                'active' => $activeOrganizations,
                'new_this_month' => $newOrganizationsThisMonth,
            ],
            'users' => [
                'total' => $totalUsers,
                'new_this_month' => $newUsersThisMonth,
            ],
            'revenue' => [
                'total' => $totalRevenue,
                'this_month' => $revenueThisMonth,
            ],
            'points' => [
                'total_issued' => $totalPointsIssued,
                'total_used' => abs($totalPointsUsed),
                'outstanding' => $totalPointsIssued + $totalPointsUsed, // spend는 음수이므로 +
            ]
        ];
    }

    private function getMonthlyStatistics(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'label' => $date->format('Y-m'),
                'organizations' => Organization::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'users' => User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'revenue' => BillingHistory::where('status', 'DONE')
                    ->whereYear('approved_at', $date->year)
                    ->whereMonth('approved_at', $date->month)
                    ->sum('amount'),
            ];
        }

        return $months;
    }

    private function getRecentActivities(): array
    {
        $activities = [];
        
        // 최근 조직 생성
        $recentOrganizations = Organization::with('owner')
            ->latest()
            ->take(5)
            ->get();
            
        foreach ($recentOrganizations as $org) {
            $activities[] = [
                'type' => 'organization_created',
                'title' => '새 조직 생성',
                'description' => "{$org->name} 조직이 생성되었습니다.",
                'user' => $org->owner?->name,
                'created_at' => $org->created_at,
                'icon' => 'building',
                'color' => 'blue'
            ];
        }

        // 최근 결제
        $recentPayments = BillingHistory::with('organization')
            ->where('status', 'DONE')
            ->latest('approved_at')
            ->take(5)
            ->get();
            
        foreach ($recentPayments as $payment) {
            $activities[] = [
                'type' => 'payment_completed',
                'title' => '결제 완료',
                'description' => "{$payment->organization->name}에서 " . number_format($payment->amount) . "원 결제",
                'user' => $payment->organization->name,
                'created_at' => $payment->approved_at,
                'icon' => 'credit-card',
                'color' => 'green'
            ];
        }

        // 최근 포인트 거래
        $recentPoints = PointTransaction::with('organization')
            ->latest()
            ->take(5)
            ->get();
            
        foreach ($recentPoints as $point) {
            $activities[] = [
                'type' => 'point_transaction',
                'title' => $point->getTransactionTypeText() . ' 포인트',
                'description' => "{$point->organization->name}에서 " . $point->getFormattedAmount(),
                'user' => $point->organization->name,
                'created_at' => $point->created_at,
                'icon' => 'star',
                'color' => $point->amount >= 0 ? 'yellow' : 'red'
            ];
        }

        // 시간순으로 정렬
        usort($activities, function($a, $b) {
            return $b['created_at']->timestamp <=> $a['created_at']->timestamp;
        });

        return array_slice($activities, 0, 15);
    }
}