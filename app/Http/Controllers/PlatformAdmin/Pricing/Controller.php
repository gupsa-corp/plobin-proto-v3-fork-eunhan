<?php

namespace App\Http\Controllers\PlatformAdmin\Pricing;


use Illuminate\Http\Request;

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
}