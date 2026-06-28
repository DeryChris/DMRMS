@extends('layouts.applicant')

@section('title', 'Appointment - Ghana Armed Forces')

@section('content')
<div class="max-w-4xl mx-auto px-4">
    <div class="gradient-border pb-4 mb-6">
        <h1 class="font-heading font-bold text-2xl text-gray-800">My Appointment</h1>
        <p class="text-gray-500 text-sm mt-1">View your screening appointment details.</p>
    </div>

    @if($appointment)
    <div class="bg-white border border-gray-200 rounded-xl p-8 mb-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-heading font-semibold text-xl text-gray-800">Screening Appointment</h2>
            <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $appointment->status === 'scheduled' ? 'bg-blue-100 text-blue-700' : ($appointment->status === 'checked_in' ? 'bg-teal-100 text-teal-700' : ($appointment->status === 'attended' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700')) }}">{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-gaf-green mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <div><p class="text-sm font-medium text-gray-800">Date</p><p class="text-sm text-gray-500">{{ $appointment->scheduled_date?->format('F j, Y') ?? 'TBD' }}</p></div>
            </div>
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-gaf-green mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div><p class="text-sm font-medium text-gray-800">Time</p><p class="text-sm text-gray-500">{{ $appointment->scheduled_time ?? 'TBD' }}</p></div>
            </div>
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-gaf-green mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <div><p class="text-sm font-medium text-gray-800">Venue</p><p class="text-sm text-gray-500">{{ $appointment->venue ?? 'GAF Recruitment Center, Burma Camp, Accra' }}</p></div>
            </div>
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-gaf-green mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <div><p class="text-sm font-medium text-gray-800">Slot Reference</p><p class="text-sm text-gray-500">SLOT-{{ str_pad($appointment->slot_number, 3, '0', STR_PAD_LEFT) }}</p></div>
            </div>
        </div>
        @if($appointment->checked_in_at)
        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center space-x-2 text-sm text-green-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Checked in at {{ $appointment->checked_in_at->format('H:i') }}</span>
        </div>
        @endif
    </div>

    @if($verificationCode)
    <div class="bg-white border border-gray-200 rounded-xl p-8 mb-6" x-data="verificationCard" data-code="{{ $verificationCode->code_value }}" data-name="{{ $applicant->first_name }} {{ $applicant->last_name }}" data-gaf="{{ $application->gaf_id }}" data-date="{{ $appointment->scheduled_date?->format('F j, Y') ?? 'N/A' }}" data-time="{{ $appointment->scheduled_time ?? 'N/A' }}" data-venue="{{ $appointment->venue ?? 'N/A' }}">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
            <div class="flex flex-col items-center">
                <template x-if="qrDataUrl">
                    <img :src="qrDataUrl" alt="Verification QR Code" class="w-48 h-48">
                </template>
                <template x-if="!qrDataUrl">
                    <div class="w-48 h-48 bg-gray-100 rounded-lg flex items-center justify-center">
                        <span class="text-xs text-gray-400">Generating QR...</span>
                    </div>
                </template>
            </div>
            <div class="flex-1 text-center md:text-left">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Verification Code</p>
                <p class="font-heading font-bold text-3xl text-gray-800 tracking-[0.25em] font-mono select-all mb-4">{{ $verificationCode->code_value }}</p>
                <div class="flex flex-wrap gap-2 justify-center md:justify-start mb-4">
                    <button @click="copyCode" class="inline-flex items-center space-x-1.5 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span x-text="copyLabel">Copy Code</span>
                    </button>
                    <button @click="downloadCard" class="inline-flex items-center space-x-1.5 bg-gaf-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Download Card</span>
                    </button>
                </div>
                <p class="text-xs text-gray-400">Present this QR code and verification code at the screening venue.</p>
            </div>
        </div>
    </div>
    @endif
    @else
    <div class="bg-white border border-gray-200 rounded-xl p-8 mb-6 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <h2 class="font-heading font-semibold text-xl text-gray-800 mb-2">No Appointment Scheduled</h2>
        <p class="text-sm text-gray-500">You will be notified once a screening appointment is scheduled for you.</p>
        <p class="text-xs text-gray-400 mt-4">Check back later or monitor your notifications for updates.</p>
    </div>
    @endif
</div>
@endsection
