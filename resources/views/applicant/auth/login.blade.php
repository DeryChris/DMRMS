<x-applicant-guest-layout title="Applicant Login" subtitle="Sign in to your recruitment portal">
    <form method="POST" action="{{ route('applicant.login') }}" style="display:flex;flex-direction:column;gap:18px;">
        @csrf

        <div>
            <label for="email" style="display:block;font-size:12px;font-weight:700;color:#4a7a65;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Email Address</label>
            <input id="email" type="email" name="email" :value="old('email')" placeholder="your@email.com" required autofocus autocomplete="username"
                   style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:border-color 0.2s,box-shadow 0.2s;box-sizing:border-box;"
                   onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                   onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
            <x-input-error :messages="$errors->get('email')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
        </div>

        <div>
            <label for="password" style="display:block;font-size:12px;font-weight:700;color:#4a7a65;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Password</label>
            <div style="position:relative;" x-data="{ showPassword: false }">
                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" placeholder="Enter your password" required autocomplete="current-password"
                       style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                       onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                       onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                <button type="button" @click="showPassword = !showPassword"
                        style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:#94a3b8;display:flex;align-items:center;justify-content:center;"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'" tabindex="-1">
                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;">
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#64748b;cursor:pointer;">
                <input type="checkbox" name="remember" style="width:16px;height:16px;border-radius:4px;border:2px solid #cbd5e1;accent-color:#14532d;">
                Remember me
            </label>
            <a href="{{ route('applicant.password.request') }}" style="font-size:13px;color:#14532d;font-weight:600;text-decoration:none;transition:color 0.2s;"
               onmouseover="this.style.color='#0f2f1f'" onmouseout="this.style.color='#14532d'">Forgot Password?</a>
        </div>

        <button type="submit"
                style="width:100%;padding:14px;background:linear-gradient(135deg,#14532d,#166534);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all 0.3s ease;box-shadow:0 4px 16px rgba(20,83,45,0.3);margin-top:4px;"
                onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 28px rgba(20,83,45,0.4)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 16px rgba(20,83,45,0.3)'"
                onmousedown="this.style.transform='scale(0.98)'"
                onmouseup="this.style.transform='translateY(-2px)'">
            Log In
        </button>

        <p style="text-align:center;font-size:13px;color:#64748b;margin:0;">
            Don't have an account?
            <a href="{{ route('applicant.register') }}" style="color:#14532d;font-weight:700;text-decoration:none;transition:color 0.2s;"
               onmouseover="this.style.color='#0f2f1f'" onmouseout="this.style.color='#14532d'">Register</a>
        </p>

        <p style="text-align:center;font-size:12px;color:#94a3b8;margin:0;border-top:1px solid #e2e8f0;padding-top:12px;">
            <a href="{{ route('login') }}" style="color:#64748b;text-decoration:none;transition:color 0.2s;"
               onmouseover="this.style.color='#14532d'" onmouseout="this.style.color='#64748b'">Admin Login</a>
        </p>
    </form>
</x-applicant-guest-layout>
