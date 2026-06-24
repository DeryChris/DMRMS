<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">
    <title>@yield('title', 'Ghana Armed Forces')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=montserrat:600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
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

        @hasSection('hero')
            <section>
                @yield('hero')
            </section>
        @endif

        <main>
            @yield('content')
        </main>

        <x-chatbot-widget />

        <footer class="bg-gaf-green text-gray-300 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="flex items-center space-x-2 mb-4 md:mb-0">
                        <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:32px;width:auto;">
                        <span class="text-sm font-heading font-semibold">Ghana Armed Forces Recruitment</span>
                    </div>
                    <div class="flex space-x-6 text-sm">
                        <a href="#" class="hover:text-white transition">Privacy Policy</a>
                        <a href="#" class="hover:text-white transition">Terms of Service</a>
                        <a href="#" class="hover:text-white transition">Disclaimer</a>
                    </div>
                </div>
                <div class="text-center text-xs mt-6 text-gray-500">
                    &copy; {{ date('Y') }} Ghana Armed Forces. All rights reserved.
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
