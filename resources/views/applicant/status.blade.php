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

    @if($eligible && $verificationCode && $currentStage < 10)
    <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center mb-6">
        <p class="text-sm text-green-700 mb-1">Verification Code</p>
        <p class="font-heading font-bold text-3xl text-green-800 tracking-widest">{{ $verificationCode->code_value }}</p>
        <p class="text-xs text-green-600 mt-1">Present this code at your screening appointment.</p>
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
