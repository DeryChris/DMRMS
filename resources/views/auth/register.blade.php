@php
    $title = 'Create Account';
    $subtitle = 'Register to begin your recruitment journey';
@endphp

<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" style="display:flex;flex-direction:column;gap:16px;">
        @csrf

        <div>
            <label for="name" style="display:block;font-size:12px;font-weight:700;color:#4a7a65;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Full Name</label>
            <input id="name" type="text" name="name" :value="old('name')" placeholder="Enter your full name" required autofocus autocomplete="name"
                   style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                   onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                   onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
            <x-input-error :messages="$errors->get('name')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
        </div>

        <div>
            <label for="email" style="display:block;font-size:12px;font-weight:700;color:#4a7a65;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Email Address</label>
            <input id="email" type="email" name="email" :value="old('email')" placeholder="your@email.com" required autocomplete="username"
                   style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                   onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                   onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
            <x-input-error :messages="$errors->get('email')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
        </div>

        <div>
            <label for="password" style="display:block;font-size:12px;font-weight:700;color:#4a7a65;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Password</label>
            <input id="password" type="password" name="password" placeholder="Create a strong password" required autocomplete="new-password"
                   style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                   onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                   onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
            <x-input-error :messages="$errors->get('password')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
        </div>

        <div>
            <label for="password_confirmation" style="display:block;font-size:12px;font-weight:700;color:#4a7a65;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirm your password" required autocomplete="new-password"
                   style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                   onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                   onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
            <x-input-error :messages="$errors->get('password_confirmation')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
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
            Already registered?
            <a href="{{ route('login') }}" style="color:#14532d;font-weight:700;text-decoration:none;transition:color 0.2s;"
               onmouseover="this.style.color='#0f2f1f'" onmouseout="this.style.color='#14532d'">Log in</a>
        </p>
    </form>
</x-guest-layout>