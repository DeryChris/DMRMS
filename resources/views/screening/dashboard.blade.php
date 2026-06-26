@extends('layouts.screening')

@section('title', 'Screening Dashboard - Ghana Armed Forces')

@section('today-count', $todayCount)
@section('checked-in-count', $checkedInCount)
@section('pending-count', $pendingCount)

@php use Carbon\Carbon; @endphp

@section('content')
<div class="space-y-6">
    <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
        <h2 class="font-heading font-semibold text-lg text-gray-800 mb-4">Quick Verify</h2>
        <div class="flex space-x-3">
            <input type="text" placeholder="Enter verification code or GAF ID" class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
            <button class="px-6 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Verify</button>
        </div>
    </div>

    <div class="glass-strong rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-heading font-semibold text-lg text-gray-800">Today's Schedule</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="table-header-gradient"><th class="px-6 py-3 text-left text-white/90">Slot</th><th class="px-6 py-3 text-left text-white/90">Applicant</th><th class="px-6 py-3 text-left text-white/90">GAF ID</th><th class="px-6 py-3 text-left text-white/90">Time</th><th class="px-6 py-3 text-left text-white/90">Status</th><th class="px-6 py-3 text-right text-white/90">Action</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($todayAppointments as $appt)
                @php
                    $applicant = $appt->application->applicant;
                    $statusMap = config('recruitment.appointment_statuses');
                    $statusKey = $appt->checked_in_at ? 'checked_in' : ($appt->status ?? 'scheduled');
                    $statusLabel = $statusMap[$statusKey]['label'] ?? ucfirst(str_replace('_', ' ', $appt->status ?? 'scheduled'));
                    $statusClasses = $statusMap[$statusKey]['color'] ?? 'bg-gray-100 text-gray-700';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-mono text-xs">SLOT-{{ str_pad($appt->slot_number ?? $loop->iteration, 4, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-6 py-3 font-medium">{{ $applicant->name ?? 'N/A' }}</td>
                    <td class="px-6 py-3 font-mono text-xs">{{ $appt->application->gaf_id ?? 'N/A' }}</td>
                    <td class="px-6 py-3">{{ $appt->scheduled_time ? Carbon::parse($appt->scheduled_time)->format('g:i A') : 'N/A' }}</td>
                    <td class="px-6 py-3">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $statusClasses }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-right">
                        <div class="flex items-center justify-end space-x-1">
                            <a href="{{ route('screening.medical', ['application_id' => $appt->application_id]) }}" class="relative group p-1.5 rounded-lg hover:bg-red-50 text-red-500 transition-colors" title="Medical">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Medical</span>
                            </a>
                            <a href="{{ route('screening.fitness', ['application_id' => $appt->application_id]) }}" class="relative group p-1.5 rounded-lg hover:bg-gaf-green/10 text-gaf-green transition-colors" title="Fitness">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Fitness</span>
                            </a>
                            <a href="{{ route('screening.interview', ['application_id' => $appt->application_id]) }}" class="relative group p-1.5 rounded-lg hover:bg-blue-50 text-blue-600 transition-colors" title="Interview">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Interview</span>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm font-medium">No appointments scheduled for today</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
