<x-applicant-guest-layout title="Verify Your Email" subtitle="Enter the 6-digit code sent to your email">
    @if (session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:16px;">{{ session('success') }}</div>
    @endif
    @if (session('info'))
        <div style="background:#dbeafe;border:1px solid #93c5fd;color:#1e40af;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:16px;">{{ session('info') }}</div>
    @endif

    <div style="text-align:center;margin-bottom:20px;">
        <div style="width:56px;height:56px;background:#e8f5e9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#14532d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <p style="font-size:14px;color:#334155;margin:0 0 4px;">We sent a verification code to</p>
        <p style="font-size:14px;font-weight:700;color:#14532d;margin:0;">{{ $email ?? 'your email' }}</p>
    </div>

    <form method="POST" action="{{ route('applicant.verify') }}" style="display:flex;flex-direction:column;gap:16px;">
        @csrf
        <div>
            <label for="code" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;text-align:center;">6-Digit Verification Code</label>
            <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code"
                   placeholder="000000" required autofocus
                   style="width:100%;max-width:240px;margin:0 auto;display:block;padding:14px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:24px;font-weight:700;letter-spacing:12px;color:#1e293b;outline:none;background:#fff;text-align:center;transition:all 0.2s;box-sizing:border-box;"
                   onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                   onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'"
                   oninput="this.value=this.value.replace(/[^0-9]/g,'')">
            <x-input-error :messages="$errors->get('code')" style="font-size:12px;color:#dc2626;text-align:center;margin-top:6px;display:block;" />
        </div>

        <button type="submit"
                style="width:100%;padding:14px;background:linear-gradient(135deg,#14532d,#166534);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all 0.3s ease;box-shadow:0 4px 16px rgba(20,83,45,0.3);"
                onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 28px rgba(20,83,45,0.4)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 16px rgba(20,83,45,0.3)'"
                onmousedown="this.style.transform='scale(0.98)'"
                onmouseup="this.style.transform='translateY(-2px)'">
            Verify Email
        </button>
    </form>

    <div style="text-align:center;margin-top:16px;padding-top:16px;border-top:1px solid #e2e8f0;">
        <p style="font-size:13px;color:#64748b;margin:0 0 4px;">Didn't receive the code?</p>
        <a href="{{ route('applicant.verify.resend') }}" style="color:#14532d;font-weight:700;font-size:13px;text-decoration:none;transition:color 0.2s;"
           onmouseover="this.style.color='#0f2f1f'" onmouseout="this.style.color='#14532d'">Resend Code</a>
    </div>

    <div style="text-align:center;margin-top:12px;">
        <a href="{{ route('applicant.register') }}" style="font-size:12px;color:#94a3b8;text-decoration:none;transition:color 0.2s;"
           onmouseover="this.style.color='#14532d'" onmouseout="this.style.color='#94a3b8'">&larr; Back to Registration</a>
    </div>
</x-applicant-guest-layout>
