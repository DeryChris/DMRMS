<div
    x-data="notificationBell"
    @click.away="open = false"
    class="relative"
>
    <button @click="toggle()" class="relative p-2 text-gray-500 hover:text-gray-700 transition rounded-lg hover:bg-gray-100 focus:outline-none">
        <svg class="w-6 h-6" :class="{'animate-breathing': unread > 0}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span x-show="unread > 0" x-cloak class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-gaf-red text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-sm" x-text="unread"></span>
    </button>

    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-2xl border border-gray-100 z-50 max-h-[80vh] flex flex-col">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <div>
                <span class="text-sm font-heading font-bold text-gray-900">Notifications</span>
                <span x-show="unread > 0" class="ml-2 text-xs text-gray-400" x-text="`(${unread} unread)`"></span>
            </div>
            <button x-show="unread > 0" @click="markAllRead()" class="text-[11px] text-gaf-khaki hover:underline font-medium">Mark all read</button>
        </div>

        <div class="overflow-y-auto flex-1 min-h-0">
            <template x-if="loading">
                <div class="flex items-center justify-center py-12">
                    <svg class="w-6 h-6 text-gray-300 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </div>
            </template>
            <template x-if="!loading && items.length === 0">
                <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <p class="text-sm font-medium">No notifications yet</p>
                </div>
            </template>
            <template x-for="(item, i) in items" :key="item.id">
                <div @click="toggleExpand(i, item)" class="px-5 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/70 transition cursor-pointer" :class="{'bg-gaf-green/[0.03]': !item.read_at}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium" :class="item.read_at ? 'text-gray-600' : 'text-gray-900'" x-text="item.subject || '(No subject)'"></p>
                            <div x-show="expanded === i" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="mt-1.5 text-xs text-gray-500 leading-relaxed" x-text="item.message"></div>
                            <p class="text-[11px] text-gray-400 mt-1.5">
                                <span x-text="timeAgo(item.sent_at)"></span>
                                <span x-show="item.read_at" class="ml-2 text-gaf-green">&#10003; Read</span>
                            </p>
                        </div>
                        <div class="flex items-center space-x-2 ml-3 flex-shrink-0">
                            <div x-show="!item.read_at" class="w-2 h-2 rounded-full bg-gaf-red flex-shrink-0"></div>
                            <svg class="w-3.5 h-3.5 text-gray-300 transition-transform duration-200" :class="{'rotate-180': expanded === i, 'rotate-0': expanded !== i}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 text-center flex-shrink-0">
            <button @click="showAll = true; open = false; initAll()" class="text-xs text-gaf-khaki font-semibold hover:underline">See all notifications</button>
        </div>
    </div>

    <div x-show="showAll" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 z-[60] flex items-start justify-center pt-16">
        <div class="absolute inset-0 bg-black/30" @click="showAll = false"></div>
        <div @click.away="showAll = false" class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-xl max-h-[80vh] flex flex-col overflow-hidden mt-8">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
                <div>
                    <h3 class="text-base font-heading font-bold text-gray-900">All Notifications</h3>
                    <p class="text-xs text-gray-400" x-text="`${total} total`"></p>
                </div>
                <button @click="showAll = false" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="overflow-y-auto flex-1 min-h-0 px-2 py-2">
                <template x-if="allLoading">
                    <div class="flex items-center justify-center py-16">
                        <svg class="w-8 h-8 text-gray-200 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </div>
                </template>
                <template x-if="!allLoading && allItems.length === 0">
                    <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                        <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <p class="text-sm font-medium">No notifications</p>
                    </div>
                </template>
                <template x-for="(item, i) in allItems" :key="'all-'+item.id">
                    <div @click="expandAll(i, item)" class="px-4 py-3.5 rounded-xl border border-transparent transition cursor-pointer" :class="{'bg-gaf-green/[0.03] border-gaf-green/10': !item.read_at, 'hover:bg-gray-50': true}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium" :class="item.read_at ? 'text-gray-600' : 'text-gray-900'" x-text="item.subject || '(No subject)'"></p>
                                <div x-show="allExpanded === i" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="mt-2 text-xs text-gray-500 leading-relaxed bg-gray-50 p-3 rounded-lg" x-text="item.message"></div>
                                <p class="text-[11px] text-gray-400 mt-1.5">
                                    <span x-text="timeAgo(item.sent_at)"></span>
                                    <span x-show="item.read_at" class="ml-2 text-gaf-green">&#10003; Read</span>
                                </p>
                            </div>
                            <div class="flex items-center space-x-2 ml-3 flex-shrink-0">
                                <div x-show="!item.read_at" class="w-2 h-2 rounded-full bg-gaf-red flex-shrink-0"></div>
                                <svg class="w-3.5 h-3.5 text-gray-300 transition-transform duration-200" :class="{'rotate-180': allExpanded === i}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <div x-show="allHasMore" class="px-6 py-3 border-t border-gray-100 text-center flex-shrink-0">
                <button @click="loadMore()" class="text-xs text-gaf-khaki font-semibold hover:underline" x-text="allLoading ? 'Loading...' : 'Load more'"></button>
            </div>
        </div>
    </div>

    <style>
        @keyframes breathing {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.12); }
        }
        .animate-breathing {
            animation: breathing 2s ease-in-out infinite;
            transform-origin: center;
        }
    </style>
</div>