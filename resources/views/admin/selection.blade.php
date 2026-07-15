@extends('layouts.admin')

@section('title', 'Selection - Ghana Armed Forces')

@section('content')
<div x-data="{
    selected: [], selectAll: false,
    showConfirmModal: false, confirmAppId: null, confirmDecision: '', confirmRemarks: '', confirmName: '',
    evalOpen: null, evalScores: { leadership: 5, communication: 5, technical: 5, discipline: 5, overall: 5 },
    eligibleCount: {{ $eligibleApplicants->count() }},
    screenedCount: {{ $screenedApplicants->count() }},
    vacancyStats: {{ json_encode($vacancyStats->map(fn($s) => [
        'cycle_id' => $s['cycle']->id,
        'cycle_name' => $s['cycle']->name,
        'shortlisted_count' => $s['shortlisted_count'],
        'total_vacancies' => $s['total_vacancies'],
        'remaining' => $s['remaining'],
        'pct' => $s['pct'],
    ])) }},
    pollTimer: null,

    init() {
        this.pollTimer = setInterval(() => this.refreshStats(), 30000);
    },
    destroy() {
        if (this.pollTimer) clearInterval(this.pollTimer);
    },

    async refreshStats() {
        try {
            const res = await fetch('{{ route('admin.selection.stats') }}' + window.location.search);
            if (!res.ok) return;
            const data = await res.json();
            this.eligibleCount = data.eligibleCount;
            this.screenedCount = data.screenedCount;
            this.vacancyStats = data.vacancyStats;
        } catch (e) {}
    },

    openConfirm(appId, decision, name) {
        this.confirmAppId = appId; this.confirmDecision = decision; this.confirmName = name;
        this.confirmRemarks = ''; this.showConfirmModal = true;
    },
    submitDecision(formEl) {
        if (this.confirmDecision === 'reserve' || this.confirmDecision === 'rejected') {
            if (!this.confirmRemarks.trim()) { alert('Please provide remarks for this decision.'); return; }
        }
        let input = document.createElement('input'); input.type = 'hidden'; input.name = 'remarks'; input.value = this.confirmRemarks;
        if (this.evalOpen === this.confirmAppId) {
            let evalInput = document.createElement('input'); evalInput.type = 'hidden'; evalInput.name = 'evaluation';
            evalInput.value = JSON.stringify(this.evalScores); formEl.appendChild(evalInput);
        }
        formEl.appendChild(input); formEl.submit();
    }
}" class="space-y-6">
    <div class="flex items-center justify-between gradient-border pb-4">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Selection Committee Review</h1>
        <form method="GET" action="{{ route('admin.selection') }}" class="flex items-center space-x-2">
            <select name="cycle_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Cycles</option>
                @foreach($cycles as $c)
                    <option value="{{ $c->id }}" {{ request('cycle_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-2 bg-gaf-green text-white rounded-lg text-sm hover:bg-gaf-dark-green transition">Filter</button>
        </form>
    </div>

    {{-- Live Vacancy Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" x-ref="vacancyCards">
        <template x-for="s in vacancyStats" :key="s.cycle_id">
            <div class="glass-strong rounded-xl shadow-sm p-4 gradient-border-left">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold text-sm text-gray-800" x-text="s.cycle_name"></h4>
                    <span class="text-xs font-medium" :class="s.pct >= 100 ? 'text-red-600' : (s.pct >= 80 ? 'text-amber-600' : 'text-green-600')" x-text="s.shortlisted_count + '/' + s.total_vacancies"></span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full" :class="s.pct >= 100 ? 'bg-red-500' : (s.pct >= 80 ? 'bg-amber-500' : 'bg-green-500')" :style="'width: ' + Math.min(s.pct, 100) + '%'"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1" x-text="s.remaining + ' vacancies remaining'"></p>
            </div>
        </template>
    </div>

    {{-- Table 1: Batch Shortlist (Eligible Applicants) --}}
    <div class="glass-strong rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-heading font-semibold text-base text-gray-800">
                Batch Shortlist
                <span class="text-sm font-normal text-gray-500" x-text="'(' + eligibleCount + ' eligible)'"></span>
            </h3>
            <form method="POST" action="{{ route('admin.selection.shortlist') }}" class="flex items-center space-x-3">
                @csrf
                <span class="text-sm text-gray-500" x-text="selected.length + ' selected'"></span>
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="application_ids[]" :value="id">
                </template>
                <button type="submit" class="px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition"
                        :disabled="selected.length === 0"
                        :class="selected.length === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                    Shortlist Selected
                </button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="table-header-gradient">
                    <th class="px-6 py-4 text-left text-white">
                        <input type="checkbox" @click="selectAll = !selectAll; selected = selectAll ? {{ $eligibleApplicants->pluck('id') }} : []" class="rounded border-gray-300">
                    </th>
                    <th class="px-6 py-4 text-left text-white/90">Applicant</th>
                    <th class="px-6 py-4 text-left text-white/90">GAF ID</th>
                    <th class="px-6 py-4 text-left text-white/90">Region</th>
                    <th class="px-6 py-4 text-left text-white/90">Cycle</th>
                    <th class="px-6 py-4 text-left text-white/90">Eligibility</th>
                    <th class="px-6 py-4 text-right text-white/90">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($eligibleApplicants as $app)
                <tr class="hover:bg-gray-50" style="border-left: 3px solid transparent;">
                    <td class="px-6 py-4">
                        <input type="checkbox" value="{{ $app->id }}" x-model="selected" class="rounded border-gray-300">
                    </td>
                    <td class="px-6 py-4 font-medium">{{ $app->applicant->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $app->gaf_id ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $app->applicant->region ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $app->cycle?->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{!! status_badge($app->eligibilityResult?->overall_status ?? 'pending', 'screening') !!}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-1">
                        <form method="POST" action="{{ route('admin.selection.shortlist') }}" class="inline">
                            @csrf
                            <input type="hidden" name="application_ids[]" value="{{ $app->id }}">
                            <button type="submit" class="relative group p-1.5 rounded-lg hover:bg-gaf-green/10 text-gaf-green transition-colors" title="Shortlist">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Shortlist</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.selection.dismiss') }}" x-ref="dismissForm{{ $app->id }}" class="inline">
                            @csrf
                            <input type="hidden" name="application_id" value="{{ $app->id }}">
                            <button type="button" @click="if(confirm('Dismiss this applicant from the shortlisting pool?')) $refs.dismissForm{{ $app->id }}.submit()" class="relative group p-1.5 rounded-lg hover:bg-red-50 text-red-600 transition-colors" title="Dismiss">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Dismiss</span>
                            </button>
                        </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-sm font-medium">No eligible applicants ready for shortlisting</p>
                        <p class="text-xs text-gray-400 mt-1">Applicants who pass eligibility checks will appear here.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Table 2: Final Decisions (Screened Applicants) --}}
    <div class="glass-strong rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-heading font-semibold text-base text-gray-800">
                Final Decisions
                <span class="text-sm font-normal text-gray-500" x-text="'(' + screenedCount + ' awaiting decision)'"></span>
            </h3>
            <form method="POST" action="{{ route('admin.selection.process-decisions') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition" onclick="return confirm('Process decisions for all screened applicants? This will score and assign selected/reserve/rejected statuses automatically.')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Process Decisions Now
                </button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="table-header-gradient">
                    <th class="px-6 py-4 text-left text-white/90">Applicant</th>
                    <th class="px-6 py-4 text-left text-white/90">GAF ID</th>
                    <th class="px-6 py-4 text-left text-white/90">Medical</th>
                    <th class="px-6 py-4 text-left text-white/90">Fitness</th>
                    <th class="px-6 py-4 text-left text-white/90">Interview</th>
                    <th class="px-6 py-4 text-left text-white/90">Status</th>
                    <th class="px-6 py-4 text-right text-white/90">Decision</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($screenedApplicants as $app)
                <tr class="hover:bg-gray-50" style="border-left: 3px solid transparent;">
                    <td class="px-6 py-4 font-medium">{{ $app->applicant->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $app->gaf_id ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{!! status_badge($app->screeningResult?->medical_result ?? 'pending', 'screening') !!}</td>
                    <td class="px-6 py-4">{!! status_badge($app->screeningResult?->fitness_result ?? 'pending', 'screening') !!}</td>
                    <td class="px-6 py-4">{!! status_badge($app->screeningResult?->interview_result ?? 'pending', 'screening') !!}</td>
                    <td class="px-6 py-4">{!! status_badge($app->status) !!}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-1">
                        <button @click="evalOpen = evalOpen === {{ $app->id }} ? null : {{ $app->id }}; openConfirm({{ $app->id }}, 'selected', '{{ $app->applicant->name }}')" class="relative group p-1.5 rounded-lg hover:bg-green-50 text-green-600 transition-colors" title="Admit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Admit</span>
                        </button>
                        <button @click="openConfirm({{ $app->id }}, 'reserve', '{{ $app->applicant->name }}')" class="relative group p-1.5 rounded-lg hover:bg-blue-50 text-blue-600 transition-colors" title="Reserve">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Reserve</span>
                        </button>
                        <button @click="openConfirm({{ $app->id }}, 'deferred', '{{ $app->applicant->name }}')" class="relative group p-1.5 rounded-lg hover:bg-yellow-50 text-yellow-600 transition-colors" title="Defer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Defer</span>
                        </button>
                        <button @click="openConfirm({{ $app->id }}, 'rejected', '{{ $app->applicant->name }}')" class="relative group p-1.5 rounded-lg hover:bg-red-50 text-red-600 transition-colors" title="Reject">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Reject</span>
                        </button>
                        </div>
                    </td>
                </tr>
                <tr x-show="evalOpen === {{ $app->id }}" x-cloak>
                    <td colspan="7" class="px-6 py-4 bg-gray-50">
                        <div class="text-sm">
                            <p class="font-medium text-gray-700 mb-2">Committee Evaluation Scores</p>
                            <div class="grid grid-cols-5 gap-3 max-w-2xl">
                                <div><label class="block text-xs text-gray-500">Leadership</label>
                                    <select x-model="evalScores.leadership" class="border border-gray-300 rounded px-2 py-1 text-xs w-full">
                                        @for($i=1;$i<=10;$i++)<option value="{{$i}}">{{$i}}</option>@endfor
                                    </select>
                                </div>
                                <div><label class="block text-xs text-gray-500">Communication</label>
                                    <select x-model="evalScores.communication" class="border border-gray-300 rounded px-2 py-1 text-xs w-full">
                                        @for($i=1;$i<=10;$i++)<option value="{{$i}}">{{$i}}</option>@endfor
                                    </select>
                                </div>
                                <div><label class="block text-xs text-gray-500">Technical</label>
                                    <select x-model="evalScores.technical" class="border border-gray-300 rounded px-2 py-1 text-xs w-full">
                                        @for($i=1;$i<=10;$i++)<option value="{{$i}}">{{$i}}</option>@endfor
                                    </select>
                                </div>
                                <div><label class="block text-xs text-gray-500">Discipline</label>
                                    <select x-model="evalScores.discipline" class="border border-gray-300 rounded px-2 py-1 text-xs w-full">
                                        @for($i=1;$i<=10;$i++)<option value="{{$i}}">{{$i}}</option>@endfor
                                    </select>
                                </div>
                                <div><label class="block text-xs text-gray-500">Overall</label>
                                    <select x-model="evalScores.overall" class="border border-gray-300 rounded px-2 py-1 text-xs w-full">
                                        @for($i=1;$i<=10;$i++)<option value="{{$i}}">{{$i}}</option>@endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/></svg>
                        <p class="text-sm font-medium">No applicants awaiting committee decision</p>
                        <p class="text-xs text-gray-400 mt-1">Applicants who complete the screening process will appear here.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Confirm Decision Modal --}}
    <div x-show="showConfirmModal" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="glass-strong rounded-xl shadow-2xl p-8 max-w-md w-full mx-4" style="background:rgba(255,255,255,0.95);backdrop-filter:blur(16px);">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-2">Confirm Committee Decision</h3>
            <p class="text-sm text-gray-500 mb-4">
                <span x-text="confirmName"></span> &mdash;
                <span class="font-semibold" x-text="confirmDecision.charAt(0).toUpperCase() + confirmDecision.slice(1)"></span>
            </p>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks <span class="text-red-500" x-show="confirmDecision === 'rejected' || confirmDecision === 'reserve'">*</span></label>
                <textarea x-model="confirmRemarks" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gaf-khaki" placeholder="Required for reject/reserve decisions..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button @click="showConfirmModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                <form method="POST" :action="'{{ route('admin.selection.finalize') }}'" x-ref="decisionForm">
                    @csrf
                    <input type="hidden" name="application_id" x-model="confirmAppId">
                    <input type="hidden" name="decision" x-model="confirmDecision">
                    <button @click="submitDecision($el.closest('form'))" type="button" class="px-6 py-2 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green">Confirm Decision</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection