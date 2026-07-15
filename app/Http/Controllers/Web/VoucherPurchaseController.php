<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\VoucherPurchaseMail;
use App\Models\Cycle;
use App\Models\Voucher;
use App\Services\Voucher\VoucherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class VoucherPurchaseController extends Controller
{
    protected VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    public function showPurchaseForm(): View
    {
        $activeCycles = Cycle::where('status', 'active')
            ->where('application_deadline', '>', now())
            ->orderBy('start_date', 'desc')
            ->get();

        $paymentMethods = [
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Debit/Credit Card',
            'bank_deposit' => 'Bank Deposit',
        ];

        $unsplashPhoto = unsplash_hero();

        return view('public.buy-voucher', compact('activeCycles', 'paymentMethods', 'unsplashPhoto'));
    }

    public function purchase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cycle_id' => ['required', 'exists:cycles,id,status,active'],
            'purchaser_name' => ['required', 'string', 'max:100'],
            'purchaser_email' => ['required', 'email', 'max:100'],
            'purchaser_phone' => ['required', 'string', 'max:20'],
            'payment_method' => ['required', 'string', 'in:mobile_money,bank_transfer,card,bank_deposit'],
        ]);

        $cycle = Cycle::findOrFail($validated['cycle_id']);

        $existing = Voucher::where('cycle_id', $cycle->id)
            ->where('purchaser_email', $validated['purchaser_email'])
            ->whereIn('payment_status', ['completed', 'pending'])
            ->first();

        if ($existing) {
            return back()->withErrors(['purchaser_email' => 'You have already purchased a voucher for this recruitment cycle.'])->withInput();
        }

        $vouchers = $this->voucherService->generate($cycle->id, 1);
        $voucher = $vouchers[0];

        $voucher->update([
            'purchaser_name' => $validated['purchaser_name'],
            'purchaser_email' => $validated['purchaser_email'],
            'purchaser_phone' => $validated['purchaser_phone'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => 'completed',
            'cost' => $cycle->voucher_price ?? Config::get('recruitment.voucher_costs.regular', 0),
        ]);

        try {
            Mail::to($voucher->purchaser_email)->send(new VoucherPurchaseMail($voucher));
        } catch (\Throwable $e) {
            Log::warning('Failed to send voucher purchase email: ' . $e->getMessage());
        }

        try {
            $smsMessage = "GAF Voucher: Serial {$voucher->serial_number}, PIN {$voucher->pin_code}. Valid until {$voucher->expires_at?->format('M d, Y')}. Register at " . config('app.url') . "/applicant/register";
            Log::channel('sms')->info("Voucher SMS to {$voucher->purchaser_phone}: {$smsMessage}");
        } catch (\Throwable $e) {
            Log::warning('Failed to log voucher SMS: ' . $e->getMessage());
        }

        return redirect()->route('voucher.confirmation', $voucher)
            ->with('success', 'Voucher purchased successfully!');
    }

    public function lookupVoucher(Request $request): View
    {
        $validated = $request->validate([
            'lookup_email' => 'required|email',
        ]);

        $lookupResults = Voucher::where('purchaser_email', $validated['lookup_email'])
            ->with('cycle')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeCycles = Cycle::where('status', 'active')
            ->where('application_deadline', '>', now())
            ->orderBy('start_date', 'desc')
            ->get();

        $paymentMethods = [
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Debit/Credit Card',
            'bank_deposit' => 'Bank Deposit',
        ];

        $unsplashPhoto = unsplash_hero();

        return view('public.buy-voucher', compact('activeCycles', 'paymentMethods', 'unsplashPhoto', 'lookupResults'));
    }

    public function confirmation(Voucher $voucher): View
    {
        if ($voucher->payment_status !== 'completed') {
            return redirect()->route('voucher.buy')->with('error', 'Invalid voucher.');
        }

        $unsplashPhoto = unsplash_hero();

        return view('public.voucher-confirmation', compact('voucher', 'unsplashPhoto'));
    }
}
