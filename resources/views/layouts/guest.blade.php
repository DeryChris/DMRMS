<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - Ghana Armed Forces' : 'Ghana Armed Forces' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=montserrat:600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased" style="margin:0;padding:0;">
    <div x-data="{ mobileMenu: false }">
    <nav class="bg-gaf-green text-white shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:40px;width:auto;">
                    <div class="hidden sm:block leading-tight">
                        <div class="font-heading font-bold text-sm uppercase tracking-wide">Ghana Armed Forces</div>
                        <div class="font-heading text-xs text-gaf-khaki/80">Recruitment Platform</div>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('landing') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">Home</a>
                    <a href="{{ route('eligibility.checker') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">Eligibility Checker</a>
                    <a href="{{ route('announcements') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">Announcements</a>
                    <a href="{{ route('guide') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">Guide</a>
                    <a href="{{ route('faq') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">FAQ</a>
                    <a href="{{ route('contact') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">Contact</a>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('login') }}" class="bg-gaf-red text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 transition">Login</a>
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div x-show="mobileMenu" x-cloak class="md:hidden bg-gaf-green border-t border-gaf-dark-green px-4 py-3 space-y-2">
            <a href="{{ route('landing') }}" class="block text-white hover:text-gaf-khaki py-1 text-sm">Home</a>
            <a href="{{ route('eligibility.checker') }}" class="block text-white hover:text-gaf-khaki py-1 text-sm">Eligibility Checker</a>
            <a href="{{ route('announcements') }}" class="block text-white hover:text-gaf-khaki py-1 text-sm">Announcements</a>
            <a href="{{ route('guide') }}" class="block text-white hover:text-gaf-khaki py-1 text-sm">Guide</a>
            <a href="{{ route('faq') }}" class="block text-white hover:text-gaf-khaki py-1 text-sm">FAQ</a>
            <a href="{{ route('contact') }}" class="block text-white hover:text-gaf-khaki py-1 text-sm">Contact</a>
        </div>
    </nav>
    <div class="auth-split">
        <div class="auth-split-left" style="background:linear-gradient(135deg,rgba(20,83,45,0.92),rgba(15,47,31,0.95)),url('{{ asset("assets/images/hero/img.png") }}') no-repeat center center/cover;position:relative;">
            <div style="position:absolute;inset:0;overflow:hidden;pointer-events:none;">
                <div style="position:absolute;top:-20%;left:-10%;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(212,175,55,0.06),transparent 70%);"></div>
                <div style="position:absolute;bottom:-20%;right:-10%;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(95,164,137,0.06),transparent 70%);"></div>
            </div>
            <div class="auth-info-box">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:48px;width:auto;">
                    <div style="line-height:1.2;">
                        <div style="font-family:'Montserrat',sans-serif;font-weight:700;font-size:16px;color:#fff;letter-spacing:1px;">GHANA ARMED FORCES</div>
                        <div style="font-size:11px;color:rgba(255,255,255,0.65);letter-spacing:0.5px;">DEFENCE MANPOWER & RECORDS MANAGEMENT</div>
                    </div>
                </div>
                <h1>{{ config('app.name', 'DMRMS') }} Recruitment Portal</h1>
                <ul>
                    <li>Apply for enlistment into the Ghana Armed Forces</li>
                    <li>Track your application status in real-time</li>
                    <li>Receive notifications on recruitment updates</li>
                    <li>Secure and confidential processing of records</li>
                </ul>
                <div style="margin-top:24px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.15);">
                    <p style="font-size:11px;color:rgba(255,255,255,0.5);margin:0;letter-spacing:0.3px;">
                        &copy; {{ date('Y') }} Ghana Armed Forces. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
        <div class="auth-split-right">
            <div class="auth-form-container" style="text-align:center;">
                <div class="auth-form-header">
                    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:56px;width:auto;margin-bottom:8px;">
                    <h2 style="font-family:'Montserrat',sans-serif;font-weight:700;font-size:18px;color:#1a202c;margin:0 0 2px;letter-spacing:0.3px;">{{ $title ?? 'Authentication' }}</h2>
                    <p style="font-size:12px;color:#64748b;margin:0;">{{ $subtitle ?? '' }}</p>
                </div>
                <x-auth-session-status class="mb-4" :status="session('status')" />
                {{ $slot }}
            </div>
        </div>
    </div>
    </div>
    @stack('scripts')
</body>
</html>
