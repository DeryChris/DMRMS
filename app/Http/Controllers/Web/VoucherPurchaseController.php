<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\VoucherPurchaseMail;
use App\Models\Cycle;
use App\Models\Voucher;
use App\Models\Payment;
use App\Services\Payment\PaystackService;
use App\Services\Notification\NotificationService;
use App\Services\Voucher\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class VoucherPurchaseController extends Controller
{
    /**
     * Maps short frontend provider codes to Paystack Charge API provider values.
     */
    private const MOMO_PROVIDER_MAP = [
        'mtn' => 'mtn',
        'atl' => 'airteltigo',
        'vod' => 'vodafone',
    ];

    public function __construct(
        private VoucherService $voucherService,
        private PaystackService $paystack,
        private NotificationService $notificationService,
    ) {}

    /**
     * Show the purchase form.
     */
    public function showPurchaseForm(Request $request): View
    {
        $activeCycles = Cycle::where('status', 'active')
            ->orderBy('start_date', 'desc')
            ->get();

        $paymentMethods = [
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Debit/Credit Card',
            'bank_deposit' => 'Bank Deposit',
        ];

        $unsplashPhoto = unsplash_hero();
        $selectedCycleId = $request->query('cycle_id');

        return view('public.buy-voucher', compact('activeCycles', 'paymentMethods', 'unsplashPhoto', 'selectedCycleId'));
    }

    /**
     * Purchase voucher — for offline method (bank_deposit) only.
     * Paystack channels use the AJAX initPayment() endpoint instead.
     */
    public function purchase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cycle_id' => ['required', 'exists:cycles,id,status,active'],
            'purchaser_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÖØ-öø-ÿ\s\'\-]+$/u'],
            'purchaser_email' => ['required', 'email', 'max:100'],
            'purchaser_phone' => ['required', 'string', 'digits:10'],
            'payment_method' => ['required', 'string', 'in:mobile_money,bank_transfer,card,bank_deposit'],
        ]);

        // Only bank_deposit is allowed via this full-POST flow
        if ($validated['payment_method'] !== 'bank_deposit') {
            return back()
                ->withErrors(['payment_method' => 'Online payment methods must use the in-page payment form.'])
                ->withInput();
        }

        $cycle = Cycle::findOrFail($validated['cycle_id']);

        // Duplicate check
        $existing = Voucher::where('cycle_id', $cycle->id)
            ->where('purchaser_email', $validated['purchaser_email'])
            ->whereIn('payment_status', ['completed', 'pending'])
            ->first();

        if ($existing) {
            return back()
                ->withErrors(['purchaser_email' => 'You have already purchased a voucher for this recruitment cycle.'])
                ->withInput();
        }

        // Generate voucher and mark as completed immediately (offline payment)
        $vouchers = $this->voucherService->generate($cycle->id, 1);
        $voucher = $vouchers[0];

        $cost = $cycle->voucher_price ?? Config::get('recruitment.voucher_costs.regular', 0);

        $voucher->update([
            'purchaser_name' => $validated['purchaser_name'],
            'purchaser_email' => $validated['purchaser_email'],
            'purchaser_phone' => $validated['purchaser_phone'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => 'completed',
            'cost' => $cost,
        ]);

        // Send email
        try {
            Mail::to($voucher->purchaser_email)->send(new VoucherPurchaseMail($voucher));
        } catch (\Throwable $e) {
            Log::warning('Failed to send voucher purchase email: ' . $e->getMessage());
        }

        // Send SMS with voucher details
        try {
            $smsMessage = "GAF Voucher: Serial {$voucher->serial_number}, PIN {$voucher->pin_code}. Valid until {$voucher->expires_at?->format('M d, Y')}. Register at " . config('app.url') . "/applicant/register";
            $this->notificationService->sendSms($voucher->purchaser_phone, $smsMessage);
        } catch (\Throwable $e) {
            Log::warning('Failed to send voucher SMS: ' . $e->getMessage());
        }

        return redirect()->route('voucher.confirmation', $voucher)
            ->with('success', 'Voucher purchased successfully!');
    }

    /**
     * Initialize a Paystack payment (AJAX).
     * Creates a Payment record (NO voucher yet — voucher is created only after
     * Paystack confirms success). Accepts form data + channel-specific fields.
     */
    public function initPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cycle_id' => ['required', 'exists:cycles,id,status,active'],
            'purchaser_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÖØ-öø-ÿ\s\'\-]+$/u'],
            'purchaser_email' => ['required', 'email', 'max:100'],
            'purchaser_phone' => ['required', 'string', 'digits:10'],
            'payment_method' => ['required', 'string', 'in:mobile_money,card,bank_transfer'],

            // Mobile Money specific
            'momo_provider' => ['required_if:payment_method,mobile_money', 'nullable', 'string', 'in:mtn,atl,vod'],
            'momo_phone' => ['required_if:payment_method,mobile_money', 'nullable', 'string', 'digits:10'],

            // Bank Transfer specific
            'bank_code' => ['required_if:payment_method,bank_transfer', 'nullable', 'string', 'max:10'],

            // Card — no pre-auth code needed; PaystackPop handles card entry client-side
        ]);

        $cycle = Cycle::findOrFail($validated['cycle_id']);

        // Duplicate check against Payment table (no voucher exists yet)
        $existing = Payment::where('payer_email', $validated['purchaser_email'])
            ->where('metadata->cycle_id', $cycle->id)
            ->whereIn('status', ['pending', 'processing', 'success'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already started a payment for this recruitment cycle. Please wait for it to complete.',
                'error_field' => 'purchaser_email',
            ], 422);
        }

        $cost = $cycle->voucher_price ?? Config::get('recruitment.voucher_costs.regular', 0);
        $reference = 'DMRMS-' . strtoupper(\Illuminate\Support\Str::random(12));

        // 1. Create payment record only (no voucher until Paystack confirms)
        $payment = Payment::create([
            'voucher_id' => null,
            'paystack_reference' => $reference,
            'amount' => $cost,
            'currency' => 'GHS',
            'channel' => $validated['payment_method'],
            'status' => 'pending',
            'payer_name' => $validated['purchaser_name'],
            'payer_email' => $validated['purchaser_email'],
            'payer_phone' => $validated['purchaser_phone'],
            'momo_provider' => $validated['momo_provider'] ?? null,
            'momo_phone' => $validated['momo_phone'] ?? null,
            'ip_address' => $request->ip(),
            'metadata' => [
                'cycle_id' => $cycle->id,
                'cycle_name' => $cycle->name,
            ],
        ]);

        // 2. Build Paystack Charge API params
        $chargeParams = [
            'amount' => $cost,  // will be converted to pesewas in service
            'email' => $validated['purchaser_email'],
            'reference' => $reference,
            'metadata' => [
                'payment_id' => $payment->id,
                'payer_name' => $validated['purchaser_name'],
            ],
        ];

        // Channel-specific params
        match ($validated['payment_method']) {
            'mobile_money' => $chargeParams['mobile_money'] = [
                'phone' => $validated['momo_phone'],
                'provider' => self::MOMO_PROVIDER_MAP[$validated['momo_provider']] ?? $validated['momo_provider'],
            ],
            'card' => null, // No Charge API call for card — handled by PaystackPop client-side
            'bank_transfer' => $chargeParams['bank'] = [
                'bank' => $validated['bank_code'],
            ],
            default => null,
        };

        // 3. For card, skip Paystack Charge API — PaystackPop handles it client-side
        if ($validated['payment_method'] !== 'card') {
            $result = $this->paystack->initializeCharge($chargeParams);

            if (!$result['success']) {
                $payment->update([
                    'status' => 'failed',
                    'paystack_status' => 'failed',
                    'gateway_response' => $result['gateway_response'] ?? $result['message'],
                    'paystack_response' => $result['data'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Payment initialization failed. Please try again.',
                    'gateway_response' => $result['gateway_response'] ?? null,
                ], 422);
            }

            // Update payment with response data
            $chargeData = $result['data'];
            $chargeStatus = $chargeData['status'] ?? 'unknown';

            $payment->update([
                'paystack_access_code' => $chargeData['access_code'] ?? null,
                'paystack_status' => $chargeStatus,
                'paystack_response' => $chargeData,
                'gateway_response' => $result['gateway_response'] ?? $chargeData['gateway_response'] ?? null,
                'status' => match ($chargeStatus) {
                    'success' => 'success',
                    'pay_offline' => 'processing',
                    'send_otp' => 'processing',
                    default => 'processing',
                },
            ]);

            // Handle channel-specific response data
            if ($validated['payment_method'] === 'bank_transfer' && isset($chargeData['bank'])) {
                $payment->update([
                    'bank_name' => $chargeData['bank']['name'] ?? null,
                    'bank_account_number' => $chargeData['bank']['account_number'] ?? null,
                    'bank_account_name' => $chargeData['bank']['account_name'] ?? null,
                    'bank_transfer_deadline' => isset($chargeData['bank']['deadline'])
                        ? now()->parse($chargeData['bank']['deadline'])
                        : now()->addHours(24),
                ]);
            }

            // If Paystack already confirms success (rare — possible for bank_transfer with saved method)
            if ($chargeStatus === 'success') {
                $payment->update([
                    'status' => 'success',
                    'paid_at' => now(),
                ]);
                // Create the voucher now that payment succeeded
                $payment->createOrActivateVoucher($cycle);
            }
        } else {
            // Card: no Charge API call yet — PaystackPop will process it
            $payment->update([
                'paystack_status' => 'pending',
                'paystack_response' => ['status' => 'awaiting_card'],
            ]);
        }

        // 4. Return response
        $responseData = [
            'success' => true,
            'payment_id' => $payment->id,
            'reference' => $reference,
            'status' => $payment->fresh()->status,
            'paystack_status' => $payment->paystack_status,
            'gateway_response' => $payment->gateway_response,
            'display_text' => $chargeData['display_text'] ?? null,
        ];

        // Include bank details for bank transfer
        if ($validated['payment_method'] === 'bank_transfer' && $payment->bank_account_number) {
            $responseData['bank_details'] = [
                'bank_name' => $payment->bank_name,
                'account_number' => $payment->bank_account_number,
                'account_name' => $payment->bank_account_name,
                'amount' => number_format($cost, 2),
                'deadline' => $payment->bank_transfer_deadline?->format('M d, Y H:i'),
            ];
        }

        // Include voucher info if it was immediately created (success status from Paystack)
        if ($payment->voucher_id) {
            $voucher = $payment->voucher;
            $responseData['voucher_id'] = $voucher->id;
            $responseData['serial_number'] = $voucher->serial_number;
            $responseData['pin_code'] = $voucher->pin_code;
        }

        return response()->json($responseData);
    }

    /**
     * Poll payment status (AJAX).
     * Uses Payment (not Voucher) because voucher is created only after success.
     */
    public function paymentStatus(Payment $payment): JsonResponse
    {
        $response = [
            'status' => $payment->status,
            'paystack_status' => $payment->paystack_status,
            'gateway_response' => $payment->gateway_response,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'amount' => $payment->amount,
            'channel' => $payment->channel,
        ];

        // If still processing, check with Paystack API
        if (in_array($payment->status, ['pending', 'processing'])) {
            $checkResult = $this->paystack->checkChargeStatus($payment->paystack_reference);

            if ($checkResult['success']) {
                $apiStatus = $checkResult['status'];

                if ($apiStatus === 'success') {
                    // Verify server-side
                    $verified = $this->paystack->verifyTransaction($payment->paystack_reference);
                    if ($verified['success'] && $verified['verified']) {
                        $payment->update([
                            'status' => 'success',
                            'paystack_status' => 'success',
                            'gateway_response' => $verified['gateway_response'],
                            'paystack_response' => $verified['data'],
                            'fees' => $verified['fees'],
                            'paid_at' => now(),
                        ]);

                        // Create the voucher NOW — payment is confirmed
                        $cycle = Cycle::find($payment->metadata['cycle_id'] ?? null);
                        if ($cycle) {
                            $voucher = $payment->createOrActivateVoucher($cycle);
                            $response['voucher_id'] = $voucher->id;
                            $response['serial_number'] = $voucher->serial_number;
                            $response['pin_code'] = $voucher->pin_code;
                        }
                    }
                } elseif (in_array($apiStatus, ['failed', 'reversed', 'expired'])) {
                    $payment->update([
                        'status' => 'failed',
                        'paystack_status' => $apiStatus,
                    ]);
                }

                $response['status'] = $payment->fresh()->status;
                $response['paystack_status'] = $apiStatus;
            }
        }

        // If already success but no voucher yet (edge case), create it
        if ($payment->status === 'success' && !$payment->voucher_id) {
            $cycle = Cycle::find($payment->metadata['cycle_id'] ?? null);
            if ($cycle) {
                $voucher = $payment->createOrActivateVoucher($cycle);
                $response['voucher_id'] = $voucher->id;
                $response['serial_number'] = $voucher->serial_number;
                $response['pin_code'] = $voucher->pin_code;
            }
        }

        return response()->json($response);
    }

    /**
     * Submit OTP for Telecel/Vodafone flow (AJAX).
     */
    public function submitOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:100'],
            'otp' => ['required', 'string', 'max:10'],
        ]);

        $payment = Payment::where('paystack_reference', $validated['reference'])->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment record not found.',
            ], 404);
        }

        $result = $this->paystack->submitOtp($validated['reference'], $validated['otp']);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'OTP submission failed.',
            ], 422);
        }

        // If OTP submission succeeded and charge is now successful, create voucher
        if ($result['status'] === 'success') {
            $cycle = Cycle::find($payment->metadata['cycle_id'] ?? null);
            if ($cycle) {
                $payment->createOrActivateVoucher($cycle);
            }
        }

        return response()->json([
            'success' => true,
            'status' => $result['status'],
            'gateway_response' => $result['gateway_response'],
        ]);
    }

    /**
     * Lookup voucher by email.
     */
    public function lookupVoucher(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'lookup_email' => 'required|email',
        ]);

        $lookupResults = Voucher::where('purchaser_email', $validated['lookup_email'])
            ->with('cycle')
            ->orderBy('created_at', 'desc')
            ->get();

        // AJAX response — used by the frontend overlay
        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'email'    => $validated['lookup_email'],
                'vouchers' => $lookupResults->map(fn($v) => [
                    'id'            => $v->id,
                    'cycle_name'    => $v->cycle?->name ?? 'N/A',
                    'serial_number' => $v->serial_number,
                    'pin_code'      => $v->pin_code,
                    'status'        => $v->status,
                    'purchased_at'  => $v->purchased_at?->format('d M Y'),
                    'expires_at'    => $v->expires_at?->format('d M Y'),
                ]),
            ]);
        }

        $activeCycles = Cycle::where('status', 'active')
            ->orderBy('start_date', 'desc')
            ->get();

        $paymentMethods = [
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Debit/Credit Card',
            'bank_deposit' => 'Bank Deposit',
        ];

        $unsplashPhoto = unsplash_hero();

        return view('public.buy-voucher',
            compact('activeCycles', 'paymentMethods', 'unsplashPhoto', 'lookupResults'));
    }

    /**
     * Show voucher confirmation page.
     */
    public function confirmation(Voucher $voucher): View
    {
        if ($voucher->payment_status !== 'completed') {
            return redirect()->route('voucher.buy')->with('error', 'Invalid voucher.');
        }

        $unsplashPhoto = unsplash_hero();
        $payment = $voucher->payment;

        return view('public.voucher-confirmation', compact('voucher', 'payment', 'unsplashPhoto'));
    }

    /**
     * Generate a unique voucher serial number.
     */
    private function generateUniqueSerial(): string
    {
        do {
            $serial = 'DMRMS-' . strtoupper(\Illuminate\Support\Str::random(8));
        } while (Voucher::where('serial_number', $serial)->exists());

        return $serial;
    }

    /**
     * Generate a random PIN code (10 chars, no ambiguous characters).
     */
    private function generatePin(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        return substr(str_shuffle(str_repeat($chars, 10)), 0, 10);
    }
}
