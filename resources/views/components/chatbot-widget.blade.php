@php
    $activeCycle = \App\Models\Cycle::where('status', 'active')->orderBy('start_date', 'desc')->first();
    $deadlineDate = $activeCycle?->application_deadline?->format('F j, Y') ?? 'TBD';
    $apiUrl = '/api/v1/chatbot/message';
@endphp
<style>
.scroll-hide::-webkit-scrollbar { display: none; }
</style>
<div x-data="chatbotWidget('{{ $apiUrl }}')" class="fixed bottom-6 right-6 z-50">
    <div x-show="open" x-cloak x-transition class="mb-4 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden">
        <div class="bg-gaf-green text-white px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-gaf-khaki rounded-full flex items-center justify-center"><span class="text-gaf-green font-heading font-bold text-xs">AI</span></div>
                <span class="text-sm font-heading font-semibold">GAF Assistant</span>
            </div>
            <button @click="open = false" class="text-gray-300 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="h-80 overflow-y-auto p-4 space-y-3 scroll-hide" x-ref="chatbox" style="scrollbar-width: none; -ms-overflow-style: none;">
            <template x-for="(msg, i) in messages" :key="i">
                <div class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                    <div class="max-w-[80%] px-4 py-2 rounded-lg text-sm" :class="msg.role === 'user' ? 'bg-gaf-green text-white rounded-br-none' : 'bg-gray-100 text-gray-800 rounded-bl-none'">
                        <template x-if="msg.role === 'user'">
                            <p x-text="msg.text"></p>
                        </template>
                        <template x-if="msg.role === 'bot'">
                            <div x-html="formatText(msg.text)" class="text-sm [&_p]:mb-2 [&_p:last-child]:mb-0 [&_ul]:list-disc [&_ul]:pl-4 [&_ol]:list-decimal [&_ol]:pl-4 [&_li]:mb-1 [&_strong]:font-semibold [&_em]:italic [&_code]:text-xs [&_code]:bg-gray-200 [&_code]:px-1 [&_code]:rounded"></div>
                        </template>
                    </div>
                </div>
            </template>
            <template x-if="loading">
                <div class="flex justify-start">
                    <div class="bg-gray-100 text-gray-800 rounded-lg rounded-bl-none px-4 py-3 text-sm">
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <div class="border-t border-gray-200 p-3 flex space-x-2">
            <input type="text" x-model="input" @keydown.enter="send" placeholder="Type a message..." class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gaf-khaki">
            <button @click="send" class="px-4 py-2 bg-gaf-red text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">Send</button>
        </div>
    </div>
    <button @click="open = !open" class="w-14 h-14 bg-gaf-red text-white rounded-full shadow-lg hover:bg-red-700 transition flex items-center justify-center">
        <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>
