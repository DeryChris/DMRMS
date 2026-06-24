@php
    $title = 'Confirm Password';
    $subtitle = 'This is a secure area. Please confirm your password before continuing.';
@endphp

<x-guest-layout>
    <form method="POST" action="{{ route('password.confirm') }}" style="display:flex;flex-direction:column;gap:18px;">
        @csrf

        <div style="padding:14px 16px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;font-size:13px;color:#92400e;line-height:1.5;">
            This is a secure area of the application. Please confirm your password before continuing.
        </div>

        <div>
            <label for="password" style="display:block;font-size:12px;font-weight:700;color:#4a7a65;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Password</label>
            <input id="password" type="password" name="password" placeholder="Enter your password" required autocomplete="current-password"
                   style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                   onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                   onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
            <x-input-error :messages="$errors->get('password')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
        </div>

        <button type="submit"
                style="width:100%;padding:14px;background:linear-gradient(135deg,#14532d,#166534);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all 0.3s ease;box-shadow:0 4px 16px rgba(20,83,45,0.3);"
                onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 28px rgba(20,83,45,0.4)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 16px rgba(20,83,45,0.3)'"
                onmousedown="this.style.transform='scale(0.98)'"
                onmouseup="this.style.transform='translateY(-2px)'">
            Confirm
        </button>
    </form>
</x-guest-layout>