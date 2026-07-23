<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Voucher;
use App\Services\Voucher\VoucherService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected VoucherService $voucherService;
    protected NotificationService $notificationService;

    public function __construct(VoucherService $voucherService, NotificationService $notificationService)
    {
        $this->voucherService = $voucherService;
        $this->notificationService = $notificationService;
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|exists:vouchers,serial_number',
            'pin_code'      => 'required|string',
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'other_names'   => 'nullable|string|max:255',
            'date_of_birth' => 'required|date',
            'gender'        => 'required|in:male,female',
            'marital_status'=> 'nullable|in:single,married,divorced,widowed',
            'contact_number'=> 'required|string|regex:/^[0-9]{10}$/|unique:applicants,contact_number,NULL,id,deleted_at,NULL',
            'alternative_contact' => 'nullable|string|regex:/^[0-9]{10}$/',
            'email'         => 'required|email|max:255|unique:applicants,email,NULL,id,deleted_at,NULL',
            'residential_address' => 'required|string|max:500',
            'region'        => 'required|string|max:255',
            'district'      => 'required|string|max:255',
            'nationality'   => 'required|string|max:255',
            'national_id'   => 'required|string|max:50|unique:applicants,national_id,NULL,id,deleted_at,NULL',
            'password'      => app(\App\Services\Security\PasswordPolicyService::class)->getValidationRules(),
        ]);

        $voucherValidation = $this->voucherService->validate($validated['serial_number'], $validated['pin_code']);
        if (!$voucherValidation['valid']) {
            return response()->json(['message' => $voucherValidation['error']], 422);
        }

        $voucher = $voucherValidation['voucher'];

        $applicant = Applicant::create([
            'voucher_id'            => $voucher->id,
            'first_name'            => $validated['first_name'],
            'last_name'             => $validated['last_name'],
            'other_names'           => $validated['other_names'] ?? null,
            'date_of_birth'         => $validated['date_of_birth'],
            'gender'                => $validated['gender'],
            'marital_status'        => $validated['marital_status'] ?? null,
            'contact_number'        => $validated['contact_number'],
            'alternative_contact'   => $validated['alternative_contact'] ?? null,
            'email'                 => $validated['email'],
            'residential_address'   => $validated['residential_address'],
            'region'                => $validated['region'],
            'district'              => $validated['district'],
            'nationality'           => $validated['nationality'],
            'national_id'           => $validated['national_id'],
            'password'              => Hash::make($validated['password']),
            'status'                => 'registered',
        ]);

        $this->voucherService->markAsUsed($voucher->id, $applicant->id);

        $this->generateAndSendVerificationCodes($applicant);

        $token = $applicant->createToken('auth-token', ['applicant'])->plainTextToken;

        return response()->json([
            'message'   => 'Registration successful. Please verify your email and phone.',
            'token'     => $token,
            'applicant' => $applicant,
        ], 201);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $applicant = $request->user();

        $verificationCode = $applicant->verificationCodes()
            ->where('type', 'email')
            ->where('code_value', $request->code)
            ->where('expiry_date', '>', now())
            ->where('used_status', false)
            ->first();

        if (!$verificationCode) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 422);
        }

        $verificationCode->update(['used_status' => true, 'used_at' => now()]);
        $applicant->update(['email_verified_at' => now()]);

        return response()->json(['message' => 'Email verified successfully.']);
    }

    public function verifyPhone(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $applicant = $request->user();

        $verificationCode = $applicant->verificationCodes()
            ->where('type', 'phone')
            ->where('code_value', $request->code)
            ->where('expiry_date', '>', now())
            ->where('used_status', false)
            ->first();

        if (!$verificationCode) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 422);
        }

        $verificationCode->update(['used_status' => true, 'used_at' => now()]);
        $applicant->update(['phone_verified' => true]);

        return response()->json(['message' => 'Phone verified successfully.']);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $applicant = Applicant::where('email', $request->email)->first();

        if (!$applicant || !Hash::check($request->password, $applicant->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $abilities = $applicant->email_verified_at ? ['applicant'] : ['applicant'];

        $token = $applicant->createToken('auth-token', $abilities)->plainTextToken;

        return response()->json([
            'message'   => 'Login successful.',
            'token'     => $token,
            'applicant' => $applicant,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function refresh(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        $token = $request->user()->createToken('auth-token', ['applicant'])->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully.',
            'token'   => $token,
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('applicants')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 422);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => app(\App\Services\Security\PasswordPolicyService::class)->getValidationRules(),
        ]);

        $status = Password::broker('applicants')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Applicant $applicant, string $password) {
                $applicant->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 422);
    }

    private function generateAndSendVerificationCodes(Applicant $applicant): void
    {
        $emailCode = $this->generateVerificationCode();
        $phoneCode = $this->generateVerificationCode();

        $applicant->verificationCodes()->createMany([
            [
                'type'        => 'email',
                'code_value'  => $emailCode,
                'issue_date'  => now(),
                'expiry_date' => now()->addMinutes(30),
                'used_status' => false,
            ],
            [
                'type'        => 'phone',
                'code_value'  => $phoneCode,
                'issue_date'  => now(),
                'expiry_date' => now()->addMinutes(30),
                'used_status' => false,
            ],
        ]);

        $this->notificationService->sendEmailVerificationCode($applicant, $emailCode);
        $this->notificationService->sendSmsVerificationCode($applicant, $phoneCode);
    }

    private function generateVerificationCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
