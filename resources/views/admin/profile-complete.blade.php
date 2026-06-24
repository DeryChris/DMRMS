<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Complete Profile - Ghana Armed Forces</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=montserrat:600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex items-center justify-center px-4" style="margin:0;">
    <div class="w-full max-w-lg">
        <div class="text-center mb-8">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:48px;width:auto;" class="mx-auto mb-4">
            <h1 class="text-2xl font-heading font-bold text-gray-900">Welcome, {{ auth()->user()->username ?? 'New User' }}!</h1>
            <p class="text-sm text-gray-500 mt-1">Please enter your name to complete registration</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
            <form method="POST" action="{{ route('admin.profile.complete.store') }}" style="display:flex;flex-direction:column;gap:18px;">
                @csrf

                <div>
                    <label for="first_name" style="display:block;font-size:12px;font-weight:700;color:#4a7a65;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">First Name</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" placeholder="Enter your first name" required autofocus
                           style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    <x-input-error :messages="$errors->get('first_name')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
                </div>

                <div>
                    <label for="last_name" style="display:block;font-size:12px;font-weight:700;color:#4a7a65;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">Last Name</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Enter your last name" required
                           style="width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;color:#1e293b;outline:none;background:#fff;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#5fa489';this.style.boxShadow='0 0 0 4px rgba(95,164,137,0.12)'"
                           onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    <x-input-error :messages="$errors->get('last_name')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
                </div>

                <button type="submit"
                        style="width:100%;padding:14px;background:linear-gradient(135deg,#14532d,#166534);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all 0.3s ease;box-shadow:0 4px 16px rgba(20,83,45,0.3);margin-top:4px;"
                        onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 28px rgba(20,83,45,0.4)'"
                        onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 16px rgba(20,83,45,0.3)'"
                        onmousedown="this.style.transform='scale(0.98)'"
                        onmouseup="this.style.transform='translateY(-2px)'">
                    Save & Continue
                </button>
            </form>
        </div>
    </div>
</body>
</html>
