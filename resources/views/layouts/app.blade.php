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
<body class="font-sans antialiased text-gray-900 dark:text-slate-100" style="background: linear-gradient(180deg, #f8faf8 0%, #f0f7f0 50%, #f8faf8 100%);">
    @php
        $anyAuth = auth()->check() || auth('applicant')->check();
        $isApplicant = auth('applicant')->check();
    @endphp
    <div x-data="{ mobileMenu: false }" class="flex flex-col min-h-screen">
        <nav x-data="{ navVisible: true, lastScroll: 0 }" @scroll.window="const y = window.scrollY; navVisible = y < 80 || y < lastScroll; lastScroll = y" :class="{ '-translate-y-full': !navVisible }" class="bg-gaf-green text-white shadow-lg sticky top-0 z-40 gradient-border transition-transform duration-300 md:!translate-y-0">
            <div class="flex items-center h-16 relative px-4 sm:px-6 lg:px-8">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:40px;width:auto;">
                    <div class="hidden sm:block leading-tight">
                        <div class="font-heading font-bold text-sm uppercase tracking-wide">Ghana Armed Forces</div>
                        <div class="font-heading text-xs text-gaf-khaki/80">Recruitment Platform</div>
                    </div>
                </div>
                <div class="hidden md:flex items-center justify-center absolute left-1/2 -translate-x-1/2 space-x-6">
                    <a href="{{ route('landing') }}" class="relative pb-0.5 border-b-2 {{ request()->routeIs('landing') ? 'text-gaf-khaki border-gaf-khaki' : 'text-white/80 border-transparent hover:text-gaf-khaki hover:border-gaf-khaki/50' }} transition text-sm font-medium">Home</a>
                    @if(!$isApplicant)
                    <a href="{{ route('recruitment.portal') }}" class="relative pb-0.5 border-b-2 {{ request()->routeIs('recruitment.portal') ? 'text-gaf-khaki border-gaf-khaki' : 'text-white/80 border-transparent hover:text-gaf-khaki hover:border-gaf-khaki/50' }} transition text-sm font-medium">Recruitment</a>
                    @endif
                    @if(!$anyAuth)
                    <a href="{{ route('voucher.buy') }}" class="relative pb-0.5 border-b-2 {{ request()->routeIs('voucher.buy') ? 'text-gaf-khaki border-gaf-khaki' : 'text-white/80 border-transparent hover:text-gaf-khaki hover:border-gaf-khaki/50' }} transition text-sm font-medium">Buy Voucher</a>
                    <a href="{{ route('eligibility.checker') }}" class="relative pb-0.5 border-b-2 {{ request()->routeIs('eligibility.checker') ? 'text-gaf-khaki border-gaf-khaki' : 'text-white/80 border-transparent hover:text-gaf-khaki hover:border-gaf-khaki/50' }} transition text-sm font-medium">Eligibility Checker</a>
                    @endif
                    @if(!$isApplicant)
                    <a href="{{ route('announcements') }}" class="relative pb-0.5 border-b-2 {{ request()->routeIs('announcements') || request()->routeIs('announcements.show*') ? 'text-gaf-khaki border-gaf-khaki' : 'text-white/80 border-transparent hover:text-gaf-khaki hover:border-gaf-khaki/50' }} transition text-sm font-medium">Announcements</a>
                    @endif
                    <a href="{{ route('guide') }}" class="relative pb-0.5 border-b-2 {{ request()->routeIs('guide') ? 'text-gaf-khaki border-gaf-khaki' : 'text-white/80 border-transparent hover:text-gaf-khaki hover:border-gaf-khaki/50' }} transition text-sm font-medium">Guide</a>
                    <a href="{{ route('faq') }}" class="relative pb-0.5 border-b-2 {{ request()->routeIs('faq') ? 'text-gaf-khaki border-gaf-khaki' : 'text-white/80 border-transparent hover:text-gaf-khaki hover:border-gaf-khaki/50' }} transition text-sm font-medium">FAQ</a>
                    <a href="{{ route('contact') }}" class="relative pb-0.5 border-b-2 {{ request()->routeIs('contact') ? 'text-gaf-khaki border-gaf-khaki' : 'text-white/80 border-transparent hover:text-gaf-khaki hover:border-gaf-khaki/50' }} transition text-sm font-medium">Contact</a>
                </div>
                <div class="flex items-center space-x-3 ml-auto">
                    <x-theme-toggle :dark="true" />
                    @if(!$anyAuth)
                    <a href="{{ route('applicant.login') }}" class="bg-gaf-red text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 transition">Login</a>
                    <a href="{{ route('applicant.register') }}" class="bg-gaf-green text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Register</a>
                    @if(request()->routeIs('landing'))
                    <a href="{{ route('login') }}" class="bg-gaf-khaki text-gaf-green px-4 py-2 rounded-lg text-sm font-semibold hover:bg-yellow-500 transition">Admin</a>
                    @endif
                    @elseif(auth('applicant')->check())
                    @if(!request()->routeIs('applicant.dashboard'))
                    <a href="{{ route('applicant.dashboard') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">Dashboard</a>
                    @endif
                    <x-profile-dropdown :dark="true" />
                    <x-notification-bell :dark="true" />
                    <form method="POST" action="{{ route('applicant.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-gaf-khaki text-gaf-green px-4 py-2 rounded-lg text-sm font-semibold hover:bg-yellow-500 transition">Sign Out</button>
                    </form>
                    @else
                    @if(!request()->routeIs('applicant.dashboard'))
                    <a href="{{ route('applicant.dashboard') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-gaf-khaki text-gaf-green px-4 py-2 rounded-lg text-sm font-semibold hover:bg-yellow-500 transition">Sign Out</button>
                    </form>
                    @endif
                    <button @click="mobileMenu = !mobileMenu" class="md:hidden text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div x-show="mobileMenu" x-cloak class="md:hidden bg-gaf-green border-t border-gaf-dark-green px-4 py-3 space-y-2">
                <a href="{{ route('landing') }}" class="block py-1.5 text-sm transition {{ request()->routeIs('landing') ? 'text-gaf-khaki border-l-4 border-gaf-khaki pl-3 font-medium' : 'text-white/80 hover:text-gaf-khaki pl-4' }}">Home</a>
                @if(!$isApplicant)
                <a href="{{ route('recruitment.portal') }}" class="block py-1.5 text-sm transition {{ request()->routeIs('recruitment.portal') ? 'text-gaf-khaki border-l-4 border-gaf-khaki pl-3 font-medium' : 'text-white/80 hover:text-gaf-khaki pl-4' }}">Recruitment</a>
                @endif
                @if(!$anyAuth)
                <a href="{{ route('voucher.buy') }}" class="block py-1.5 text-sm transition {{ request()->routeIs('voucher.buy') ? 'text-gaf-khaki border-l-4 border-gaf-khaki pl-3 font-medium' : 'text-white/80 hover:text-gaf-khaki pl-4' }}">Buy Voucher</a>
                <a href="{{ route('eligibility.checker') }}" class="block py-1.5 text-sm transition {{ request()->routeIs('eligibility.checker') ? 'text-gaf-khaki border-l-4 border-gaf-khaki pl-3 font-medium' : 'text-white/80 hover:text-gaf-khaki pl-4' }}">Eligibility Checker</a>
                @endif
                @if(!$isApplicant)
                <a href="{{ route('announcements') }}" class="block py-1.5 text-sm transition {{ request()->routeIs('announcements') || request()->routeIs('announcements.show*') ? 'text-gaf-khaki border-l-4 border-gaf-khaki pl-3 font-medium' : 'text-white/80 hover:text-gaf-khaki pl-4' }}">Announcements</a>
                @endif
                <a href="{{ route('guide') }}" class="block py-1.5 text-sm transition {{ request()->routeIs('guide') ? 'text-gaf-khaki border-l-4 border-gaf-khaki pl-3 font-medium' : 'text-white/80 hover:text-gaf-khaki pl-4' }}">Guide</a>
                <a href="{{ route('faq') }}" class="block py-1.5 text-sm transition {{ request()->routeIs('faq') ? 'text-gaf-khaki border-l-4 border-gaf-khaki pl-3 font-medium' : 'text-white/80 hover:text-gaf-khaki pl-4' }}">FAQ</a>
                <a href="{{ route('contact') }}" class="block py-1.5 text-sm transition {{ request()->routeIs('contact') ? 'text-gaf-khaki border-l-4 border-gaf-khaki pl-3 font-medium' : 'text-white/80 hover:text-gaf-khaki pl-4' }}">Contact</a>
                @if(auth('applicant')->check())
                @if(!request()->routeIs('applicant.dashboard'))
                <a href="{{ route('applicant.dashboard') }}" class="block py-1.5 text-sm transition {{ request()->routeIs('applicant.dashboard') ? 'text-gaf-khaki border-l-4 border-gaf-khaki pl-3 font-medium' : 'text-white/80 hover:text-gaf-khaki pl-4' }}">Dashboard</a>
                @endif
                <form method="POST" action="{{ route('applicant.logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left text-white/80 hover:text-gaf-khaki py-1.5 text-sm transition">Sign Out</button>
                </form>
                @elseif(auth()->check())
                @if(!request()->routeIs('applicant.dashboard'))
                <a href="{{ route('applicant.dashboard') }}" class="block py-1.5 text-sm transition {{ request()->routeIs('applicant.dashboard') ? 'text-gaf-khaki border-l-4 border-gaf-khaki pl-3 font-medium' : 'text-white/80 hover:text-gaf-khaki pl-4' }}">Dashboard</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left text-white/80 hover:text-gaf-khaki py-1.5 text-sm transition">Sign Out</button>
                </form>
                @endif
            </div>
        </nav>

        @hasSection('hero')
            <section>
                @yield('hero')
            </section>
        @endif

        <main class="flex-1 relative pb-20 md:pb-0 dark:bg-slate-900 dark:text-slate-100">
            <div style="position:absolute;inset:0;pointer-events:none;opacity:0.025;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%2314532d%22 fill-opacity=%221%22%3E%3Ccircle cx=%2230%22 cy=%2230%22 r=%2220%22/%3E%3Ccircle cx=%22170%22 cy=%2240%22 r=%2225%22/%3E%3Ccircle cx=%22100%22 cy=%22170%22 r=%2230%22/%3E%3Ccircle cx=%2260%22 cy=%22140%22 r=%2215%22/%3E%3Ccircle cx=%22150%22 cy=%22130%22 r=%2218%22/%3E%3Ccircle cx=%2220%22 cy=%22100%22 r=%2222%22/%3E%3Ccircle cx=%22180%22 cy=%22160%22 r=%2212%22/%3E%3Ccircle cx=%2280%22 cy=%2280%22 r=%2210%22/%3E%3Ccircle cx=%22130%22 cy=%2220%22 r=%2214%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
            @php
                $flashType = session('error') ? 'error' : (session('success') ? 'success' : (session('info') ? 'info' : ''));
                $flashMsg = session('error') ?? session('success') ?? session('info') ?? '';
            @endphp
            <div x-data="flashToast({{ json_encode($flashType) }}, {{ json_encode($flashMsg) }})" class="fixed top-20 right-4 z-50 max-w-sm w-full pointer-events-auto">
                <template x-if="type === 'success'">
                    <div x-show="show" x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transform transition ease-in duration-300" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-full opacity-0" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 text-sm">
                        <svg class="w-5 h-5 shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="flex-1" x-text="msg"></span>
                        <button @click="show = false" class="shrink-0 text-green-400 hover:text-green-600">&times;</button>
                    </div>
                </template>
                <template x-if="type === 'error'">
                    <div x-show="show" x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transform transition ease-in duration-300" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-full opacity-0" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 text-sm">
                        <svg class="w-5 h-5 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="flex-1" x-text="msg"></span>
                        <button @click="show = false" class="shrink-0 text-red-400 hover:text-red-600">&times;</button>
                    </div>
                </template>
                <template x-if="type === 'info'">
                    <div x-show="show" x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transform transition ease-in duration-300" x-transition:leave-start="translate-x-0 opacity-100" x-transition:leave-end="translate-x-full opacity-0" class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 text-sm">
                        <svg class="w-5 h-5 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="flex-1" x-text="msg"></span>
                        <button @click="show = false" class="shrink-0 text-blue-400 hover:text-blue-600">&times;</button>
                    </div>
                </template>
            </div>
            @yield('content')
        </main>

        <x-chatbot-widget />

        <footer class="bg-gaf-dark-green text-gray-300 py-3 mt-2 relative">
            <div style="position:absolute;inset:0;pointer-events:none;opacity:0.05;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%23D4AF37%22 fill-opacity=%221%22%3E%3Cpath d=%22M0 0 L200 0 L200 200 L0 200 Z M30 30 Q60 10 100 30 Q140 50 170 30%22/%3E%3Cpath d=%22M10 80 Q40 60 80 80 Q120 100 160 80%22/%3E%3Cpath d=%22M20 130 Q60 110 100 130 Q140 150 180 130%22/%3E%3Cpath d=%22M5 170 Q50 150 100 170 Q150 190 195 170%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="flex flex-col md:flex-row items-center justify-between gap-1.5">
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:20px;width:auto;">
                        <span class="text-xs font-heading font-semibold text-white">Ghana Armed Forces Recruitment</span>
                    </div>
                    <div class="flex space-x-4 text-xs">
                        <a href="#" class="hover:text-gaf-khaki transition">Privacy</a>
                        <a href="#" class="hover:text-gaf-khaki transition">Terms</a>
                        <a href="#" class="hover:text-gaf-khaki transition">Disclaimer</a>
                    </div>
                    <div class="text-xs text-gray-500 text-center md:text-right">
                        <div>&copy; {{ date('Y') }} GAF. All rights reserved.</div>
                        <div class="mt-0.5">Built by <a href="https://github.com/DeryChris" target="_blank" rel="noopener noreferrer" class="text-gaf-khaki hover:underline font-medium">Nibenang</a></div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
