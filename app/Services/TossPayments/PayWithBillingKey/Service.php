<?php

namespace App\Services\TossPayments\PayWithBillingKey;

use Illuminate\Support\Facades\Http;

class Service
{
    public function __invoke(
        string $billingKey,
        string $customerKey,
        int $amount,
        string $orderId,
        string $orderName
    ): array {
        $baseUrl = config('services.toss.api_url', 'https://api.tosspayments.com');
        $secretKey = config('services.toss.secret_key');
        $headers = [
            'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
            'Content-Type' => 'application/json',
        ];

        $url = "{$baseUrl}/v1/billing/{$billingKey}";

        $response = Http::withHeaders($headers)->post($url, [
            'customerKey' => $customerKey,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderName' => $orderName,
        ]);

        $logResponseService = app(\App\Services\TossPayments\LogResponse\Service::class);
        $logResponseService('payWithBillingKey', $response);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Billing payment failed: ' . $response->body());
    }
}