@extends('layouts.app')

@section('styles')
    @stack('styles')
@endsection

@section('content')
<div x-data="{ sidebarOpen: true }" class="flex min-h-screen bg-gray-100">
    @php $applicant = auth('applicant')->user(); @endphp
    <aside x-show="sidebarOpen" x-cloak class="w-64 bg-white shadow-lg fixed md:relative z-30 h-full md:h-auto" x-transition>
        <div class="p-6 border-b text-center">
            <div class="w-16 h-16 bg-gaf-khaki rounded-full mx-auto flex items-center justify-center text-white font-heading font-bold text-xl">
                {{ substr($applicant->name ?? 'A', 0, 1) }}
            </div>
            <h3 class="font-heading font-semibold text-sm mt-2">{{ $applicant->name ?? 'Applicant' }}</h3>
            <p class="text-xs text-gray-500">GAF-{{ optional($applicant->application)->gaf_id ?? '000000' }}</p>
        </div>
        <nav class="p-4 space-y-1">
            <a href="{{ route('applicant.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-sm {{ request()->routeIs('applicant.dashboard') ? 'bg-gaf-green text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('applicant.application') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-sm {{ request()->routeIs('applicant.application') ? 'bg-gaf-green text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>My Application</span>
            </a>
            <a href="{{ route('applicant.documents') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-sm {{ request()->routeIs('applicant.documents') ? 'bg-gaf-green text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Documents</span>
            </a>
            <a href="{{ route('applicant.status') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-sm {{ request()->routeIs('applicant.status') ? 'bg-gaf-green text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Status Timeline</span>
            </a>
            <a href="{{ route('applicant.appointment') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-sm {{ request()->routeIs('applicant.appointment') ? 'bg-gaf-green text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Appointment</span>
            </a>
            <a href="{{ route('applicant.notifications') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-sm {{ request()->routeIs('applicant.notifications') ? 'bg-gaf-green text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Notifications</span>
            </a>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-h-screen">
        <header class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <x-notification-bell />
        </header>

        <main class="flex-1 p-6">
            @yield('content')
        </main>

        <footer class="bg-white border-t px-6 py-3 text-xs text-gray-500 flex items-center justify-between">
            <span>Current Stage: <span class="font-semibold text-gaf-green">{{ $currentStage ?? 'Registration' }}</span></span>
            <span>DMRMS v1.0</span>
        </footer>
    </div>
</div>
@endsection

@section('scripts')
    @stack('scripts')
@endsection
