<?php

namespace App\Services\Payment;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    private string $baseUrl = 'https://api.paystack.co';

    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
            'Content-Type' => 'application/json',
        ])->timeout(30);
    }

    /**
     * Initialize a charge via the Charge API.
     *
     * @param array $params  Requires: amount (GHS), email, reference.
     *                       Optional: mobile_money, authorization_code, bank
     */
    public function initializeCharge(array $params): array
    {
        // Amount from controller is in GHS — convert to pesewas (GHS × 100)
        if (isset($params['amount'])) {
            $params['amount'] = $this->toPesewas((float) $params['amount']);
        }

        $params['currency'] = 'GHS';

        Log::info('Paystack Charge API: initializing charge', [
            'reference' => $params['reference'] ?? null,
            'amount' => $params['amount'],
            'email' => $params['email'],
        ]);

        $response = $this->client()->post($this->baseUrl . '/charge', $params);

        $body = $response->json();

        if (!$response->successful() || !($body['status'] ?? false)) {
            Log::error('Paystack Charge API failed', [
                'reference' => $params['reference'] ?? null,
                'status' => $response->status(),
                'response' => $body,
            ]);

            return [
                'success' => false,
                'message' => $body['data']['message']
                    ?? $body['data']['gateway_response']
                    ?? $body['message']
                    ?? 'Payment initialization failed',
                'gateway_response' => $body['data']['gateway_response']
                    ?? $body['data']['message']
                    ?? null,
                'data' => $body['data'] ?? null,
            ];
        }

        return [
            'success' => true,
            'message' => $body['message'] ?? 'Charge attempted',
            'gateway_response' => $body['data']['gateway_response'] ?? null,
            'data' => $body['data'],
        ];
    }

    /**
     * Check the status of a charge via the Charge API.
     * Used for polling.
     */
    public function checkChargeStatus(string $reference): array
    {
        $response = $this->client()->get($this->baseUrl . "/charge/{$reference}");

        $body = $response->json();

        if (!$response->successful()) {
            Log::warning('Paystack Charge status check failed', [
                'reference' => $reference,
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'message' => $body['message'] ?? 'Status check failed',
            ];
        }

        $data = $body['data'] ?? [];

        return [
            'success' => true,
            'status' => $data['status'] ?? 'unknown',
            'gateway_response' => $data['gateway_response'] ?? null,
            'data' => $data,
        ];
    }

    /**
     * Verify a transaction via the Transaction API.
     * This is the definitive verification endpoint.
     */
    public function verifyTransaction(string $reference): array
    {
        $response = $this->client()->get($this->baseUrl . "/transaction/verify/{$reference}");

        $body = $response->json();

        if (!$response->successful() || !($body['status'] ?? false)) {
            Log::warning('Paystack transaction verification failed', [
                'reference' => $reference,
                'status' => $response->status(),
                'response' => $body,
            ]);

            return [
                'success' => false,
                'verified' => false,
                'message' => $body['message'] ?? 'Verification failed',
                'status' => $body['data']['status'] ?? 'unknown',
            ];
        }

        $data = $body['data'];

        return [
            'success' => true,
            'verified' => ($data['status'] ?? '') === 'success',
            'status' => $data['status'] ?? 'unknown',
            'gateway_response' => $data['gateway_response'] ?? null,
            'amount' => $this->fromPesewas($data['amount'] ?? 0),
            'channel' => $data['channel'] ?? null,
            'paid_at' => $data['paid_at'] ?? null,
            'fees' => $this->fromPesewas($data['fees'] ?? 0),
            'customer_email' => $data['customer']['email'] ?? null,
            'data' => $data,
        ];
    }

    /**
     * Submit OTP for Telecel/Vodafone flow.
     */
    public function submitOtp(string $reference, string $otp): array
    {
        $response = $this->client()->post($this->baseUrl . '/charge/submit_otp', [
            'reference' => $reference,
            'otp' => $otp,
        ]);

        $body = $response->json();

        if (!$response->successful() || !($body['status'] ?? false)) {
            return [
                'success' => false,
                'message' => $body['message'] ?? 'OTP submission failed',
                'data' => $body['data'] ?? null,
            ];
        }

        return [
            'success' => true,
            'status' => $body['data']['status'] ?? 'unknown',
            'gateway_response' => $body['data']['gateway_response'] ?? null,
            'data' => $body['data'],
        ];
    }

    /**
     * Verify incoming Paystack webhook signature.
     * Uses HMAC-SHA512 with the secret key.
     */
    public function verifyWebhookSignature(string $rawBody, string $signature): bool
    {
        $secret = config('services.paystack.secret_key');
        $computed = hash_hmac('sha512', $rawBody, $secret);

        return hash_equals($computed, $signature);
    }

    /**
     * Get list of banks for Bank Transfer.
     */
    public function getBanks(string $country = 'ghana'): array
    {
        $response = $this->client()->get($this->baseUrl . '/bank', [
            'country' => $country,
            'currency' => 'GHS',
        ]);

        $body = $response->json();

        if (!$response->successful() || !($body['status'] ?? false)) {
            Log::warning('Paystack: failed to fetch banks', [
                'status' => $response->status(),
            ]);

            return [];
        }

        return $body['data'] ?? [];
    }

    /**
     * Convert GHS to pesewas (GHS * 100).
     */
    public function toPesewas(float $amountGhs): int
    {
        return (int) round($amountGhs * 100);
    }

    /**
     * Convert pesewas to GHS (pesewas / 100).
     */
    public function fromPesewas(int $pesewas): float
    {
        return $pesewas / 100;
    }
}
