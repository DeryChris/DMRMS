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
            <input id="password" type="password" name="password" placeholder="Enter your password" required autocomplete="current-password"
                   style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                   onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                   onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
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
