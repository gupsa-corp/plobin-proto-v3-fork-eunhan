<?php

namespace App\Http\Controllers\PlatformAdmin\Users;


use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class Controller extends \App\Http\Controllers\Controller
{
    public function list(Request $request)
    {
        $query = User::with(['organizations'])->withCount('organizations');

        // 검색
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate($request->get('per_page', 15));
        
        // 각 사용자를 배열 형태로 변환
        $users->getCollection()->transform(function ($user) {
            return $user->toArrayWithRole();
        });

        return view('900-page-platform-admin.903-users.000-list.000-index', [
            'users' => $users,
            'filters' => [
                'search' => $request->get('search', ''),
            ]
        ]);
    }

    public function details(User $user)
    {
        $user->load(['organizations', 'organizationMembers.organization']);

        return view('900-page-platform-admin.903-users.100-details.000-index', [
            'user' => $user
        ]);
    }

    public function activityLogs(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        // 사용자 필터
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', User::class);
        }

        // 액션 타입 필터
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // 날짜 필터
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // 검색
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('log_name', 'LIKE', "%{$search}%");
            });
        }

        $activities = $query->paginate($request->get('per_page', 20));

        // 사용자 목록 (필터용)
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        // 이벤트 타입 목록
        $eventTypes = Activity::distinct('event')
            ->whereNotNull('event')
            ->pluck('event')
            ->filter()
            ->sort()
            ->values();

        return view('900-page-platform-admin.903-users.200-activity-logs.000-index', [
            'activities' => $activities,
            'users' => $users,
            'eventTypes' => $eventTypes,
            'filters' => [
                'user_id' => $request->get('user_id', ''),
                'event' => $request->get('event', ''),
                'date_from' => $request->get('date_from', ''),
                'date_to' => $request->get('date_to', ''),
                'search' => $request->get('search', ''),
            ]
        ]);
    }

    public function reports(Request $request)
    {
        return view('900-page-platform-admin.903-users.300-reports.000-index', [
            'reports' => []
        ]);
    }
}