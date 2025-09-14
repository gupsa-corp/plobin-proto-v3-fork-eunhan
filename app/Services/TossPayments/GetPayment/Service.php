<?php

namespace App\Services\TossPayments\GetPayment;

use Illuminate\Support\Facades\Http;

class Service
{
    public function __invoke(string $paymentKey): array
    {
        $baseUrl = config('services.toss.api_url', 'https://api.tosspayments.com');
        $secretKey = config('services.toss.secret_key');
        $headers = [
            'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
            'Content-Type' => 'application/json',
        ];

        $url = "{$baseUrl}/v1/payments/{$paymentKey}";

        $response = Http::withHeaders($headers)->get($url);

        $logResponseService = app(\App\Services\TossPayments\LogResponse\Service::class);
        $logResponseService('getPayment', $response);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Payment retrieval failed: ' . $response->body());
    }
}