<?php

namespace App\Console\Commands;

use App\Mail\VoucherPurchaseMail;
use App\Models\Payment;
use App\Models\Voucher;
use App\Services\Payment\PaystackService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VerifyStalePayments extends Command
{
    protected $signature = 'payments:verify-stale';
    protected $description = 'Verify and recover stale/pending payments with Paystack';

    public function handle(PaystackService $paystack): int
    {
        $this->info('Checking stale payments...');

        // Find payments stuck in 'processing' for more than 5 minutes
        $stalePayments = Payment::whereIn('status', ['pending', 'processing'])
            ->where('created_at', '<', now()->subMinutes(5))
            ->get();

        $count = 0;

        foreach ($stalePayments as $payment) {
            $this->line("Checking payment {$payment->id} (ref: {$payment->paystack_reference})...");

            try {
                $result = $paystack->verifyTransaction($payment->paystack_reference);

                if (!$result['success']) {
                    // Try charge status as fallback
                    $chargeResult = $paystack->checkChargeStatus($payment->paystack_reference);
                    $apiStatus = $chargeResult['status'] ?? 'unknown';
                } else {
                    $apiStatus = $result['status'];
                }

                if ($apiStatus === 'success') {
                    // Verify succeeded
                    if (!$result['success']) {
                        $result = $paystack->verifyTransaction($payment->paystack_reference);
                    }

                    $payment->update([
                        'status' => 'success',
                        'paystack_status' => 'success',
                        'gateway_response' => $result['gateway_response'] ?? 'Verified by stale check',
                        'paystack_response' => $result['data'] ?? null,
                        'fees' => $result['fees'] ?? null,
                        'paid_at' => $result['paid_at'] ? now()->parse($result['paid_at']) : now(),
                    ]);

                    $voucher = $payment->voucher;
                    if ($voucher && $voucher->payment_status !== 'completed') {
                        $voucher->update([
                            'payment_status' => 'completed',
                            'status' => 'available',
                            'payment_method' => $payment->channel,
                            'payment_reference' => $payment->paystack_reference,
                            'cost' => $payment->amount,
                            'purchased_at' => now(),
                        ]);

                        try {
                            Mail::to($voucher->purchaser_email)->send(new VoucherPurchaseMail($voucher));
                        } catch (\Throwable $e) {
                            Log::warning('Stale payment: failed to send email', ['error' => $e->getMessage()]);
                        }

                        $this->info("Payment {$payment->id} recovered — voucher activated.");
                        $count++;
                    }
                } elseif (in_array($apiStatus, ['failed', 'expired', 'reversed'])) {
                    $payment->update([
                        'status' => 'failed',
                        'paystack_status' => $apiStatus,
                    ]);

                    if ($payment->voucher_id) {
                        Voucher::where('id', $payment->voucher_id)->update(['payment_status' => 'failed']);
                    }

                    $this->warn("Payment {$payment->id} marked as {$apiStatus}.");
                }

                // Handle bank transfer deadline expiration
                if ($payment->channel === 'bank_transfer' && $payment->bank_transfer_deadline && now()->gt($payment->bank_transfer_deadline)) {
                    if (in_array($payment->status, ['pending', 'processing'])) {
                        $payment->update(['status' => 'abandoned']);
                        if ($payment->voucher_id) {
                            Voucher::where('id', $payment->voucher_id)->update(['payment_status' => 'failed']);
                        }
                        $this->warn("Bank transfer payment {$payment->id} abandoned (deadline passed).");
                    }
                }

            } catch (\Throwable $e) {
                Log::error('Stale payment check failed', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Done. Recovered {$count} payments.");
        return Command::SUCCESS;
    }
}
