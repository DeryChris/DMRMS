@extends('layouts.applicant')

@section('title', 'Notifications - Ghana Armed Forces')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-heading font-bold text-2xl text-gray-800">Notifications</h1>
            <p class="text-gray-500 text-sm">Stay informed about your application.</p>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($allNotifications as $n)
        <div class="bg-white border border-gray-200 rounded-xl p-5 flex items-start space-x-4 {{ !$n->read_at ? 'border-l-4 border-l-gaf-green' : '' }}">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 {{ !$n->read_at ? 'bg-gaf-green bg-opacity-10' : 'bg-gray-100' }}">
                <svg class="w-5 h-5 {{ !$n->read_at ? 'text-gaf-green' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                    <p class="text-sm font-medium {{ !$n->read_at ? 'text-gray-900' : 'text-gray-600' }}">{{ $n->subject ?? 'Notification' }}</p>
                    <span class="text-xs text-gray-400 flex-shrink-0 ml-2">{{ $n->created_at?->diffForHumans() ?? '' }}</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">{{ $n->message ?? $n->subject }}</p>
            </div>
            @if(!$n->read_at)
            <div class="w-2 h-2 bg-gaf-green rounded-full flex-shrink-0 mt-2"></div>
            @endif
        </div>
        @empty
        <div class="bg-white border border-gray-200 rounded-xl p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-gray-500 text-sm">No notifications yet.</p>
        </div>
        @endforelse
    </div>

    @if($allNotifications->hasPages())
    <div class="mt-8">
        {{ $allNotifications->links() }}
    </div>
    @endif
</div>
@endsection
