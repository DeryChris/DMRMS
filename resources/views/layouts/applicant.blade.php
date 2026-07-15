@extends('layouts.app')

@section('styles')
    @parent
    @stack('styles')
@endsection

@section('content')
<div x-data="{ sidebarOpen: window.innerWidth >= 768 }" class="flex min-h-screen dark:bg-slate-900 dark:text-slate-100" style="background:linear-gradient(180deg, #f0f7f0 0%, #f8faf8 50%, #f0f7f0 100%);" @resize.window="sidebarOpen = window.innerWidth >= 768">
    @php $applicant = auth('applicant')->user(); @endphp
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 bg-black/30 z-20 md:hidden"></div>
    <aside x-show="sidebarOpen" x-cloak class="w-64 bg-white dark:bg-slate-800 shadow-lg fixed md:relative z-30 h-full md:h-auto gradient-border-right" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" style="border-right:1px solid rgba(20,83,45,0.1);">
        <div class="p-6 border-b text-center">
            <div class="w-16 h-16 bg-gaf-khaki rounded-full mx-auto flex items-center justify-center text-white font-heading font-bold text-xl">
                {{ substr($applicant->name ?? 'A', 0, 1) }}
            </div>
            <h3 class="font-heading font-semibold text-sm mt-2">{{ $applicant->name ?? 'Applicant' }}</h3>
            <p class="text-xs text-gray-500">GAF-{{ optional($applicant->application)->gaf_id ?? '000000' }}</p>
        </div>
        <nav @click="if (window.innerWidth < 768 && $event.target.closest('a')) sidebarOpen = false" class="p-4 space-y-1">
            @php $appStatus = optional($applicant->application)->status ?? 'registered'; @endphp
            <a href="{{ route('applicant.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-sm {{ request()->routeIs('applicant.dashboard') ? 'bg-gaf-green text-white' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>
            @if(in_array($appStatus, ['registered', 'draft']))
            <a href="{{ route('applicant.application') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-sm {{ request()->routeIs('applicant.application') ? 'bg-gaf-green text-white' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>My Application</span>
            </a>
            @endif
            @if(in_array($appStatus, ['registered', 'draft', 'submitted']))
            <a href="{{ route('applicant.documents') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-sm {{ request()->routeIs('applicant.documents') ? 'bg-gaf-green text-white' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Documents</span>
            </a>
            @endif
            @if(!in_array($appStatus, ['registered', 'draft']))
            <a href="{{ route('applicant.status') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-sm {{ request()->routeIs('applicant.status') ? 'bg-gaf-green text-white' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Status Timeline</span>
            </a>
            @endif
            @if(in_array($appStatus, ['appointment_scheduled', 'screening_completed']))
            <a href="{{ route('applicant.appointment') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-sm {{ request()->routeIs('applicant.appointment') ? 'bg-gaf-green text-white' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Appointment</span>
            </a>
            @endif
            @php $unreadNotifCount = $applicant->notifications()->unread()->count(); @endphp
            <a href="{{ route('applicant.notifications') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-sm {{ request()->routeIs('applicant.notifications') ? 'bg-gaf-green text-white' : 'text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="flex-1">Notifications</span>
                @if($unreadNotifCount > 0)
                <span class="px-2 py-0.5 text-xs rounded-full font-bold {{ request()->routeIs('applicant.notifications') ? 'bg-white/20 text-white' : 'bg-gaf-red text-white' }}">{{ $unreadNotifCount }}</span>
                @endif
            </a>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-h-screen">
        <header class="px-6 py-3 flex items-center justify-between gradient-border dark:bg-slate-800" style="background:linear-gradient(135deg, #f8faf8 0%, #ffffff 100%);">
            <div class="flex items-center space-x-3">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                @unless(request()->routeIs('applicant.dashboard'))
                <a href="{{ route('applicant.dashboard') }}" class="inline-flex items-center space-x-1.5 text-sm text-gaf-green dark:text-gaf-khaki hover:text-gaf-dark-green dark:hover:text-yellow-400 font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>
                @endunless
            </div>
        </header>

        <main class="flex-1 p-6 pb-20 md:pb-6 relative dark:text-slate-100">
            <div style="position:absolute;inset:0;pointer-events:none;opacity:0.02;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%2314532d%22 fill-opacity=%221%22%3E%3Ccircle cx=%2230%22 cy=%2230%22 r=%2220%22/%3E%3Ccircle cx=%22170%22 cy=%2240%22 r=%2225%22/%3E%3Ccircle cx=%22100%22 cy=%22170%22 r=%2230%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
            <div class="relative z-10">
                <div x-data="{ show: true, type: '{{ session('error') ? 'error' : (session('success') ? 'success' : (session('info') ? 'info' : '')) }}', msg: '{{ session('error') ?? session('success') ?? session('info') ?? '' }}' }"
                     x-show="show && msg"
                     x-init="if (msg) setTimeout(() => show = false, 4000)"
                     x-transition:enter="transform transition ease-out duration-300"
                     x-transition:enter-start="translate-x-full opacity-0"
                     x-transition:enter-end="translate-x-0 opacity-100"
                     x-transition:leave="transform transition ease-in duration-300"
                     x-transition:leave-start="translate-x-0 opacity-100"
                     x-transition:leave-end="translate-x-full opacity-0"
                     class="fixed top-20 right-4 z-50 max-w-sm w-full pointer-events-auto">
                    <template x-if="type === 'success'">
                        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 text-sm">
                            <svg class="w-5 h-5 shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="flex-1" x-text="msg"></span>
                            <button @click="show = false" class="shrink-0 text-green-400 hover:text-green-600">&times;</button>
                        </div>
                    </template>
                    <template x-if="type === 'error'">
                        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 text-sm">
                            <svg class="w-5 h-5 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="flex-1" x-text="msg"></span>
                            <button @click="show = false" class="shrink-0 text-red-400 hover:text-red-600">&times;</button>
                        </div>
                    </template>
                    <template x-if="type === 'info'">
                        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 text-sm">
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
@endsection

@section('scripts')
    @stack('scripts')
@endsection
