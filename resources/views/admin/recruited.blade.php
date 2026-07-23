@extends('layouts.admin')

@section('title', 'Recruited Applicants - Ghana Armed Forces')

@section('content')
<div x-data="{
    sendBackModal: false, sendBackAppId: null, sendBackName: '', sendBackStatus: '', sendBackTarget: '', sendBackReason: '',
    sendBackStatuses: [],
    openSendBack(appId, name, status, targets) {
        this.sendBackAppId = appId; this.sendBackName = name; this.sendBackStatus = status;
        this.sendBackTarget = ''; this.sendBackReason = ''; this.sendBackStatuses = targets;
        this.sendBackModal = true;
    }
}" x-init="{{ $errors->any() && old('target_status') ? '$nextTick(() => { sendBackModal = true; sendBackTarget = \''.old('target_status', '').'\'; sendBackReason = \''.str_replace("'", "\\'", old('reason', '')).'\'; })' : '' }}" class="space-y-6">
    <div class="flex items-center justify-between gradient-border pb-4">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Recruited Applicants</h1>
        <form method="GET" action="{{ route('admin.recruited') }}" class="flex items-center space-x-2">
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                <option value="selected" {{ request('status') === 'selected' ? 'selected' : '' }}>Selected</option>
                <option value="recruited" {{ request('status') === 'recruited' ? 'selected' : '' }}>Recruited</option>
            </select>
            <button type="submit" class="px-3 py-2 bg-gaf-green text-white rounded-lg text-sm hover:bg-gaf-dark-green transition">Filter</button>
        </form>
    </div>

    <div class="glass-strong rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="table-header-gradient">
                    <th class="px-6 py-4 text-left text-white/90">Applicant</th>
                    <th class="px-6 py-4 text-left text-white/90">GAF ID</th>
                    <th class="px-6 py-4 text-left text-white/90">Status</th>
                    <th class="px-6 py-4 text-left text-white/90">Cycle</th>
                    <th class="px-6 py-4 text-left text-white/90">Decision Date</th>
                    <th class="px-6 py-4 text-center text-white/90">Returned</th>
                    <th class="px-6 py-4 text-right text-white/90">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($applications as $app)
                @php
                    $targets = \App\Http\Controllers\Web\AdminWebController::getSendBackTargetsStatic($app->status);
                @endphp
                <tr class="hover:bg-gray-50" style="border-left: 3px solid transparent;">
                    <td class="px-6 py-4 text-left font-medium">{{ $app->applicant->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-left text-gray-600">{{ $app->gaf_id ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-left">{!! status_badge($app->status) !!}</td>
                    <td class="px-6 py-4 text-left text-gray-500">{{ $app->cycle?->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-left text-gray-500">{{ $app->finalDecision?->decision_date?->format('M j, Y') ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-center">
                        @if(($app->returned_count ?? 0) > 0)
                        <span class="inline-flex items-center space-x-1 text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-full" title="Last: {{ $app->last_returned_from }} → {{ $app->last_returned_to }}: {{ $app->last_return_reason }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            <span>{{ $app->returned_count }}x</span>
                        </span>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-1">
                        @if(!empty($targets))
                            <a href="{{ route('admin.applications.detail', $app->id) }}" class="relative group p-1.5 rounded-lg hover:bg-gaf-green/10 text-gaf-green transition-colors" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">View</span>
                            </a>
                            @if($app->status === 'selected')
                            <form method="POST" action="{{ route('admin.recruit', $app->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="relative group p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600 transition-colors" title="Mark Recruited">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Recruit</span>
                                </button>
                            </form>
                            @endif
                        <button @click="openSendBack({{ $app->id }}, '{{ $app->applicant->name }}', '{{ $app->status }}', {{ json_encode($targets) }})" class="relative group p-1.5 rounded-lg hover:bg-amber-50 text-amber-600 transition-colors" title="Send Back">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Send Back</span>
                        </button>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm font-medium">No recruited or selected applicants found</p>
                        <p class="text-xs text-gray-400 mt-1">Applicants who pass the selection stage will appear here.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing {{ $applications->firstItem() ?? 0 }} to {{ $applications->lastItem() ?? 0 }} of {{ $applications->total() }} entries</p>
        {{ $applications->links() }}
    </div>

    <div id="recruited-old-data" data-old="{{ json_encode(old()) }}" class="hidden"></div>

    {{-- Send Back Modal --}}
    <div x-show="sendBackModal" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="glass-strong rounded-xl shadow-2xl p-8 max-w-md w-full mx-4" style="background:rgba(255,255,255,0.95);backdrop-filter:blur(16px);">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-2">Send Back for Re-review</h3>
            <p class="text-sm text-gray-500 mb-4">
                <span x-text="sendBackName"></span> &mdash;
                <span class="font-semibold" x-text="sendBackStatus.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
            </p>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Send Back to Stage <span class="text-red-500">*</span></label>
                <select x-model="sendBackTarget" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    <option value="">Select stage...</option>
                    <template x-for="s in sendBackStatuses" :key="s">
                        <option :value="s" x-text="s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"></option>
                    </template>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label>
                <textarea x-model="sendBackReason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gaf-khaki" placeholder="Explain why this applicant is being sent back..."></textarea>
            </div>
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 mb-4">
                <p class="font-medium">⚠️ This action will:</p>
                <ul class="list-disc list-inside mt-1 space-y-0.5">
                    <li>Move the applicant back to the selected stage</li>
                    <li>Notify the applicant and relevant officers</li>
                    <li>Reset any decision/appointment records if going past those stages</li>
                </ul>
            </div>
            <div class="flex justify-end space-x-3">
                <button @click="sendBackModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                <form method="POST" :action="'{{ route('admin.applications.send-back', '__ID__') }}'.replace('__ID__', sendBackAppId)" x-ref="sendBackForm">
                    @csrf
                    <input type="hidden" name="target_status" x-model="sendBackTarget">
                    <input type="hidden" name="reason" x-model="sendBackReason">
                    <button @click="if(!sendBackTarget || !sendBackReason.trim()) { alert('Please select a target stage and provide a reason.'); return; } $el.closest('form').submit()" type="button" class="px-6 py-2 bg-amber-600 text-white rounded-lg text-sm font-semibold hover:bg-amber-700">Send Back</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection