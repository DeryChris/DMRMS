<x-applicant-guest-layout title="Create Account" subtitle="Register with your voucher to begin your recruitment journey">
    <form method="POST" action="{{ route('applicant.register') }}" style="display:flex;flex-direction:column;gap:14px;">
        @csrf

        <div>
            <label for="email" style="display:block;font-size:12px;font-weight:700;color:#4a7a65;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Email Address</label>
            <input id="email" type="email" name="email" :value="old('email')" placeholder="your@email.com" required autofocus
                   style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                   onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                   onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
            <x-input-error :messages="$errors->get('email')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
                <label for="password" style="display:block;font-size:12px;font-weight:700;color:#4a7a65;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Password</label>
                <input id="password" type="password" name="password" placeholder="Min 8 characters" required autocomplete="new-password"
                       style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                       onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                <x-input-error :messages="$errors->get('password')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
            </div>
            <div>
                <label for="password_confirmation" style="display:block;font-size:12px;font-weight:700;color:#4a7a65;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirm password" required autocomplete="new-password"
                       style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                       onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                <x-input-error :messages="$errors->get('password_confirmation')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
            </div>
        </div>

        <div style="border-top:2px dashed #e2e8f0;padding-top:14px;margin-top:4px;">
            <p style="font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Voucher Details</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label for="serial_number" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">Serial Number</label>
                    <input id="serial_number" type="text" name="serial_number" :value="old('serial_number')" placeholder="e.g. GAF-SN-2026-XXXX" required
                           style="width:100%;padding:10px 14px;border:2px solid #e2e8f0;border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    <x-input-error :messages="$errors->get('serial_number')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
                </div>
                <div>
                    <label for="pin_code" style="display:block;font-size:11px;font-weight:700;color:#4a7a65;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.5px;">PIN Code</label>
                    <input id="pin_code" type="text" name="pin_code" :value="old('pin_code')" placeholder="e.g. 123456" required
                           style="width:100%;padding:10px 14px;border:2px solid #e2e8f0;border-radius:10px;font-size:13px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    <x-input-error :messages="$errors->get('pin_code')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
                </div>
            </div>
            <x-input-error :messages="$errors->get('voucher')" style="font-size:11px;color:#dc2626;margin-top:2px;" />
        </div>

        <button type="submit"
                style="width:100%;padding:14px;background:linear-gradient(135deg,#14532d,#166534);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all 0.3s ease;box-shadow:0 4px 16px rgba(20,83,45,0.3);margin-top:4px;"
                onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 28px rgba(20,83,45,0.4)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 16px rgba(20,83,45,0.3)'"
                onmousedown="this.style.transform='scale(0.98)'"
                onmouseup="this.style.transform='translateY(-2px)'">
            Create Account
        </button>

        <p style="text-align:center;font-size:13px;color:#64748b;margin:0;">
            Already have an account?
            <a href="{{ route('applicant.login') }}" style="color:#14532d;font-weight:700;text-decoration:none;transition:color 0.2s;"
               onmouseover="this.style.color='#0f2f1f'" onmouseout="this.style.color='#14532d'">Log in</a>
        </p>

        <p style="text-align:center;font-size:12px;color:#94a3b8;margin:0;border-top:1px solid #e2e8f0;padding-top:12px;">
            <a href="{{ route('login') }}" style="color:#64748b;text-decoration:none;transition:color 0.2s;"
               onmouseover="this.style.color='#14532d'" onmouseout="this.style.color='#64748b'">Admin Login</a>
        </p>
    </form>
</x-applicant-guest-layout>
