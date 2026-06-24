@extends('layouts.applicant')

@section('title', 'Notifications - DMRMS')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-heading font-bold text-2xl text-gray-800">Notifications</h1>
            <p class="text-gray-500 text-sm">Stay informed about your application.</p>
        </div>
        <button class="text-sm text-gaf-khaki font-medium hover:underline">Mark all as read</button>
    </div>

    <div class="space-y-3">
        @php
            $notifications = [
                ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'subject' => 'Application Received', 'preview' => 'Your application has been successfully received and is under review.', 'time' => '2 hours ago', 'read' => false],
                ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'subject' => 'Document Update', 'preview' => 'Your National ID document has been verified successfully.', 'time' => '1 day ago', 'read' => false],
                ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'subject' => 'Status Update', 'preview' => 'Your application has moved to the eligibility verification stage.', 'time' => '3 days ago', 'read' => true],
                ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'subject' => 'Appointment Scheduled', 'preview' => 'Your screening appointment has been scheduled for July 15, 2026.', 'time' => '5 days ago', 'read' => true],
                ['icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'subject' => 'Welcome', 'preview' => 'Welcome to the DMRMS portal. Please complete your application.', 'time' => '1 week ago', 'read' => true],
            ];
        @endphp
        @foreach($notifications as $n)
        <div class="bg-white border border-gray-200 rounded-xl p-5 flex items-start space-x-4 {{ !$n['read'] ? 'border-l-4 border-l-gaf-green' : '' }}">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 {{ !$n['read'] ? 'bg-gaf-green bg-opacity-10' : 'bg-gray-100' }}">
                <svg class="w-5 h-5 {{ !$n['read'] ? 'text-gaf-green' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $n['icon'] }}"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                    <p class="text-sm font-medium {{ !$n['read'] ? 'text-gray-900' : 'text-gray-600' }}">{{ $n['subject'] }}</p>
                    <span class="text-xs text-gray-400 flex-shrink-0 ml-2">{{ $n['time'] }}</span>
                </div>
                <p class="text-sm text-gray-500 mt-1 truncate">{{ $n['preview'] }}</p>
            </div>
            @if(!$n['read'])
            <div class="w-2 h-2 bg-gaf-green rounded-full flex-shrink-0 mt-2"></div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="mt-8 flex justify-center space-x-2">
        <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">&laquo; Prev</button>
        <button class="px-4 py-2 bg-gaf-green text-white rounded-lg text-sm">1</button>
        <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">2</button>
        <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">3</button>
        <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Next &raquo;</button>
    </div>
</div>
@endsection
