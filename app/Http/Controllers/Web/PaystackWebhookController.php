<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cycle;
use App\Models\Payment;
use App\Services\Payment\PaystackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    public function __construct(
        private PaystackService $paystack
    ) {}

    /**
     * Handle incoming Paystack webhook.
     * Route: POST /api/webhooks/paystack (no CSRF)
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        // 1. Verify signature
        $signature = $request->header('x-paystack-signature');
        $rawBody = $request->getContent();

        if (!$signature || !$this->paystack->verifyWebhookSignature($rawBody, $signature)) {
            Log::warning('Paystack webhook: invalid signature');
            return response()->json(['status' => 'invalid signature'], 403);
        }

        // 2. Parse event
        $payload = json_decode($rawBody, true);
        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];
        $reference = $data['reference'] ?? '';

        Log::info('Paystack webhook received', [
            'event' => $event,
            'reference' => $reference,
        ]);

        // 3. Handle by event type
        try {
            match ($event) {
                'charge.success' => $this->handleChargeSuccess($reference),
                'charge.failed' => $this->handleChargeFailed($reference),
                default => Log::info('Paystack webhook: unhandled event', ['event' => $event]),
            };
        } catch (\Throwable $e) {
            Log::error('Paystack webhook processing error', [
                'event' => $event,
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
        }

        // 4. Always return 200 to acknowledge receipt
        return response()->json(['status' => 'ok']);
    }

    private function handleChargeSuccess(string $reference): void
    {
        // Find payment by reference
        $payment = Payment::where('paystack_reference', $reference)->first();
        if (!$payment) {
            Log::warning('Paystack webhook: payment not found', ['reference' => $reference]);
            return;
        }

        // Idempotency — payment already processed
        if ($payment->status === 'success') {
            Log::info('Paystack webhook: payment already processed', ['reference' => $reference]);
            return;
        }

        // Double-verify with Transaction API
        $verified = $this->paystack->verifyTransaction($reference);
        if (!$verified['success'] || !$verified['verified']) {
            Log::error('Paystack webhook: verification failed', [
                'reference' => $reference,
                'verified_status' => $verified['status'] ?? 'unknown',
            ]);
            $payment->update([
                'status' => 'failed',
                'paystack_status' => $verified['status'] ?? 'verification_failed',
                'gateway_response' => $verified['message'] ?? 'Verification failed',
            ]);
            return;
        }

        // Update payment
        $payment->update([
            'status' => 'success',
            'paystack_status' => $verified['status'],
            'gateway_response' => $verified['gateway_response'],
            'paystack_response' => $verified['data'],
            'fees' => $verified['fees'],
            'paid_at' => $verified['paid_at'] ? now()->parse($verified['paid_at']) : now(),
        ]);

        // Create voucher if not already created (idempotent)
        $cycle = Cycle::find($payment->metadata['cycle_id'] ?? null);
        if ($cycle) {
            $payment->createOrActivateVoucher($cycle);
            Log::info('Voucher created/activated via Paystack webhook', [
                'payment_id' => $payment->id,
                'voucher_id' => $payment->fresh()->voucher_id,
                'reference' => $reference,
            ]);
        }
    }

    private function handleChargeFailed(string $reference): void
    {
        $payment = Payment::where('paystack_reference', $reference)->first();
        if (!$payment) {
            Log::warning('Paystack webhook: payment not found for failed charge', ['reference' => $reference]);
            return;
        }

        $payment->update([
            'status' => 'failed',
            'paystack_status' => 'failed',
        ]);

        Log::info('Payment marked as failed via webhook', ['reference' => $reference]);
    }
}
