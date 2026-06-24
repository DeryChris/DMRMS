<div x-data="{
    open: false,
    messages: [
        { role: 'bot', text: 'Hello! Welcome to the GAF Recruitment Portal. How can I help you today?' }
    ],
    input: '',
    send() {
        if(!this.input.trim()) return;
        this.messages.push({ role: 'user', text: this.input });
        let q = this.input;
        this.input = '';
        setTimeout(() => {
            let responses = {
                'eligibility': 'To check eligibility, visit the eligibility Checker page or ensure you are a Ghanaian citizen aged 18-35 with at least SSCE/WASSCE.',
                'apply': 'To apply, create an account, complete the 4-step application form, upload documents, and Submit before the deadline.',
                'deadline': 'The current application deadline is July 31, 2026.',
                'documents': 'Required documents: girth Certificate, National ID, WASSCE/SSCE Certificate, Passport Photo, Medical Reporttt, and Police Clearance.',
                'status': 'You can track your application status from your applicant dashboard after logging in.',
            };
            let answer = 'I am an AI assistant. For specific questions, please contact GAF recruitment office or check the FAQ page.';
            for(let [key, val] of Object.entries(responses)) {
                if(q.toLowerCase().includes(key)) { answer = val; break; }
            }
            this.messages.push({ role: 'bot', text: answer });
        }, 800);
    }
}" class="fixed bottom-6 right-6 z-50">
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
        <div class="h-80 overflow-y-auto p-4 space-y-3" x-ref="chatbox">
            <template x-for="(msg, i) in messages" :key="i">
                <div class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                    <div class="max-w-[80%] px-4 py-2 rounded-lg text-sm" :class="msg.role === 'user' ? 'bg-gaf-green text-white rounded-br-none' : 'bg-gray-100 text-gray-800 rounded-bl-none'">
                        <p x-text="msg.text"></p>
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
