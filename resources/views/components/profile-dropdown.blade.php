@props(['dark' => false])
@php $applicant = auth('applicant')->user(); @endphp

<div x-data="{
    open: false,
}" @click.away="open = false" class="relative">
    <button @click="open = !open" 
        class="relative p-1.5 rounded-lg transition focus:outline-none @if($dark) text-white/70 hover:text-white hover:bg-white/10 @else text-gray-500 hover:text-gray-700 hover:bg-gray-100 @endif">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-heading font-bold text-sm @if($dark) bg-gaf-khaki @else bg-gaf-green @endif">
            {{ substr($applicant->name ?? 'A', 0, 1) }}
        </div>
    </button>

    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" class="absolute right-0 mt-2 w-72 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-gray-100 dark:border-slate-700 z-50 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-700">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gaf-green rounded-full flex items-center justify-center text-white font-heading font-bold text-lg">
                    {{ substr($applicant->name ?? 'A', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-heading font-bold text-gray-900 dark:text-slate-100 truncate">{{ $applicant->name ?? 'Applicant' }}</p>
                    <p class="text-xs text-gray-500 dark:text-slate-400 truncate">{{ $applicant->email ?? '' }}</p>
                </div>
            </div>
        </div>
        <div class="px-5 py-3 space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-slate-400">GAF ID</span>
                <span class="font-medium text-gray-800 dark:text-slate-200">{{ optional($applicant->application)->gaf_id ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-slate-400">Phone</span>
                <span class="font-medium text-gray-800 dark:text-slate-200">{{ $applicant->contact_number ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-slate-400">Status</span>
                @if($applicant->application)
                    <span>{!! status_badge($applicant->application->status) !!}</span>
                @else
                    <span class="text-xs font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-700">Not Started</span>
                @endif
            </div>
        </div>
        <div class="px-5 py-3 border-t border-gray-100 dark:border-slate-700">
            <form method="POST" action="{{ route('applicant.logout') }}">
                @csrf
                <button type="submit" class="w-full text-center bg-gaf-khaki text-gaf-green px-4 py-2 rounded-lg text-xs font-semibold hover:bg-yellow-500 transition">Sign Out</button>
            </form>
        </div>
    </div>
</div>
