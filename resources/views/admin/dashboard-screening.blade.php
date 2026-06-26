@php use Carbon\Carbon; @endphp
@extends('layouts.admin')
@section('title', 'Screening Dashboard - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gradient-border pb-4">
        <div>
            <h1 class="text-2xl font-heading font-bold text-gaf-dark-green">Screening Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Today's screening activity overview</p>
        </div>
        <span class="text-sm text-gray-500 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-2">
            <svg class="w-4 h-4 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>{{ now()->format('l, F j, Y') }}</span>
        </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl shadow-sm p-4 relative overflow-hidden" style="background:linear-gradient(135deg, #14532d 0%, #166534 100%);">
            <div style="position:absolute;inset:0;pointer-events:none;opacity:0.06;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%23D4AF37%22 fill-opacity=%221%22%3E%3Cpath d=%22M0 0 L200 0 L200 200 L0 200 Z M30 30 Q60 10 100 30 Q140 50 170 30%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
            <div class="relative z-10">
                <p class="text-xs text-white/70 uppercase tracking-wide">Today's Applicants</p>
                <p class="text-2xl font-heading font-bold text-white mt-1">{{ $todayCount }}</p>
            </div>
        </div>
        <div class="rounded-xl shadow-sm p-4 relative overflow-hidden" style="background:linear-gradient(135deg, #0D9488 0%, #115E59 100%);">
            <div style="position:absolute;inset:0;pointer-events:none;opacity:0.06;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22%3E%3Cpath d=%22M0 0 L200 0 L200 200 L0 200 Z M30 30 Q60 10 100 30 Q140 50 170 30%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
            <div class="relative z-10">
                <p class="text-xs text-white/70 uppercase tracking-wide">Checked In</p>
                <p class="text-2xl font-heading font-bold text-white mt-1">{{ $checkedInCount }}</p>
            </div>
        </div>
        <div class="rounded-xl shadow-sm p-4 relative overflow-hidden" style="background:linear-gradient(135deg, #9B2226 0%, #7F1D1D 100%);">
            <div style="position:absolute;inset:0;pointer-events:none;opacity:0.06;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22%3E%3Cpath d=%22M0 0 L200 0 L200 200 L0 200 Z M30 30 Q60 10 100 30 Q140 50 170 30%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
            <div class="relative z-10">
                <p class="text-xs text-white/70 uppercase tracking-wide">Pending</p>
                <p class="text-2xl font-heading font-bold text-white mt-1">{{ $pendingCount }}</p>
            </div>
        </div>
        <div class="rounded-xl shadow-sm p-4 relative overflow-hidden" style="background:linear-gradient(135deg, #2e6b3e 0%, #4a7f55 100%);">
            <div style="position:absolute;inset:0;pointer-events:none;opacity:0.06;background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%23D4AF37%22 fill-opacity=%221%22%3E%3Cpath d=%22M0 0 L200 0 L200 200 L0 200 Z M30 30 Q60 10 100 30 Q140 50 170 30%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;"></div>
            <div class="relative z-10">
                <p class="text-xs text-white/70 uppercase tracking-wide">Results Recorded Today</p>
                <p class="text-2xl font-heading font-bold text-white mt-1">{{ $resultsToday }}</p>
            </div>
        </div>
    </div>

    <div class="glass-strong rounded-xl shadow-sm overflow-hidden gradient-border-left">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-heading font-bold text-gaf-dark-green">Today's <span class="text-gray-400 font-normal">Schedule</span></h3>
            <span class="text-xs text-gray-400">{{ $todayCount }} appointments</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-5 py-3 font-medium">Slot</th>
                        <th class="px-5 py-3 font-medium">Applicant</th>
                        <th class="px-5 py-3 font-medium">GAF ID</th>
                        <th class="px-5 py-3 font-medium">Time</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($todayAppointments as $appt)
                    @php
                        $applicant = $appt->application->applicant;
                        $statusMap = config('recruitment.appointment_statuses');
                        $statusKey = $appt->checked_in_at ? 'checked_in' : ($appt->status ?? 'scheduled');
                        $statusLabel = $statusMap[$statusKey]['label'] ?? ucfirst(str_replace('_', ' ', $appt->status ?? 'scheduled'));
                        $statusClasses = $statusMap[$statusKey]['color'] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-3.5 font-mono text-xs text-gray-500">SLOT-{{ str_pad($appt->slot_number ?? $loop->iteration, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-5 py-3.5 font-medium text-gray-900">{{ $applicant->name ?? 'N/A' }}</td>
                        <td class="px-5 py-3.5 font-mono text-xs text-gray-500">{{ $appt->application->gaf_id ?? 'N/A' }}</td>
                        <td class="px-5 py-3.5 text-gray-500">{{ $appt->scheduled_time ? Carbon::parse($appt->scheduled_time)->format('g:i A') : 'N/A' }}</td>
                        <td class="px-5 py-3.5">
                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $statusClasses }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end space-x-1">
                                <a href="{{ route('screening.medical', ['application_id' => $appt->application_id]) }}" class="relative group p-1.5 rounded-lg hover:bg-gaf-green/10 text-gaf-green transition-colors inline-flex items-center" title="Medical">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 0v4m0-4h4m-4 0H8"/></svg>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Medical</span>
                                </a>
                                <a href="{{ route('screening.fitness', ['application_id' => $appt->application_id]) }}" class="relative group p-1.5 rounded-lg hover:bg-gaf-green/10 text-gaf-green transition-colors inline-flex items-center" title="Fitness">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Fitness</span>
                                </a>
                                <a href="{{ route('screening.interview', ['application_id' => $appt->application_id]) }}" class="relative group p-1.5 rounded-lg hover:bg-gaf-green/10 text-gaf-green transition-colors inline-flex items-center" title="Interview">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Interview</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-sm font-medium">No appointments scheduled for today</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('screening.dashboard') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-gaf-green/30 transition-all group flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-gaf-green/10 flex items-center justify-center group-hover:bg-gaf-green/20 transition-colors">
                <svg class="w-6 h-6 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 group-hover:text-gaf-dark-green transition-colors">Open Screening Portal</p>
                <p class="text-xs text-gray-500 mt-0.5">Verify entries, record medical/fitness/interview results</p>
            </div>
            <svg class="w-5 h-5 text-gray-300 group-hover:text-gaf-green ml-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('admin.screening-results') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-gaf-dark-green/30 transition-all group flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-gaf-dark-green/10 flex items-center justify-center group-hover:bg-gaf-dark-green/20 transition-colors">
                <svg class="w-6 h-6 text-gaf-dark-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 group-hover:text-gaf-dark-green transition-colors">View Screening Results</p>
                <p class="text-xs text-gray-500 mt-0.5">Review all recorded screening outcomes</p>
            </div>
            <svg class="w-5 h-5 text-gray-300 group-hover:text-gaf-dark-green ml-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
</div>
@endsection
