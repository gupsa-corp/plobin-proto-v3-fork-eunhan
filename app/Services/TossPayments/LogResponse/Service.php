<?php

namespace App\Services\TossPayments\LogResponse;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __invoke(string $method, Response $response): void
    {
        Log::info("Toss Payments API {$method}", [
            'status' => $response->status(),
            'response' => $response->successful() ? 'SUCCESS' : 'FAILED',
            'body' => $response->body(),
        ]);
    }
}