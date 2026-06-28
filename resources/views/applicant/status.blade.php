@extends('layouts.applicant')

@section('title', 'Application Status - Ghana Armed Forces')

@section('content')
<div class="max-w-4xl mx-auto px-4">
    <div class="gradient-border pb-4 mb-6">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Application Status</h1>
        <p class="text-gray-500 text-sm mt-1">Track your recruitment progress.</p>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-8 mb-8">
        <x-applicant-status-timeline :currentStage="$currentStage" :stages="$stages" />
    </div>

    @if($verificationCode && in_array($application->status ?? '', ['appointment_scheduled', 'screening_completed']))
    <div class="bg-white border border-gray-200 rounded-xl p-8 mb-6" x-data="verificationCard" data-code="{{ $verificationCode->code_value }}" data-name="{{ $applicant->first_name }} {{ $applicant->last_name }}" data-gaf="{{ $application->gaf_id }}" data-date="{{ $application->appointment?->scheduled_date?->format('F j, Y') ?? 'N/A' }}" data-time="{{ $application->appointment?->scheduled_time ?? 'N/A' }}" data-venue="{{ $application->appointment?->venue ?? 'N/A' }}">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
            <div class="flex flex-col items-center">
                <template x-if="qrDataUrl">
                    <img :src="qrDataUrl" alt="QR Code" class="w-40 h-40">
                </template>
                <template x-if="!qrDataUrl">
                    <div class="w-40 h-40 bg-gray-100 rounded-lg flex items-center justify-center">
                        <span class="text-xs text-gray-400">Generating QR...</span>
                    </div>
                </template>
            </div>
            <div class="flex-1 text-center md:text-left">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Verification Code</p>
                <p class="font-heading font-bold text-3xl text-gray-800 tracking-[0.25em] font-mono select-all mb-3" id="verification-code">{{ $verificationCode->code_value }}</p>
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

    @if(in_array($application->status ?? '', ['selected', 'recruited']))
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl p-8 text-center mb-6 shadow-sm">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h2 class="font-heading font-bold text-2xl text-green-800 mb-2">Congratulations!</h2>
        <p class="text-green-700 text-sm mb-1">Dear <strong>{{ $applicant->first_name }} {{ $applicant->last_name }}</strong>,</p>
        <p class="text-green-700 text-sm mb-4">We are pleased to inform you that you have been selected to join the Ghana Armed Forces.</p>
        @if($barracks->count())
        <div class="bg-white rounded-lg border border-green-200 p-4 inline-block text-left">
            <p class="text-sm font-semibold text-gray-700 mb-2">Reporting Instructions</p>
            <p class="text-sm text-gray-600">Report to:</p>
            <ul class="mt-1 space-y-1">
                @foreach($barracks as $barrack)
                <li class="text-sm font-medium text-green-800">
                    {{ $barrack->name }}@if($barrack->location), {{ $barrack->location }}@endif
                </li>
                @endforeach
            </ul>
        </div>
        @endif
        <p class="text-xs text-green-600 mt-4">Further instructions will be communicated via email and notifications.</p>
    </div>
    @endif

    @if(in_array($application->status ?? '', ['rejected', 'disqualified']))
    <div class="bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-300 rounded-xl p-8 text-center mb-6 shadow-sm">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h2 class="font-heading font-bold text-2xl text-red-800 mb-2">Application Unsuccessful</h2>
        <p class="text-red-700 text-sm mb-1">Dear <strong>{{ $applicant->first_name }} {{ $applicant->last_name }}</strong>,</p>
        <p class="text-red-700 text-sm">We regret to inform you that your application was not successful at this time.</p>
        <p class="text-xs text-red-500 mt-4">We encourage you to apply again in future recruitment cycles. Thank you for your interest in serving the Ghana Armed Forces.</p>
    </div>
    @endif
</div>
@endsection
