<?php

namespace App\Services\TossPayments\ConfirmPayment;

use Illuminate\Support\Facades\Http;

class Service
{
    public function __invoke(string $paymentKey, string $orderId, int $amount): array
    {
        $baseUrl = config('services.toss.api_url', 'https://api.tosspayments.com');
        $secretKey = config('services.toss.secret_key');
        $headers = [
            'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
            'Content-Type' => 'application/json',
        ];

        $url = "{$baseUrl}/v1/payments/confirm";

        $response = Http::withHeaders($headers)->post($url, [
            'paymentKey' => $paymentKey,
            'orderId' => $orderId,
            'amount' => $amount,
        ]);

        $logResponseService = app(\App\Services\TossPayments\LogResponse\Service::class);
        $logResponseService('confirmPayment', $response);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Payment confirmation failed: ' . $response->body());
    }
}