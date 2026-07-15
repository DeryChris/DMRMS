<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ApplicantNewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('applicant.auth.reset-password', ['request' => $request]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => app(\App\Services\Security\PasswordPolicyService::class)->getValidationRules(),
        ]);

        $status = Password::broker('applicants')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Applicant $applicant) use ($request) {
                $applicant->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($applicant));
            }
        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('applicant.login')->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
