@php
    $title = 'Reset Password';
    $subtitle = 'Enter your email and we\'ll send you a reset link';
@endphp

<x-guest-layout>
    <form method="POST" action="{{ route('password.email') }}" style="display:flex;flex-direction:column;gap:18px;">
        @csrf

        <div style="padding:14px 16px;background:#f0fdf4;border:1px solid #86efac;border-radius:12px;font-size:13px;color:#166534;line-height:1.5;">
            Forgot your password? No problem. Just enter your email address below and we'll send you a password reset link.
        </div>

        <div>
            <label for="email" style="display:block;font-size:12px;font-weight:700;color:#4a7a65;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Email Address</label>
            <input id="email" type="email" name="email" :value="old('email')" placeholder="your@email.com" required autofocus
                   style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                   onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                   onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
            <x-input-error :messages="$errors->get('email')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
        </div>

        <button type="submit"
                style="width:100%;padding:14px;background:linear-gradient(135deg,#14532d,#166534);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all 0.3s ease;box-shadow:0 4px 16px rgba(20,83,45,0.3);"
                onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 28px rgba(20,83,45,0.4)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 16px rgba(20,83,45,0.3)'"
                onmousedown="this.style.transform='scale(0.98)'"
                onmouseup="this.style.transform='translateY(-2px)'">
            Send Reset Link
        </button>

        <p style="text-align:center;font-size:13px;color:#64748b;margin:0;">
            Remember your password?
            <a href="{{ route('login') }}" style="color:#14532d;font-weight:700;text-decoration:none;transition:color 0.2s;"
               onmouseover="this.style.color='#0f2f1f'" onmouseout="this.style.color='#14532d'">Log in</a>
        </p>
    </form>
</x-guest-layout>