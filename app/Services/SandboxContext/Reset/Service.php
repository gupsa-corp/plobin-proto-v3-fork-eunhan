<?php

namespace App\Services\SandboxContext\Reset;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class Service
{
    const SESSION_KEY = 'current_sandbox';

    public function __invoke(): void
    {
        Session::forget(self::SESSION_KEY);
        Log::info('Sandbox context reset to default');
    }
}