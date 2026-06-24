@extends(auth()->user() && auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.applicant')

@section('title', 'All Notifications')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="font-heading font-bold text-2xl text-gray-800">All Notifications</h1>
        <p class="text-gray-500 text-sm">View and manage all your notifications.</p>
    </div>

    <div class="space-y-2">
        @forelse($notifications as $notification)
        <div class="bg-white border border-gray-200 rounded-xl p-5 transition hover:shadow-sm {{ is_null($notification->read_at) ? 'border-l-4 border-l-gaf-green' : '' }}">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold {{ is_null($notification->read_at) ? 'text-gray-900' : 'text-gray-600' }}">{{ $notification->subject }}</p>
                    <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">{{ $notification->message }}</p>
                    <p class="text-[11px] text-gray-400 mt-2">
                        {{ $notification->sent_at ? $notification->sent_at->diffForHumans() : '' }}
                        @if($notification->read_at)
                        <span class="ml-2 text-gaf-green">&#10003; Read {{ $notification->read_at->diffForHumans() }}</span>
                        @endif
                    </p>
                </div>
                @if(is_null($notification->read_at))
                <div class="w-2 h-2 bg-gaf-red rounded-full flex-shrink-0 mt-2"></div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-gray-400 font-medium">No notifications yet</p>
        </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</div>
@endsection