@extends('layouts.applicant')

@section('title', 'Application Status - Ghana Armed Forces')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="font-heading font-bold text-2xl text-gray-800 mb-2">Application Status</h1>
    <p class="text-gray-500 text-sm mb-8">Track your recruitment progress.</p>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl p-8 mb-8">
        <x-applicant-status-timeline :currentStage="$currentStage" :stages="$stages" />
    </div>

    @if($eligible && $verificationCode)
    <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center mb-6">
        <p class="text-sm text-green-700 mb-1">Verification Code</p>
        <p class="font-heading font-bold text-3xl text-green-800 tracking-widest">{{ $verificationCode->code }}</p>
        <p class="text-xs text-green-600 mt-1">Present this code at your screening appointment.</p>
    </div>
    @endif

    @if($eligible)
    <div class="text-center">
        <a href="#" class="inline-flex items-center space-x-2 bg-gaf-green text-white px-6 py-3 rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Download Admission Letter</span>
        </a>
    </div>
    @endif
</div>
@endsection
