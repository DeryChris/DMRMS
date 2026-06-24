@php
    $title = 'Verify Email';
    $subtitle = 'Please verify your email address to continue';
@endphp

<x-guest-layout>
    <form method="POST" action="{{ route('verification.send') }}" style="display:flex;flex-direction:column;gap:18px;">
        @csrf

        <div style="padding:14px 16px;background:#f0fdf4;border:1px solid #86efac;border-radius:12px;font-size:13px;color:#166534;line-height:1.5;">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div style="padding:12px 16px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;font-size:13px;color:#92400e;line-height:1.5;">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <button type="submit"
                style="width:100%;padding:14px;background:linear-gradient(135deg,#14532d,#166534);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all 0.3s ease;box-shadow:0 4px 16px rgba(20,83,45,0.3);margin-top:4px;"
                onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 28px rgba(20,83,45,0.4)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 16px rgba(20,83,45,0.3)'"
                onmousedown="this.style.transform='scale(0.98)'"
                onmouseup="this.style.transform='translateY(-2px)'">
            {{ __('Resend Verification Email') }}
        </button>

        <p style="text-align:center;font-size:13px;color:#64748b;margin:0;">
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="color:#14532d;font-weight:700;text-decoration:none;cursor:pointer;border:none;background:none;font-size:13px;transition:color 0.2s;"
                        onmouseover="this.style.color='#0f2f1f'" onmouseout="this.style.color='#14532d'">{{ __('Log Out') }}</button>
            </form>
        </p>
    </form>
</x-guest-layout>
