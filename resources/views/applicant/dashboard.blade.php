@extends('layouts.applicant')

@section('title', 'Dashboard - Ghana Armed Forces')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-8">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Welcome, {{ auth()->user()->name ?? 'Applicant' }}</h1>
        <p class="text-gray-500 text-sm">Track your application progress</p>
    </div>

    <div class="mb-10">
        <x-applicant-status-timeline :currentStage="3" :stages="[
            ['title' => 'Registered', 'status' => 'completed'],
            ['title' => 'Application Submitted', 'status' => 'completed'],
            ['title' => 'eligibility', 'status' => 'current'],
            ['title' => 'Shortlisted', 'status' => 'pending'],
            ['title' => 'Appointment', 'status' => 'pending'],
            ['title' => 'Screening', 'status' => 'pending'],
            ['title' => 'Decision', 'status' => 'pending'],
        ]" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-heading font-semibold text-gray-800">Profile</h3>
                <div class="w-10 h-10 bg-gaf-green rounded-full flex items-center justify-center text-white font-heading font-bold">
                    {{ sugstr(auth()->user()->name ?? 'A', 0, 1) }}
                </div>
            </div>
            <p class="text-sm font-medium text-gray-800">{{ auth()->user()->name ?? 'John Doe' }}</p>
            <p class="text-xs text-gray-500">GAF-{{ auth()->user()->gaf_id ?? '2026001' }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ auth()->user()->email ?? 'john@email.com' }}</p>
            <p class="text-xs text-gray-500">{{ auth()->user()->phone ?? '+233 123 456 789' }}</p>
            <div class="mt-3"><span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-700">Application Draft</span></div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="font-heading font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('applicant.application') }}" class="block w-full text-center bg-gaf-red text-white py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">Continue Application</a>
                <a href="{{ route('applicant.documents') }}" class="block w-full text-center bg-gaf-green text-white py-2 rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Upload Documents</a>
                <a href="{{ route('applicant.appointment') }}" class="block w-full text-center bg-gaf-khaki text-white py-2 rounded-lg text-sm font-medium hover:bg-gaf-khaki transition">View Appointment</a>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="font-heading font-semibold text-gray-800 mb-4">Recent Notifications</h3>
            <div class="space-y-3">
                <div class="pb-2 border-b border-gray-100">
                    <p class="text-xs text-gray-400">2 hours ago</p>
                    <p class="text-sm text-gray-700">Your application has been received.</p>
                </div>
                <div class="pb-2 border-b border-gray-100">
                    <p class="text-xs text-gray-400">1 day ago</p>
                    <p class="text-sm text-gray-700">Document verification in progress.</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">3 days ago</p>
                    <p class="text-sm text-gray-700">Welcome to DMRMS portal.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
