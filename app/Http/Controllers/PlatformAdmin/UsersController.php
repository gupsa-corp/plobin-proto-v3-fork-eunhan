<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function list(Request $request)
    {
        $query = User::with(['organizations']);

        // 검색
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate($request->get('per_page', 15));

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
        return view('900-page-platform-admin.903-users.200-activity-logs.000-index', [
            'activities' => []
        ]);
    }

    public function reports(Request $request)
    {
        return view('900-page-platform-admin.903-users.300-reports.000-index', [
            'reports' => []
        ]);
    }
}