<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">
    <title>@yield('title', 'Screening - Ghana Armed Forces')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=montserrat:600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased dark:bg-slate-900 dark:text-slate-100" style="background:linear-gradient(180deg, #f0f7f0 0%, #f8faf8 50%, #f0f7f0 100%);">
    <div class="min-h-screen flex flex-col">
        <nav class="bg-gaf-green text-white px-6 py-3 flex items-center justify-between shadow-md sticky top-0 z-40 gradient-border">
            <div class="flex items-center space-x-3">
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:32px;width:auto;">
                <span class="font-heading font-semibold text-sm">Screening Officer</span>
            </div>
            <div class="flex items-center space-x-4 text-sm">
                <x-theme-toggle :dark="true" />
                <span class="text-gray-300">{{ date('l, F j, Y') }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-300 hover:text-white transition">Logout</button>
                </form>
            </div>
        </nav>

        <div class="flex-1 p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="rounded-xl shadow-sm p-4 relative overflow-hidden" style="background:linear-gradient(135deg, #14532d 0%, #166534 100%);">
                    <div style="position:absolute;inset:0;pointer-events:none;opacity:0.06;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%23D4AF37%22 fill-opacity=%221%22%3E%3Cpath d=%22M0 0 L200 0 L200 200 L0 200 Z M30 30 Q60 10 100 30 Q140 50 170 30%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
                    <div class="relative z-10">
                        <p class="text-xs text-white/70 uppercase tracking-wide">Today's Applicants</p>
                        <p class="text-2xl font-heading font-bold text-white mt-1">@yield('today-count', '0')</p>
                    </div>
                </div>
                <div class="rounded-xl shadow-sm p-4 relative overflow-hidden" style="background:linear-gradient(135deg, #0D9488 0%, #115E59 100%);">
                    <div style="position:absolute;inset:0;pointer-events:none;opacity:0.06;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22%3E%3Cpath d=%22M0 0 L200 0 L200 200 L0 200 Z M30 30 Q60 10 100 30 Q140 50 170 30%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
                    <div class="relative z-10">
                        <p class="text-xs text-white/70 uppercase tracking-wide">Checked In</p>
                        <p class="text-2xl font-heading font-bold text-white mt-1">@yield('checked-in-count', '0')</p>
                    </div>
                </div>
                <div class="rounded-xl shadow-sm p-4 relative overflow-hidden" style="background:linear-gradient(135deg, #9B2226 0%, #7F1D1D 100%);">
                    <div style="position:absolute;inset:0;pointer-events:none;opacity:0.06;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22%3E%3Cpath d=%22M0 0 L200 0 L200 200 L0 200 Z M30 30 Q60 10 100 30 Q140 50 170 30%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
                    <div class="relative z-10">
                        <p class="text-xs text-white/70 uppercase tracking-wide">Pending</p>
                        <p class="text-2xl font-heading font-bold text-white mt-1">@yield('pending-count', '0')</p>
                    </div>
                </div>
            </div>

            <main>
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
        </div>
    </div>
    @stack('scripts')
</body>
</html>
