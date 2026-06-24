<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Voucher;
use App\Services\Voucher\VoucherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ApplicantAuthController extends Controller
{
    protected VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    public function showLoginForm(): View
    {
        return view('applicant.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('applicant')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $applicant = Auth::guard('applicant')->user();
            $applicant->update(['last_login' => now()]);

            return redirect()->intended(route('applicant.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    public function showRegisterForm(): View
    {
        return view('applicant.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|exists:vouchers,serial_number',
            'pin_code'      => 'required|string',
            'email'         => 'required|email|max:255|unique:applicants,email',
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $voucherValidation = $this->voucherService->validate($validated['serial_number'], $validated['pin_code']);
        if (!$voucherValidation['valid']) {
            return back()->withErrors(['voucher' => $voucherValidation['error']])->withInput();
        }

        $voucher = $voucherValidation['voucher'];

        $applicant = Applicant::create([
            'voucher_id'            => $voucher->id,
            'first_name'            => explode('@', $validated['email'])[0],
            'last_name'             => '',
            'date_of_birth'         => now()->subYears(18),
            'gender'                => 'male',
            'contact_number'        => '0000000000',
            'email'                 => $validated['email'],
            'residential_address'   => '',
            'region'                => 'Greater Accra',
            'district'              => '',
            'national_id'           => 'GHA-000-000000-0',
            'password'              => Hash::make($validated['password']),
            'status'                => 'registered',
        ]);

        $this->voucherService->markAsUsed($voucher->id, $applicant->id);

        Auth::guard('applicant')->login($applicant);

        $request->session()->regenerate();

        return redirect()->route('applicant.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('applicant')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
