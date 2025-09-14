<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Organization;
use App\Models\BillingHistory;
use App\Models\PaymentMethod;

class TossPaymentsService
{
    private string $baseUrl;
    private string $secretKey;
    private array $headers;

    public function __construct()
    {
        $this->baseUrl = config('services.toss.api_url', 'https://api.tosspayments.com');
        $this->secretKey = config('services.toss.secret_key');
        $this->headers = [
            'Authorization' => 'Basic ' . base64_encode($this->secretKey . ':'),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * 결제 승인
     */
    public function confirmPayment(string $paymentKey, string $orderId, int $amount): array
    {
        return app(\App\Services\TossPayments\ConfirmPayment\Service::class)($paymentKey, $orderId, $amount);
    }

    /**
     * 결제 조회
     */
    public function getPayment(string $paymentKey): array
    {
        return app(\App\Services\TossPayments\GetPayment\Service::class)($paymentKey);
    }

    /**
     * 결제 취소
     */
    public function cancelPayment(string $paymentKey, string $cancelReason, ?int $cancelAmount = null): array
    {
        $url = "{$this->baseUrl}/v1/payments/{$paymentKey}/cancel";
        
        $data = [
            'cancelReason' => $cancelReason,
        ];

        if ($cancelAmount !== null) {
            $data['cancelAmount'] = $cancelAmount;
        }

        $response = Http::withHeaders($this->headers)->post($url, $data);
        
        $this->logResponse('cancelPayment', $response);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Payment cancellation failed: ' . $response->body());
    }

    /**
     * 빌링키 발급 (자동결제용)
     */
    public function issueBillingKey(string $customerKey, string $authKey): array
    {
        $url = "{$this->baseUrl}/v1/billing/authorizations/issue";
        
        $response = Http::withHeaders($this->headers)->post($url, [
            'customerKey' => $customerKey,
            'authKey' => $authKey,
        ]);

        $this->logResponse('issueBillingKey', $response);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Billing key issuance failed: ' . $response->body());
    }

    /**
     * 빌링키로 결제
     */
    public function payWithBillingKey(
        string $billingKey, 
        string $customerKey, 
        int $amount, 
        string $orderId, 
        string $orderName
    ): array {
        $url = "{$this->baseUrl}/v1/billing/{$billingKey}";
        
        $response = Http::withHeaders($this->headers)->post($url, [
            'customerKey' => $customerKey,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderName' => $orderName,
        ]);

        $this->logResponse('payWithBillingKey', $response);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Billing payment failed: ' . $response->body());
    }

    /**
     * 정기결제 처리 (조직의 월간 구독)
     */
    public function processMonthlyBilling(Organization $organization): ?BillingHistory
    {
        return app(\App\Services\TossPayments\ProcessMonthlyBilling\Service::class)($organization);
    }

    /**
     * 웹훅 검증
     */
    public function verifyWebhook(string $signature, string $body): bool
    {
        $expectedSignature = base64_encode(hash_hmac('sha256', $body, $this->secretKey, true));
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * 결제 내역 업데이트 (웹훅용)
     */
    public function updateBillingHistoryFromWebhook(array $webhookData): void
    {
        $paymentKey = $webhookData['data']['paymentKey'] ?? null;
        if (!$paymentKey) {
            return;
        }

        $billingHistory = BillingHistory::where('payment_key', $paymentKey)->first();
        if (!$billingHistory) {
            Log::warning("Billing history not found for payment key: {$paymentKey}");
            return;
        }

        $billingHistory->update([
            'status' => $webhookData['data']['status'],
            'approved_at' => isset($webhookData['data']['approvedAt']) 
                ? new \DateTime($webhookData['data']['approvedAt']) 
                : null,
            'toss_response' => array_merge($billingHistory->toss_response ?? [], $webhookData['data']),
        ]);

        Log::info("Updated billing history {$billingHistory->id} from webhook");
    }

    /**
     * 응답 로깅
     */
    private function logResponse(string $method, Response $response): void
    {
        Log::info("Toss Payments API {$method}", [
            'status' => $response->status(),
            'response' => $response->successful() ? 'SUCCESS' : 'FAILED',
            'body' => $response->body(),
        ]);
    }

    /**
     * 고유한 주문 ID 생성
     */
    public function generateOrderId(string $prefix = 'order'): string
    {
        return $prefix . '_' . time() . '_' . mt_rand(1000, 9999);
    }

    /**
     * 영수증 URL 생성
     */
    public function generateReceiptUrl(string $paymentKey): string
    {
        return "{$this->baseUrl}/v1/payments/{$paymentKey}/receipt";
    }
}