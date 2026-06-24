@extends('layouts.applicant')

@section('title', 'Dashboard - Ghana Armed Forces')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-8">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Welcome, {{ $applicant->name ?? 'Applicant' }}</h1>
        <p class="text-gray-500 text-sm">Track your application progress</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">{{ session('success') }}</div>
    @endif

    <div class="mb-10">
        <x-applicant-status-timeline :currentStage="$currentStage" :stages="$stages" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-heading font-semibold text-gray-800">Profile</h3>
                <div class="w-10 h-10 bg-gaf-green rounded-full flex items-center justify-center text-white font-heading font-bold">
                    {{ substr($applicant->name ?? 'A', 0, 1) }}
                </div>
            </div>
            <p class="text-sm font-medium text-gray-800">{{ $applicant->name ?? 'N/A' }}</p>
            <p class="text-xs text-gray-500">GAF-{{ optional($application)->gaf_id ?? 'N/A' }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $applicant->email ?? 'N/A' }}</p>
            <p class="text-xs text-gray-500">{{ $applicant->contact_number ?? 'N/A' }}</p>
            <div class="mt-3">
                @php
                    $statusLabels = ['draft' => 'Application Draft', 'submitted' => 'Submitted', 'eligibility_passed' => 'Eligibility Passed', 'shortlisted' => 'Shortlisted', 'appointment_scheduled' => 'Appointment Scheduled', 'rejected' => 'Rejected', 'selected' => 'Selected'];
                    $statusColors = ['draft' => 'bg-amber-100 text-amber-700', 'submitted' => 'bg-blue-100 text-blue-700', 'eligibility_passed' => 'bg-green-100 text-green-700', 'shortlisted' => 'bg-purple-100 text-purple-700', 'appointment_scheduled' => 'bg-indigo-100 text-indigo-700', 'rejected' => 'bg-red-100 text-red-700', 'selected' => 'bg-green-100 text-green-700'];
                @endphp
                @if($application)
                    <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $statusColors[$application->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $statusLabels[$application->status] ?? ucfirst($application->status) }}</span>
                @else
                    <span class="text-xs font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-700">Not Started</span>
                @endif
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="font-heading font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('applicant.application') }}" class="block w-full text-center bg-gaf-red text-white py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">{{ $application ? 'Continue Application' : 'Start Application' }}</a>
                <a href="{{ route('applicant.documents') }}" class="block w-full text-center bg-gaf-green text-white py-2 rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Upload Documents</a>
                <a href="{{ route('applicant.appointment') }}" class="block w-full text-center bg-gaf-khaki text-white py-2 rounded-lg text-sm font-medium hover:bg-yellow-600 transition">View Appointment</a>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="font-heading font-semibold text-gray-800 mb-4">Recent Notifications</h3>
            <div class="space-y-3">
                @forelse($notifications as $n)
                <div class="pb-2 border-b border-gray-100 last:border-b-0">
                    <p class="text-xs text-gray-400">{{ $n->created_at?->diffForHumans() ?? '' }}</p>
                    <p class="text-sm text-gray-700">{{ $n->message ?? $n->subject }}</p>
                </div>
                @empty
                <p class="text-sm text-gray-500">No notifications yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
