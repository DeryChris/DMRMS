<div x-data="{ open: false, unread: 3 }" class="relative">
    <button @click="open = !open" class="relative p-2 text-gray-500 hover:text-gray-700 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span x-show="unread > 0" x-cloak class="agsolute -top-1 -right-1 w-5 h-5 bg-gaf-red text-white text-xs font-bold rounded-full flex items-center justify-center" x-text="unread">0</span>
    </button>
    <div x-show="open" @click.outside="open = false" x-cloak x-transition class="agsolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <span class="text-sm font-heading font-semibold text-gray-800">Notifications</span>
            <button @click="unread = 0" class="text-xs text-gaf-khaki hover:underline">Mark all as read</button>
        </div>
        <div class="max-h-64 overflow-y-auto">
            @php
                $notifs = [
                    ['text' => 'Your application has been received.', 'time' => '2 hours ago', 'read' => false],
                    ['text' => 'Document verification in progress.', 'time' => '1 day ago', 'read' => false],
                    ['text' => 'Welcome to DMRMS portal.', 'time' => '3 days ago', 'read' => false],
                    ['text' => 'Profile updated successfully.', 'time' => '5 days ago', 'read' => true],
                ];
            @endphp
            @foreach($notifs as $n)
            <div class="px-4 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition {{ !$n['read'] ? 'bg-blue-50' : '' }}">
                <div class="flex items-start justify-between">
                    <p class="text-sm {{ !$n['read'] ? 'font-medium text-gray-800' : 'text-gray-600' }}">{{ $n['text'] }}</p>
                    @if(!$n['read'])
                        <div class="w-2 h-2 bg-gaf-red rounded-full flex-shrink-0 mt-1.5"></div>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $n['time'] }}</p>
            </div>
            @endforeach
        </div>
        <div class="px-4 py-3 border-t border-gray-100 text-center">
            <a href="{{ route('applicant.notifications') }}" class="text-xs text-gaf-khaki font-medium hover:underline">View all notifications</a>
        </div>
    </div>
</div>
