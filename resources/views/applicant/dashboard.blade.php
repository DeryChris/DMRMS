@extends('layouts.applicant')

@section('title', 'Dashboard - Ghana Armed Forces')

@section('content')
<div class="max-w-6xl mx-auto px-4">
    <div class="mb-8 gradient-border pb-4">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Welcome, {{ $applicant->name ?? 'Applicant' }}</h1>
        <p class="text-gray-500 text-sm">Track your application progress</p>
    </div>

    <div class="mb-10">
        <x-applicant-status-timeline :currentStage="$currentStage" :stages="$stages" />
    </div>

    @if($application && in_array($application->status, ['selected', 'recruited', 'rejected', 'disqualified', 'reserve']))
    <div class="mb-8">
        @if(in_array($application->status, ['selected', 'recruited']))
        <div class="rounded-xl shadow-lg overflow-hidden" style="background:linear-gradient(135deg, #14532d 0%, #166534 50%, #15803d 100%);">
            <div class="relative px-8 py-10 text-center" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%23D4AF37%22 fill-opacity=%220.08%22%3E%3Cpath d=%22M0 0 L200 0 L200 200 L0 200 Z M30 30 Q60 10 100 30 Q140 50 170 30%22/%3E%3C/g%3E%3C/svg%3E');background-repeat:repeat;">
                <div class="w-20 h-20 mx-auto bg-white/15 rounded-full flex items-center justify-center mb-4 ring-4 ring-white/20">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="font-heading font-bold text-3xl text-white mb-2">Congratulations!</h2>
                <p class="text-white/80 text-lg mb-6">You have been selected for recruitment into the Ghana Armed Forces.</p>
                <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-full px-6 py-2 text-white/70 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Decision Date: {{ $finalDecision?->decision_date?->format('F j, Y') ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="bg-white px-8 py-6 space-y-4">
                @if($finalDecision?->decision_reason)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm text-green-800">{{ $finalDecision->decision_reason }}</p>
                </div>
                @endif
                <div class="border-t border-gray-100 pt-4">
                    <h4 class="font-heading font-semibold text-sm text-gray-800 mb-3">Your Reporting Details</h4>
                    <div class="bg-gaf-green/5 border border-gaf-green/20 rounded-xl p-5 mb-4">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Reporting Code</p>
                                <p class="font-heading font-bold text-2xl text-gaf-dark-green mt-1 tracking-wider">{{ $finalDecision?->reporting_code ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">Present this code at the barracks for verification</p>
                            </div>
                            @if($finalDecision?->barrack)
                            <div class="text-right">
                                <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Reporting To</p>
                                <p class="font-heading font-bold text-lg text-gaf-dark-green mt-1">{{ $finalDecision->barrack->name }}</p>
                                <p class="text-xs text-gray-500">{{ $finalDecision->barrack->location ?? $finalDecision->barrack->region }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start space-x-2">
                            <svg class="w-4 h-4 text-gaf-green mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span>Report to <strong>{{ $finalDecision?->barrack?->name ?? 'your designated barracks' }}</strong> on the specified date with your reporting code.</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg class="w-4 h-4 text-gaf-green mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span>Bring all required medical records, academic certificates, and identification documents.</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg class="w-4 h-4 text-gaf-green mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span>Maintain physical fitness — training begins upon arrival at the barracks.</span>
                        </li>
                    </ul>
                </div>
                <div class="flex justify-center">
                    <a href="{{ route('applicant.offer-letter') }}" class="inline-flex items-center space-x-2 bg-gaf-khaki text-white px-8 py-3 rounded-lg text-sm font-semibold hover:bg-yellow-600 transition shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Download Offer Letter</span>
                    </a>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800">
                    <p class="font-medium">Important Notice</p>
                    <p class="text-xs mt-1">Failure to report on the assigned date may result in forfeiture of your selection. Contact the recruitment board immediately if you have any conflicts.</p>
                </div>
            </div>
        </div>
        @elseif($application->status === 'reserve')
        <div class="rounded-xl shadow-lg overflow-hidden border border-blue-200">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-8 py-8 text-center">
                <div class="w-20 h-20 mx-auto bg-white/15 rounded-full flex items-center justify-center mb-4 ring-4 ring-white/20">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <h2 class="font-heading font-bold text-3xl text-white mb-2">Reserve List</h2>
                <p class="text-white/80 text-lg">You have been placed on the reserve list.</p>
            </div>
            <div class="bg-white px-8 py-6 space-y-3">
                @if($finalDecision?->decision_reason)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">{{ $finalDecision->decision_reason }}</p>
                </div>
                @endif
                <p class="text-sm text-gray-600">You will be considered if additional vacancies become available. We will contact you via email and SMS should your status change.</p>
            </div>
        </div>
        @else
        <div class="rounded-xl shadow-lg overflow-hidden border border-red-200">
            <div class="bg-gradient-to-r from-red-600 to-red-800 px-8 py-8 text-center">
                <div class="w-20 h-20 mx-auto bg-white/15 rounded-full flex items-center justify-center mb-4 ring-4 ring-white/20">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/></svg>
                </div>
                <h2 class="font-heading font-bold text-3xl text-white mb-2">Application Outcome</h2>
                <p class="text-white/80 text-lg">We regret to inform you that your application was not successful.</p>
            </div>
            <div class="bg-white px-8 py-6 space-y-3">
                @if($finalDecision?->decision_reason)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-red-800">{{ $finalDecision->decision_reason }}</p>
                </div>
                @endif
                <p class="text-sm text-gray-600">Thank you for your interest in the Ghana Armed Forces. You may reapply in future recruitment cycles.</p>
            </div>
        </div>
        @endif
    </div>
    @endif

    <div class="flex flex-wrap items-center justify-center gap-3">
        @if(!$application || in_array($application->status, ['draft', 'registered']))
        <a href="{{ route('applicant.application') }}" class="bg-gaf-red text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-red-700 transition">{{ $application ? 'Continue' : 'Start' }} Application</a>
        @endif
        @if($application && $application->status === 'appointment_scheduled')
        <a href="{{ route('applicant.appointment') }}" class="bg-gaf-khaki text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-yellow-600 transition">Appointment</a>
        @endif
    </div>
</div>
@endsection
