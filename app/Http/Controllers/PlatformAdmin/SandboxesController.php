<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SandboxesController extends Controller
{
    public function list()
    {
        return view('900-page-platform-admin.907-sandboxes.000-list.000-index');
    }

    public function templates()
    {
        return view('900-page-platform-admin.907-sandboxes.100-templates.000-index');
    }

    public function usage()
    {
        return view('900-page-platform-admin.907-sandboxes.200-usage.000-index');
    }

    public function settings()
    {
        return view('900-page-platform-admin.907-sandboxes.300-settings.000-index');
    }
}