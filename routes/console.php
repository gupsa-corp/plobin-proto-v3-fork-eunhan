<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 1시간마다 시스템 로깅
Schedule::command('system:hourly-log')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->emailOutputOnFailure(config('mail.admin_email', 'admin@example.com'))
    ->appendOutputTo(storage_path('logs/scheduler.log'));
