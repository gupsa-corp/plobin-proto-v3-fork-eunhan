<?php

namespace App\Http\Controllers\PlatformAdmin\Permissions;


use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

class Controller extends \App\Http\Controllers\Controller
{
    public function overview()
    {
        $organizations = Organization::orderBy('name')->get();
        $users = User::with('organizations')->paginate(15);
        
        return view('900-page-platform-admin.905-permissions.000-overview.000-index', [
            'organizations' => $organizations,
            'users' => $users
        ]);
    }

    public function roles()
    {
        return view('900-page-platform-admin.905-permissions.100-roles.000-index');
    }

    public function permissions()
    {
        return view('900-page-platform-admin.905-permissions.200-permissions.000-index');
    }

    public function users()
    {
        return view('900-page-platform-admin.905-permissions.300-users.000-index');
    }

    public function audit()
    {
        return view('900-page-platform-admin.905-permissions.400-audit.000-index');
    }

    public function auditDetails($id)
    {
        return view('900-page-platform-admin.905-permissions.400-audit.100-details.000-index', [
            'auditId' => $id
        ]);
    }

    // Legacy API methods (기존 코드와의 호환성)
    public function changeUserRole(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function toggleUserStatus(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function updateTenantPermissions(Request $request)
    {
        return response()->json(['success' => true]);
    }
}