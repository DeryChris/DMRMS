<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationMail;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Voucher;
use App\Services\Notification\NotificationService;
use App\Services\Voucher\VoucherService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ApplicantAuthController extends Controller
{
    protected VoucherService $voucherService;
    protected NotificationService $notificationService;

    public function __construct(VoucherService $voucherService, NotificationService $notificationService)
    {
        $this->voucherService = $voucherService;
        $this->notificationService = $notificationService;
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

        $applicant = Applicant::where('email', $credentials['email'])->first();

        if ($applicant && $applicant->status === 'pending_verification') {
            session([
                'applicant_verification_id' => $applicant->id,
                'applicant_verification_email' => $applicant->email,
            ]);
            return redirect()->route('applicant.verify.form')
                ->with('info', 'Please verify your email address before logging in.');
        }

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
            'first_name'    => ['required', 'string', 'max:50'],
            'last_name'     => ['required', 'string', 'max:50'],
            'other_names'   => ['nullable', 'string', 'max:50'],
            'contact_number' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'alternative_contact' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'date_of_birth' => ['required', 'date', 'before:18 years ago'],
            'gender'        => ['required', 'in:Male,Female'],
            'email'         => 'required|email|max:255',
            'password'      => app(\App\Services\Security\PasswordPolicyService::class)->getValidationRules(),
        ]);

        $voucherValidation = $this->voucherService->validate($validated['serial_number'], $validated['pin_code']);
        if (!$voucherValidation['valid']) {
            return back()->withErrors(['voucher' => $voucherValidation['error']])->withInput();
        }

        $voucher = $voucherValidation['voucher'];
        $cycle = $voucher->cycle;

        if (strcasecmp($validated['email'], $voucher->purchaser_email) !== 0) {
            return back()->withErrors(['email' => 'The email must match the one used to purchase the voucher.'])->withInput();
        }

        $existing = Applicant::where('email', $validated['email'])->whereIn('status', ['pending_verification', 'registered'])->first();

        $verificationCode = (string) random_int(100000, 999999);

        if ($existing) {
            $existing->update([
                'first_name'              => $validated['first_name'],
                'last_name'               => $validated['last_name'],
                'other_names'             => $validated['other_names'] ?? null,
                'contact_number'          => $validated['contact_number'],
                'alternative_contact'     => $validated['alternative_contact'] ?? null,
                'password'                => Hash::make($validated['password']),
                'voucher_id'              => $voucher->id,
                'email_verification_code' => $verificationCode,
                'email_verification_sent_at' => Carbon::now(),
                'status'                  => 'pending_verification',
            ]);
            $applicant = $existing;

            $this->voucherService->markAsUsed($voucher->id, $applicant->id);

            try {
                Mail::to($applicant->email)->send(new EmailVerificationMail($applicant, $verificationCode));
            } catch (\Throwable $e) {
                Log::warning('Failed to send verification email: ' . $e->getMessage());
            }

            session(['applicant_verification_id' => $applicant->id, 'applicant_verification_email' => $applicant->email]);

            return redirect()->route('applicant.verify.form')
                ->with('success', 'Verification code resent to your email.');
        }

        $applicant = Applicant::create([
            'voucher_id'              => $voucher->id,
            'first_name'              => $validated['first_name'],
            'last_name'               => $validated['last_name'],
            'other_names'             => $validated['other_names'] ?? null,
            'date_of_birth'           => $validated['date_of_birth'],
            'gender'                  => $validated['gender'],
            'contact_number'          => $validated['contact_number'],
            'alternative_contact'     => $validated['alternative_contact'] ?? null,
            'email'                   => $validated['email'],
            'residential_address'     => '',
            'region'                  => '',
            'district'                => '',
            'nationality'             => 'Ghanaian',
            'national_id'            => '',
            'password'                => Hash::make($validated['password']),
            'email_verification_code' => $verificationCode,
            'email_verification_sent_at' => Carbon::now(),
            'phone_verified'          => false,
            'status'                  => 'pending_verification',
        ]);

        $this->voucherService->markAsUsed($voucher->id, $applicant->id);

        Application::create([
            'applicant_id' => $applicant->id,
            'cycle_id' => $cycle->id,
            'application_date' => Carbon::now(),
            'status' => 'registered',
        ]);

        try {
            Mail::to($applicant->email)->send(new EmailVerificationMail($applicant, $verificationCode));
        } catch (\Throwable $e) {
            Log::warning('Failed to send verification email: ' . $e->getMessage());
        }

        session(['applicant_verification_id' => $applicant->id, 'applicant_verification_email' => $applicant->email]);

        return redirect()->route('applicant.verify.form')
            ->with('success', 'Account created! Please check your email for the verification code.');
    }

    public function showVerifyForm(): View
    {
        $email = session('applicant_verification_email');

        if (!$email) {
            $applicant = Auth::guard('applicant')->user();
            if ($applicant && $applicant->email_verified_at) {
                return redirect()->route('applicant.dashboard');
            }
            $email = $applicant?->email;
        }

        return view('applicant.auth.verify-email', compact('email'));
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $applicantId = session('applicant_verification_id');
        $applicant = $applicantId ? Applicant::find($applicantId) : null;

        if (!$applicant) {
            return back()->withErrors(['code' => 'Session expired. Please register again.']);
        }

        if ($applicant->email_verified_at) {
            session()->forget(['applicant_verification_id', 'applicant_verification_email']);
            Auth::guard('applicant')->login($applicant);
            return redirect()->route('applicant.dashboard');
        }

        if ($applicant->email_verification_code !== $request->code) {
            return back()->withErrors(['code' => 'Invalid verification code.'])->withInput();
        }

        if ($applicant->email_verification_sent_at && $applicant->email_verification_sent_at->addMinutes(30)->isPast()) {
            return back()->withErrors(['code' => 'Code has expired. Request a new one.'])->withInput();
        }

        $applicant->update([
            'email_verified_at' => Carbon::now(),
            'email_verification_code' => null,
            'email_verification_sent_at' => null,
            'status' => 'active',
        ]);

        session()->forget(['applicant_verification_id', 'applicant_verification_email']);

        Auth::guard('applicant')->login($applicant);

        $this->notificationService->registrationWelcome($applicant);

        return redirect()->route('applicant.dashboard')
            ->with('success', 'Email verified successfully! Your account is now active.');
    }

    public function resendVerification(): RedirectResponse
    {
        $applicantId = session('applicant_verification_id');
        $applicant = $applicantId ? Applicant::find($applicantId) : null;

        if (!$applicant) {
            return redirect()->route('applicant.register')
                ->withErrors(['email' => 'Session expired. Please register again.']);
        }

        if ($applicant->email_verified_at) {
            session()->forget(['applicant_verification_id', 'applicant_verification_email']);
            Auth::guard('applicant')->login($applicant);
            return redirect()->route('applicant.dashboard');
        }

        $newCode = (string) random_int(100000, 999999);

        $applicant->update([
            'email_verification_code' => $newCode,
            'email_verification_sent_at' => Carbon::now(),
        ]);

        try {
            Mail::to($applicant->email)->send(new EmailVerificationMail($applicant, $newCode));
        } catch (\Throwable $e) {
            Log::warning('Failed to resend verification email: ' . $e->getMessage());
        }

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('applicant')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
