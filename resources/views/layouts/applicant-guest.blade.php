<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">
    <title>{{ isset($title) ? $title . ' - Ghana Armed Forces' : 'Ghana Armed Forces' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=montserrat:600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased" style="margin:0;padding:0;">
    <div x-data="{ mobileMenu: false }">
    @php $unsplashPhoto = unsplash_hero(); @endphp
    <nav class="bg-gaf-green text-white shadow-lg sticky top-0 z-40">
        <div class="flex items-center h-16 relative px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-3">
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:40px;width:auto;">
                <div class="hidden sm:block leading-tight">
                    <div class="font-heading font-bold text-sm uppercase tracking-wide">Ghana Armed Forces</div>
                    <div class="font-heading text-xs text-gaf-khaki/80">Recruitment Portal</div>
                </div>
            </div>
            <div class="hidden md:flex items-center justify-center absolute left-1/2 -translate-x-1/2 space-x-6">
                <a href="{{ route('landing') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">Home</a>
                <a href="{{ request()->routeIs('applicant.register') ? route('voucher.buy') : route('eligibility.checker') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">{{ request()->routeIs('applicant.register') ? 'Buy Voucher' : 'Eligibility Checker' }}</a>
                <a href="{{ route('announcements') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">Announcements</a>
                <a href="{{ route('guide') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">Guide</a>
                <a href="{{ route('faq') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">FAQ</a>
                <a href="{{ route('contact') }}" class="text-white hover:text-gaf-khaki transition text-sm font-medium">Contact</a>
            </div>
            <div class="flex items-center space-x-3 ml-auto">
                @if(request()->routeIs('applicant.login'))
                    <a href="{{ route('applicant.register') }}" class="bg-gaf-green text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Register</a>
                    <a href="{{ route('login') }}" class="bg-gaf-khaki text-gaf-green px-4 py-2 rounded-lg text-sm font-semibold hover:bg-yellow-500 transition">Admin</a>
                @elseif(request()->routeIs('applicant.register'))
                    <a href="{{ route('applicant.login') }}" class="bg-gaf-red text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 transition">Login</a>
                    <a href="{{ route('login') }}" class="bg-gaf-khaki text-gaf-green px-4 py-2 rounded-lg text-sm font-semibold hover:bg-yellow-500 transition">Admin</a>
                @else
                    <a href="{{ route('applicant.login') }}" class="bg-gaf-red text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 transition">Login</a>
                    <a href="{{ route('applicant.register') }}" class="bg-gaf-green text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Register</a>
                    <a href="{{ route('login') }}" class="bg-gaf-khaki text-gaf-green px-4 py-2 rounded-lg text-sm font-semibold hover:bg-yellow-500 transition">Admin</a>
                @endif
                <x-theme-toggle :dark="true" />
                <button @click="mobileMenu = !mobileMenu" class="md:hidden text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div x-show="mobileMenu" x-cloak class="md:hidden bg-gaf-green border-t border-gaf-dark-green px-4 py-3 space-y-2">
            <a href="{{ route('landing') }}" class="block text-white hover:text-gaf-khaki py-1 text-sm">Home</a>
            <a href="{{ request()->routeIs('applicant.register') ? route('voucher.buy') : route('eligibility.checker') }}" class="block text-white hover:text-gaf-khaki py-1 text-sm">{{ request()->routeIs('applicant.register') ? 'Buy Voucher' : 'Eligibility Checker' }}</a>
            <a href="{{ route('announcements') }}" class="block text-white hover:text-gaf-khaki py-1 text-sm">Announcements</a>
            <a href="{{ route('guide') }}" class="block text-white hover:text-gaf-khaki py-1 text-sm">Guide</a>
            <a href="{{ route('faq') }}" class="block text-white hover:text-gaf-khaki py-1 text-sm">FAQ</a>
            <a href="{{ route('contact') }}" class="block text-white hover:text-gaf-khaki py-1 text-sm">Contact</a>
        </div>
    </nav>
    <div class="auth-split">
        <div class="auth-split-left" style="position:relative;background-image:url('{{ $unsplashPhoto['regular_url'] ?? '' }}');background-size:cover;background-position:center;">
            <div style="position:absolute;inset:0;{{ $unsplashPhoto ? 'background:linear-gradient(135deg,rgba(20,83,45,0.88) 0%,rgba(15,47,31,0.92) 70%,rgba(155,34,38,0.85) 100%);' : 'background:linear-gradient(135deg, #14532d 0%, #0f2f1f 70%, #9B2226 100%);background-size:200% 200%;animation:gradientShift 15s ease infinite;' }}"></div>
            @if(!$unsplashPhoto)
            <div style="position:absolute;inset:0;overflow:hidden;pointer-events:none;">
                <div style="position:absolute;inset:0;opacity:0.04;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%23D4AF37%22 fill-opacity=%221%22%3E%3Cpath d=%22M0 0 L200 0 L200 200 L0 200 Z M30 30 Q60 10 100 30 Q140 50 170 30%22/%3E%3Cpath d=%22M10 80 Q40 60 80 80 Q120 100 160 80%22/%3E%3Cpath d=%22M20 130 Q60 110 100 130 Q140 150 180 130%22/%3E%3Cpath d=%22M5 170 Q50 150 100 170 Q150 190 195 170%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
                <div class="deco-circle" style="top:-10%;left:-5%;width:500px;height:500px;background:radial-gradient(circle,rgba(212,175,55,0.08),transparent 70%);animation-delay:0s;"></div>
                <div class="deco-circle" style="bottom:-15%;right:-8%;width:350px;height:350px;background:radial-gradient(circle,rgba(95,164,137,0.08),transparent 70%);animation-delay:3s;"></div>
                <div class="deco-circle" style="top:40%;right:-10%;width:200px;height:200px;background:radial-gradient(circle,rgba(212,175,55,0.05),transparent 70%);animation-delay:6s;"></div>
            </div>
            @endif
            <div class="auth-info-box">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:48px;width:auto;">
                    <div style="line-height:1.2;">
                        <div style="font-family:'Montserrat',sans-serif;font-weight:700;font-size:16px;color:#fff;letter-spacing:1px;">GHANA ARMED FORCES</div>
                        <div style="font-size:11px;color:rgba(255,255,255,0.65);letter-spacing:0.5px;">DEFENCE MANPOWER & RECORDS MANAGEMENT</div>
                    </div>
                </div>
                <h1>Applicant Recruitment Portal</h1>
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
            @if($unsplashPhoto && ($unsplashPhoto['attribution']['name'] ?? '') !== 'Unsplash')
            <div class="absolute bottom-3 right-4 z-20 text-xs text-white/40">
                Photo by <a href="{{ ($unsplashPhoto['attribution']['link'] ?? '#') }}?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">{{ $unsplashPhoto['attribution']['name'] ?? 'Unknown' }}</a> on <a href="https://unsplash.com/?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">Unsplash</a>
            </div>
            @endif
        </div>
        <div class="auth-split-right dark:bg-slate-800" style="background:linear-gradient(180deg, #ffffff 0%, #f8faf8 100%);position:relative;">
            <div style="position:absolute;inset:0;pointer-events:none;opacity:0.03;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%2314532d%22 fill-opacity=%221%22%3E%3Ccircle cx=%2230%22 cy=%2230%22 r=%2220%22/%3E%3Ccircle cx=%22170%22 cy=%2240%22 r=%2225%22/%3E%3Ccircle cx=%22100%22 cy=%22170%22 r=%2230%22/%3E%3Ccircle cx=%2260%22 cy=%22140%22 r=%2215%22/%3E%3Ccircle cx=%22150%22 cy=%22130%22 r=%2218%22/%3E%3Ccircle cx=%2220%22 cy=%22100%22 r=%2222%22/%3E%3Ccircle cx=%22180%22 cy=%22160%22 r=%2212%22/%3E%3Ccircle cx=%2280%22 cy=%2280%22 r=%2210%22/%3E%3Ccircle cx=%22130%22 cy=%2220%22 r=%2214%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
            <div class="auth-form-container" style="text-align:center;">
                <div class="auth-form-header">
                    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:56px;width:auto;margin-bottom:8px;">
                    <h2 style="font-family:'Montserrat',sans-serif;font-weight:700;font-size:18px;color:#1a202c;margin:0 0 2px;letter-spacing:0.3px;" class="dark:text-slate-100">{{ $title }}</h2>
                    <p style="font-size:12px;color:#64748b;margin:0;" class="dark:text-slate-400">{{ $subtitle }}</p>
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
