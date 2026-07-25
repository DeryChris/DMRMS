<?php

namespace App\Models;

use App\Mail\VoucherPurchaseMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'voucher_id',
        'paystack_reference',
        'paystack_access_code',
        'amount',
        'currency',
        'channel',
        'status',
        'payer_name',
        'payer_email',
        'payer_phone',
        'momo_provider',
        'momo_phone',
        'card_last4',
        'card_brand',
        'card_exp_month',
        'card_exp_year',
        'authorization_code',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bank_transfer_deadline',
        'paystack_status',
        'gateway_response',
        'paystack_response',
        'fees',
        'metadata',
        'ip_address',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fees' => 'decimal:2',
            'paid_at' => 'datetime',
            'bank_transfer_deadline' => 'datetime',
            'paystack_response' => 'json',
            'metadata' => 'json',
            'card_exp_month' => 'integer',
            'card_exp_year' => 'integer',
        ];
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Generate and link a voucher after successful payment.
     * Idempotent — safe to call multiple times.
     */
    public function createOrActivateVoucher(Cycle $cycle): Voucher
    {
        // Idempotency — voucher already exists
        if ($this->voucher_id) {
            return $this->voucher;
        }

        // Generate unique serial + pin
        do {
            $serial = 'DMRMS-' . strtoupper(Str::random(8));
        } while (Voucher::where('serial_number', $serial)->exists());

        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $pin = substr(str_shuffle(str_repeat($chars, 10)), 0, 10);

        $voucher = Voucher::create([
            'cycle_id' => $cycle->id,
            'serial_number' => $serial,
            'pin_code' => $pin,
            'purchaser_name' => $this->payer_name,
            'purchaser_email' => $this->payer_email,
            'purchaser_phone' => $this->payer_phone,
            'payment_method' => $this->channel,
            'payment_status' => 'completed',
            'payment_id' => $this->id,
            'cost' => $this->amount,
            'status' => 'available',
            'purchased_at' => now(),
            'expires_at' => $cycle->application_deadline ?? now()->addMonths(3),
        ]);

        // Link payment → voucher
        $this->update(['voucher_id' => $voucher->id]);

        // Send email
        try {
            Mail::to($voucher->purchaser_email)->send(new VoucherPurchaseMail($voucher));
        } catch (\Throwable $e) {
            Log::warning('Failed to send voucher purchase email: ' . $e->getMessage());
        }

        Log::info('Voucher created and activated from payment', [
            'voucher_id' => $voucher->id,
            'payment_id' => $this->id,
            'serial' => $serial,
        ]);

        return $voucher;
    }

    /**
     * Channel display names
     */
    public static function channelLabel(string $channel): string
    {
        return match ($channel) {
            'mobile_money' => 'Mobile Money',
            'card' => 'Debit/Credit Card',
            'bank_transfer' => 'Bank Transfer',
            'bank_deposit' => 'Bank Deposit',
            default => ucfirst(str_replace('_', ' ', $channel)),
        };
    }

    /**
     * MoMo provider display names
     */
    public static function momoProviderLabel(?string $provider): string
    {
        return match ($provider) {
            'mtn' => 'MTN MoMo',
            'atl' => 'AirtelTigo Money',
            'vod' => 'Telecel Cash (Vodafone)',
            default => $provider ?? 'N/A',
        };
    }
}
