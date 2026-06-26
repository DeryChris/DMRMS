<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}">
    <title>@yield('title', 'Admin - Ghana Armed Forces')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=montserrat:600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
<div x-data="{ sidebarCollapsed: false }" class="flex h-screen overflow-hidden">
    <aside x-bind:class="sidebarCollapsed ? 'w-16' : 'w-64'" class="bg-gaf-dark-green text-white flex-shrink-0 transition-all duration-300 flex flex-col shadow-xl z-20 h-full relative">
        <div style="position:absolute;inset:0;pointer-events:none;opacity:0.04;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%23D4AF37%22 fill-opacity=%221%22%3E%3Cpath d=%22M0 0 L200 0 L200 200 L0 200 Z M30 30 Q60 10 100 30 Q140 50 170 30%22/%3E%3Cpath d=%22M10 80 Q40 60 80 80 Q120 100 160 80%22/%3E%3Cpath d=%22M20 130 Q60 110 100 130 Q140 150 180 130%22/%3E%3Cpath d=%22M5 170 Q50 150 100 170 Q150 190 195 170%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
        <div class="h-16 flex items-center justify-between px-4 border-b border-white/10 flex-shrink-0 relative z-10">
            <div x-show="!sidebarCollapsed" class="flex items-center space-x-3">
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="GAF" style="height:30px;width:auto;">
                <div class="leading-tight">
                    <div class="font-heading font-bold text-sm tracking-wide">DMRMS</div>
                    <div class="text-xs text-white/40">Admin Panel</div>
                </div>
            </div>
            <button @click="sidebarCollapsed = !sidebarCollapsed" class="text-white/40 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto p-3 space-y-0.5 min-h-0 scrollbar-hide relative z-10">
            <div class="px-3 pb-2 text-xs text-white/30 uppercase tracking-wider font-semibold" x-show="!sidebarCollapsed">Main Menu</div>
            <x-admin-nav-item icon="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" route="admin.dashboard" label="Dashboard" />

            @php $isApplications = request()->routeIs('admin.applications'); @endphp
            <a href="{{ route('admin.applications') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isApplications ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span x-show="!sidebarCollapsed">Applications</span>
            </a>
            @hasanyrole('admin|super_admin|recruitment_officer')
            <div x-data="{ open: false }">
                @php $isApplicants = request()->routeIs('admin.applicants*') || request()->routeIs('admin.recruited*'); @endphp
                <button @click="open = !open" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isApplicants ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                    <span x-show="!sidebarCollapsed" class="flex-1 text-left">Applicants</span>
                    <svg x-show="!sidebarCollapsed" class="w-4 h-4 text-white/30 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open && !sidebarCollapsed" x-cloak class="ml-9 space-y-0.5 mt-0.5 border-l border-white/10 pl-3">
                    <a href="{{ route('admin.applicants') }}" class="block px-3 py-2 text-xs text-white/50 hover:text-white hover:bg-white/10 rounded-lg transition-all {{ request()->routeIs('admin.applicants*') ? 'text-white' : '' }}">All Applicants</a>
                    <a href="{{ route('admin.recruited') }}" class="block px-3 py-2 text-xs text-white/50 hover:text-white hover:bg-white/10 rounded-lg transition-all {{ request()->routeIs('admin.recruited*') ? 'text-white' : '' }}">Recruited</a>
                </div>
            </div>
            @endhasanyrole
            @hasanyrole('admin|super_admin|recruitment_officer')
            @php $isCycles = request()->routeIs('admin.cycles'); @endphp
            <a href="{{ route('admin.cycles') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isCycles ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span x-show="!sidebarCollapsed">Cycles</span>
            </a>
            @endhasanyrole
            @hasanyrole('admin|super_admin|scheduling_officer')
            @php $isScheduling = request()->routeIs('admin.scheduling'); @endphp
            <a href="{{ route('admin.scheduling') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isScheduling ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span x-show="!sidebarCollapsed">Scheduling</span>
            </a>
            @endhasanyrole
            @hasanyrole('admin|super_admin|screening_officer')
            @php $isScreening = request()->routeIs('admin.screening-results'); @endphp
            <a href="{{ route('admin.screening-results') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isScreening ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span x-show="!sidebarCollapsed">Screening</span>
            </a>
            @endhasanyrole
            @hasanyrole('admin|super_admin')
            @php $isSelection = request()->routeIs('admin.selection'); @endphp
            <a href="{{ route('admin.selection') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isSelection ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="!sidebarCollapsed">Selection</span>
            </a>
            @php $isKpi = request()->routeIs('admin.kpi'); @endphp
            <a href="{{ route('admin.kpi') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isKpi ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span x-show="!sidebarCollapsed">KPIs</span>
            </a>
            @php $isReports = request()->routeIs('admin.reports'); @endphp
            <a href="{{ route('admin.reports') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isReports ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span x-show="!sidebarCollapsed">Reports</span>
            </a>
            @php $isNews = request()->routeIs('admin.announcements*'); @endphp
            <a href="{{ route('admin.announcements.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isNews ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                <span x-show="!sidebarCollapsed">News Feed</span>
            </a>
            @endhasanyrole

            @if(auth()->user() && auth()->user()->role === 'super_admin')
            <div class="border-t border-white/10 pt-3 mt-3">
                <div x-show="!sidebarCollapsed" class="px-3 pb-2 text-xs text-white/30 uppercase tracking-wider font-semibold">Administration</div>
                @php $isUsers = request()->routeIs('admin.users'); @endphp
                <a href="{{ route('admin.users') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isUsers ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                    <span x-show="!sidebarCollapsed">Users</span>
                </a>
                @php $isAi = request()->routeIs('admin.ai-config'); @endphp
                <a href="{{ route('admin.ai-config') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isAi ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span x-show="!sidebarCollapsed">AI Settings</span>
                </a>
                @php $isAudit = request()->routeIs('admin.audit-logs'); @endphp
                <a href="{{ route('admin.audit-logs') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isAudit ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-show="!sidebarCollapsed">Audit Logs</span>
                </a>
                @php $isSendNotif = request()->routeIs('admin.notifications.*'); @endphp
                <a href="{{ route('admin.notifications.create') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isSendNotif ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span x-show="!sidebarCollapsed">Send Notification</span>
                </a>
                @php $isBackups = request()->routeIs('admin.backups*'); @endphp
                <a href="{{ route('admin.backups.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isBackups ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                    <span x-show="!sidebarCollapsed">Backups</span>
                </a>
                @php $isSecurity = request()->routeIs('admin.security*'); @endphp
                <a href="{{ route('admin.security.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isSecurity ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span x-show="!sidebarCollapsed">Security</span>
                </a>
                @php $isBarracks = request()->routeIs('admin.barracks*'); @endphp
                <a href="{{ route('admin.barracks.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm transition-all group {{ $isBarracks ? 'bg-gaf-green text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}" x-bind:class="{'justify-center': sidebarCollapsed}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span x-show="!sidebarCollapsed">Barracks</span>
                </a>
            </div>
            @endif
        </nav>
        <div class="border-t border-white/10 p-3 flex-shrink-0">
            <div class="flex items-center space-x-3 px-3 py-2 rounded-lg">
                <div class="w-2 h-2 rounded-full bg-gaf-khaki"></div>
                <span x-show="!sidebarCollapsed" class="text-xs text-white/30">DMRMS v1.0</span>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
        <header class="bg-white shadow-sm px-6 py-3 flex items-center justify-between flex-shrink-0 sticky top-0 z-30 gradient-border" style="background:linear-gradient(135deg, #f8faf8 0%, #ffffff 100%);">
            <div class="flex items-center space-x-4 flex-1 min-w-0">
                <button @click="sidebarCollapsed = !sidebarCollapsed" class="lg:hidden text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="relative max-w-md w-full">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" placeholder="Search applicants..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki/30 focus:border-gaf-khaki focus:bg-white transition-all outline-none">
                </div>
            </div>
            <div class="flex items-center space-x-2 flex-shrink-0">
                <x-notification-bell />
                <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center space-x-3 text-sm text-gray-700 hover:text-gray-900 p-1.5 rounded-lg hover:bg-gray-100 transition-all">
                    <div class="w-8 h-8 bg-gaf-green rounded-full flex items-center justify-center text-white font-semibold text-xs shadow-sm">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <span class="hidden sm:block font-medium">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" @click.outside="open = false" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-50">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                    <a href="#" class="flex items-center space-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Profile</span>
                    </a>
                    <div class="border-t border-gray-100 mt-1 pt-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center space-x-2 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto min-h-0 relative" style="background:linear-gradient(180deg, #f0f7f0 0%, #f8faf8 50%, #f0f7f0 100%);">
            <div style="position:absolute;inset:0;pointer-events:none;opacity:0.025;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22%3E%3Cg fill=%22%2314532d%22 fill-opacity=%221%22%3E%3Ccircle cx=%2210%22 cy=%2210%22 r=%222%22/%3E%3Ccircle cx=%2250%22 cy=%2210%22 r=%222%22/%3E%3Ccircle cx=%2290%22 cy=%2210%22 r=%222%22/%3E%3Ccircle cx=%2210%22 cy=%2250%22 r=%222%22/%3E%3Ccircle cx=%2250%22 cy=%2250%22 r=%223%22/%3E%3Ccircle cx=%2290%22 cy=%2250%22 r=%222%22/%3E%3Ccircle cx=%2210%22 cy=%2290%22 r=%222%22/%3E%3Ccircle cx=%2250%22 cy=%2290%22 r=%222%22/%3E%3Ccircle cx=%2290%22 cy=%2290%22 r=%222%22/%3E%3Cline x1=%2210%22 y1=%2210%22 x2=%2250%22 y2=%2210%22 stroke=%22%2314532d%22 stroke-opacity=%221%22 stroke-width=%220.5%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
            <div class="max-w-7xl mx-auto px-6 py-6 relative z-10">
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
            </div>
        </main>
    </div>
</div>
@stack('charts')
@stack('scripts')
</body>
</html>
