<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function overview()
    {
        return view('900-page-platform-admin.906-pricing.000-overview.000-index');
    }

    public function plans()
    {
        return view('900-page-platform-admin.906-pricing.100-plans.000-index');
    }

    public function subscriptions()
    {
        return view('900-page-platform-admin.906-pricing.200-subscriptions.000-index');
    }

    public function analytics()
    {
        return view('900-page-platform-admin.906-pricing.300-analytics.000-index');
    }
}