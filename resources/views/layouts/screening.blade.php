<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">
    <title>@yield('title', 'Screening - DMRMS')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=montserrat:600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <nav class="bg-gaf-green text-white px-6 py-3 flex items-center justify-between shadow-md sticky top-0 z-40">
            <div class="flex items-center space-x-3">
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:32px;width:auto;">
                <span class="font-heading font-semibold text-sm">Screening Officer</span>
            </div>
            <div class="flex items-center space-x-4 text-sm">
                <span class="text-gray-300">{{ date('l, F j, Y') }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-300 hover:text-white transition">Logout</button>
                </form>
            </div>
        </nav>

        <div class="flex-1 p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Today's Applicants</p>
                    <p class="text-2xl font-heading font-bold text-gaf-green mt-1">@yield('today-count', '0')</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Checked In</p>
                    <p class="text-2xl font-heading font-bold text-green-600 mt-1">@yield('checked-in-count', '0')</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Pending</p>
                    <p class="text-2xl font-heading font-bold text-gaf-red mt-1">@yield('pending-count', '0')</p>
                </div>
            </div>

            <main>
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
