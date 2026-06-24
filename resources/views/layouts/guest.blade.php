<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=montserrat:600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased" style="margin:0;padding:0;background:#0a1a0f;">
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
                        @auth
                            <a href="{{ route('dashboard') }}" class="bg-gaf-khaki text-gaf-green px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gaf-khaki transition">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="bg-gaf-red text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 transition">Login</a>
                        @endauth
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
        <div class="flag-strip"></div>
        <div style="min-height:calc(100vh - 8px);display:flex;align-items:center;justify-content:center;padding:24px;position:relative;background:linear-gradient(135deg,rgba(20,83,45,0.92),rgba(15,47,31,0.95)),url('{{ asset("assets/images/hero/img.png") }}') no-repeat center center/cover;overflow:hidden;">
            <div style="position:absolute;inset:0;overflow:hidden;pointer-events:none;">
                <div style="position:absolute;top:-30%;left:-20%;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(212,175,55,0.08),transparent 70%);"></div>
                <div style="position:absolute;bottom:-20%;right:-10%;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(95,164,137,0.08),transparent 70%);"></div>
            </div>
            <div style="width:100%;max-width:460px;animation:fadeIn 0.5s ease-out;">
                <div style="background:rgba(255,255,255,0.97);backdrop-filter:blur(20px);border-radius:24px;box-shadow:0 25px 60px rgba(0,0,0,0.3),0 0 0 1px rgba(255,255,255,0.1);padding:40px 36px;transition:transform 0.3s ease,box-shadow 0.3s ease;">
                    <div style="text-align:center;margin-bottom:28px;">
                        <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:72px;width:auto;margin:0 auto 16px;display:block;">
                        <h2 style="font-family:'Montserrat',sans-serif;font-weight:700;font-size:20px;color:#1a202c;margin:0 0 4px;letter-spacing:0.3px;">{{ $title ?? str_replace(['.','-'],' ',ucwords(request()->route()->getName() ?: 'Authentication')) }}</h2>
                        <p style="font-size:13px;color:#64748b;margin:0;">{{ $subtitle ?? '' }}</p>
                    </div>
                    <div style="margin-bottom:24px;">
                        <x-auth-session-status class="mb-4" :status="session('status')" />
                    </div>
                    {{ $slot }}
                </div>
                <div style="text-align:center;margin-top:16px;">
                    <x-guidelines-overlay link-text="Read Eligibility Guidelines" style="color:rgba(255,255,255,0.7);font-size:12px;" />
                </div>
                <p style="text-align:center;margin-top:12px;font-size:12px;color:rgba(255,255,255,0.6);letter-spacing:0.5px;">
                    &copy; {{ date('Y') }} Ghana Armed Forces &mdash; DMRMS
                </p>
            </div>
        </div>
        </div>
        <style>
            @keyframes fadeIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        </style>
    </body>
</html>
