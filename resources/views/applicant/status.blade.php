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

    @if($verificationCode && ($application->status ?? '') === 'appointment_scheduled')
    <div class="bg-white border border-gray-200 rounded-xl p-8 mb-6">
        <div class="text-center">
            <div class="w-16 h-16 bg-gaf-green/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h2 class="font-heading font-bold text-xl text-gray-800 mb-2">Appointment Scheduled</h2>
            <p class="text-sm text-gray-500 mb-6">Your appointment has been scheduled. View full details including your verification code and venue on your dashboard.</p>
            <a href="{{ route('applicant.dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Go to Dashboard
            </a>
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
