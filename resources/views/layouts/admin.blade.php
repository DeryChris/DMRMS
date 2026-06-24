<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">
    <title>@yield('title', 'Admin - DMRMS')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=montserrat:600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">
<div x-data="{ sidebarCollapsed: false, activeGroup: null }" class="flex h-screen overflow-hidden">
    <aside x-bind:class="sidebarCollapsed ? 'w-16' : 'w-64'" class="bg-gaf-dark-green text-white flex-shrink-0 transition-all duration-300 flex flex-col">
        <div class="p-4 flex items-center justify-between border-b border-gray-700">
            <div x-show="!sidebarCollapsed" class="flex items-center space-x-2">
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:32px;width:auto;">
                <span class="font-heading font-bold text-sm">DMRMS Admin</span>
            </div>
            <button @click="sidebarCollapsed = !sidebarCollapsed" class="text-gray-400 hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto p-2 space-y-1">
            <x-admin-nav-item icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" :collapsed="sidebarCollapsed" route="admin.dashboard" label="Dashboard" />

            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition" :class="{'justify-center': sidebarCollapsed}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span x-show="!sidebarCollapsed">Applications</span>
                    <svg x-show="!sidebarCollapsed" class="w-4 h-4 ml-auto" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open && !sidebarCollapsed" x-cloak class="ml-8 space-y-1 mt-1">
                    <a href="{{ route('admin.applications') }}" class="block px-3 py-2 text-xs text-gray-400 hover:text-white rounded">All Applications</a>
                    <a href="{{ route('admin.application-detail', ['id' => 0]) }}" class="block px-3 py-2 text-xs text-gray-400 hover:text-white rounded">Application Detail</a>
                </div>
            </div>
            <a href="{{ route('admin.cycles') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition" :class="{'justify-center': sidebarCollapsed}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span x-show="!sidebarCollapsed">Cycles</span>
            </a>
            <a href="{{ route('admin.scheduling') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition" :class="{'justify-center': sidebarCollapsed}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span x-show="!sidebarCollapsed">Scheduling</span>
            </a>
            <a href="{{ route('admin.screening-results') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition" :class="{'justify-center': sidebarCollapsed}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span x-show="!sidebarCollapsed">Screening</span>
            </a>
            <a href="{{ route('admin.selection') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition" :class="{'justify-center': sidebarCollapsed}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="!sidebarCollapsed">Selection</span>
            </a>
            <a href="{{ route('admin.reports') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition" :class="{'justify-center': sidebarCollapsed}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span x-show="!sidebarCollapsed">Reports</span>
            </a>

            @if(auth()->user() && auth()->user()->role === 'super_admin')
            <div class="border-t border-gray-700 pt-2 mt-2">
                <a href="{{ route('admin.users') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition" :class="{'justify-center': sidebarCollapsed}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                    <span x-show="!sidebarCollapsed">Users</span>
                </a>
                <a href="{{ route('admin.ai-config') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition" :class="{'justify-center': sidebarCollapsed}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span x-show="!sidebarCollapsed">AI Settings</span>
                </a>
                <a href="{{ route('admin.audit-logs') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition" :class="{'justify-center': sidebarCollapsed}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-show="!sidebarCollapsed">Audit Logs</span>
                </a>
            </div>
            @endif
        </nav>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm px-6 py-3 flex items-center justify-between flex-shrink-0 sticky top-0 z-30">
            <div class="flex items-center space-x-4 flex-1">
                <div class="relative max-w-md w-full">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" placeholder="Search applicants..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-transparent">
                </div>
            </div>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center space-x-2 text-sm text-gray-700 hover:text-gray-900">
                    <div class="w-8 h-8 bg-gaf-green rounded-full flex items-center justify-center text-white font-semibold text-xs">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <span class="hidden sm:block">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-1 z-50">
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Logout</button>
                    </form>
                </div>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
            @yield('content')
        </main>
    </div>
</div>
@stack('charts')
@stack('scripts')
</body>
</html>
