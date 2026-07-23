@extends('layouts.admin')

@section('title', 'Recruitment Cycles - Ghana Armed Forces')

@section('content')
<div x-data="cycleManager()" x-init="{{ $errors->any() && old('name') ? '$nextTick(() => { showModal = true; restoreOld() })' : '' }}" class="space-y-6">
    <div class="flex items-center justify-between gradient-border pb-4">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Recruitment Cycles</h1>
        <button @click="openCreate()" class="px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">+ New Cycle</button>
    </div>


    <div class="glass-strong rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="table-header-gradient">
                    <th class="px-6 py-4 text-left text-white/90">Name</th>
                    <th class="px-6 py-4 text-left text-white/90">Code</th>
                    <th class="px-6 py-4 text-left text-white/90">Start Date</th>
                    <th class="px-6 py-4 text-left text-white/90">End Date</th>
                    <th class="px-6 py-4 text-left text-white/90">Vacancies</th>
                    <th class="px-6 py-4 text-left text-white/90">Voucher Price</th>
                    <th class="px-6 py-4 text-left text-white/90">Applications</th>
                    <th class="px-6 py-4 text-left text-white/90">Status</th>
                    <th class="px-6 py-4 text-right text-white/90">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($cycles as $c)
                <tr class="hover:bg-gray-50" style="border-left:3px solid {{ $c->status === 'active' ? '#22c55e' : ($c->status === 'draft' ? '#D4AF37' : '#ef4444') }};">
                    <td class="px-6 py-4 font-medium">{{ $c->name }}</td>
                    <td class="px-6 py-4">{{ $c->cycle_code }}</td>
                    <td class="px-6 py-4">{{ $c->start_date?->format('Y-m-d') }}</td>
                    <td class="px-6 py-4">{{ $c->end_date?->format('Y-m-d') }}</td>
                    <td class="px-6 py-4">{{ number_format($c->total_vacancies) }}</td>
                    <td class="px-6 py-4">GHS {{ number_format($c->voucher_price ?? config('recruitment.voucher_costs.regular', 50), 2) }}</td>
                    <td class="px-6 py-4">{{ number_format($c->applications_count) }}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ status_color($c->status, 'cycle') }}">{{ status_label($c->status, 'cycle') }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-1">
                            @if($c->status !== 'archived')
                                <button @click="openEdit({{ $c->id }}, '{{ $c->name }}', '{{ $c->cycle_code }}', '{{ $c->start_date?->format('Y-m-d') }}', '{{ $c->end_date?->format('Y-m-d') }}', '{{ $c->application_deadline?->format('Y-m-d\TH:i') }}', '{{ $c->total_vacancies }}', '{{ $c->voucher_price }}', {{ $c->ai_enabled ? 'true' : 'false' }}, {{ json_encode($c->requirements) }}, {{ json_encode($c->scoring_weights) }})" class="relative group p-1.5 rounded-lg hover:bg-gaf-khaki/10 text-gaf-khaki transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Edit</span>
                                </button>
                            @endif
                            @if(in_array($c->status, ['draft', 'closed']))
                                <form action="{{ route('admin.cycles.activate', $c) }}" method="POST" class="inline" onsubmit="return confirm('Activate this cycle? It will become visible to applicants.')">
                                    @csrf @method('PUT')
                                    <button type="submit" class="relative group p-1.5 rounded-lg hover:bg-green-50 text-green-500 hover:text-green-700 transition-colors" title="Activate">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">{{ $c->status === 'draft' ? 'Publish' : 'Activate' }}</span>
                                    </button>
                                </form>
                            @endif
                            @if($c->status === 'active')
                                <form action="{{ route('admin.cycles.close', $c) }}" method="POST" class="inline" onsubmit="return confirm('Deactivate this cycle? New applications will not be accepted.')">
                                    @csrf @method('PUT')
                                    <button type="submit" class="relative group p-1.5 rounded-lg hover:bg-red-50 text-red-400 hover:text-red-600 transition-colors" title="Deactivate">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Deactivate</span>
                                    </button>
                                </form>
                            @endif
                            @if($c->status === 'closed')
                                <form action="{{ route('admin.cycles.archive', $c) }}" method="POST" class="inline" onsubmit="return confirm('Archive this cycle? This cannot be undone.')">
                                    @csrf @method('PUT')
                                    <button type="submit" class="relative group p-1.5 rounded-lg hover:bg-gray-200 text-gray-500 hover:text-gray-700 transition-colors" title="Archive">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Archive</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                        <p class="text-sm font-medium">No cycles created yet</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="cycle-old-data" data-old="{{ json_encode(old()) }}" class="hidden"></div>

    {{-- Create/Edit Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-start justify-center pt-12 pb-8 bg-black bg-opacity-50 overflow-y-auto" @click.outside="showModal = false">
        <div class="glass-strong rounded-xl shadow-lg w-full max-w-2xl mx-4 p-8" style="background:rgba(255,255,255,0.95);backdrop-filter:blur(16px);">
            <h2 class="font-heading font-bold text-xl text-gray-800 mb-6" x-text="editingId ? 'Edit Cycle' : 'Create New Cycle'"></h2>

            <form :action="editingId ? '{{ url('admin/cycles') }}/' + editingId : '{{ route('admin.cycles.store') }}'" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="editingId ? 'PUT' : 'POST'">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    @php $f = 'name'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cycle Name</label>
                        <input type="text" name="{{ $f }}" x-model="form.name" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}" required>
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'cycle_code'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cycle Code</label>
                        <input type="text" name="{{ $f }}" x-model="form.cycle_code" @input="form.cycle_code = $event.target.value.replace(/[^a-zA-Z0-9_-]/g, '')" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}" :readonly="editingId" required>
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'start_date'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="{{ $f }}" x-model="form.start_date" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}" required>
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'end_date'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" name="{{ $f }}" x-model="form.end_date" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}" required>
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'application_deadline'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Application Deadline</label>
                        <input type="datetime-local" name="{{ $f }}" x-model="form.deadline" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}" required>
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'total_vacancies'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Vacancies</label>
                        <input type="number" name="{{ $f }}" x-model="form.vacancies" min="1" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}" required>
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'voucher_price'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Voucher Price (GHS)</label>
                        <input type="number" name="{{ $f }}" x-model="form.voucher_price" step="0.01" min="0" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}" placeholder="Leave blank to use default">
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Eligibility Criteria --}}
                <div class="border-t pt-4 mb-4">
                    <h3 class="font-heading font-bold text-base text-gray-800 mb-3">Eligibility Criteria</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Age</label>
                            <input type="number" name="requirements[min_age]" x-model="form.req.min_age" min="1" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Age</label>
                            <input type="number" name="requirements[max_age]" x-model="form.req.max_age" min="1" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Height (Male) in m</label>
                            <input type="number" name="requirements[min_height_male]" x-model="form.req.min_height_male" step="0.01" min="1.0" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Height (Female) in m</label>
                            <input type="number" name="requirements[min_height_female]" x-model="form.req.min_height_female" step="0.01" min="1.0" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                            <input type="text" name="requirements[nationality]" x-model="form.req.nationality" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Marital Status (comma-separated)</label>
                            <input type="text" name="requirements[marital_status]" x-model="form.req.marital_status" placeholder="Single, Married" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki">
                            <p class="text-xs text-gray-400 mt-0.5">Leave empty to allow all</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Required Education Levels</label>
                        <div class="flex flex-wrap gap-3">
                            @foreach(config('recruitment.education_levels') as $edu)
                            <label class="flex items-center space-x-1.5 text-sm">
                                <input type="checkbox" name="requirements[education_levels][]" value="{{ $edu }}" x-model="form.req.education_levels" class="rounded border-gray-300 text-gaf-khaki focus:ring-gaf-khaki">
                                <span>{{ $edu }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="flex items-center space-x-2 text-sm">
                            <input type="checkbox" name="requirements[national_id_required]" value="1" x-model="form.req.national_id_required" class="rounded border-gray-300 text-gaf-khaki focus:ring-gaf-khaki">
                            <span>Require National ID for application</span>
                        </label>
                    </div>
                </div>

                {{-- Scoring Weights --}}
                <div class="border-t pt-4 mb-4">
                    <h3 class="font-heading font-bold text-base text-gray-800 mb-3">Scoring Weights (overrides global defaults)</h3>
                    <p class="text-xs text-gray-500 mb-3">Composite score = medical×weight + interview×weight + fitness×weight + eligibility×weight. Leave blank to use global defaults.</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Medical</label>
                            <input type="number" name="scoring_weights[medical]" x-model="form.scoring.medical" step="0.01" min="0" max="1" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Interview</label>
                            <input type="number" name="scoring_weights[interview]" x-model="form.scoring.interview" step="0.01" min="0" max="1" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fitness</label>
                            <input type="number" name="scoring_weights[fitness]" x-model="form.scoring.fitness" step="0.01" min="0" max="1" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Eligibility</label>
                            <input type="number" name="scoring_weights[eligibility]" x-model="form.scoring.eligibility" step="0.01" min="0" max="1" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki">
                        </div>
                    </div>
                </div>

                {{-- AI Toggle --}}
                <div class="border-t pt-4 mb-4">
                    <label class="flex items-center space-x-2 text-sm">
                        <input type="checkbox" name="ai_enabled" value="1" x-model="form.ai_enabled" class="rounded border-gray-300 text-gaf-khaki focus:ring-gaf-khaki">
                        <span>Enable AI Processing (document analysis, fraud detection, ranking)</span>
                    </label>
                </div>

                <div class="flex space-x-3 pt-2">
                    <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition" x-text="editingId ? 'Update Cycle' : 'Create Cycle'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cycleManager() {
    return {
        showModal: false,
        editingId: null,
            form: {
                name: '',
                cycle_code: '',
                start_date: '',
                end_date: '',
                deadline: '',
                vacancies: '',
                voucher_price: '',
                ai_enabled: false,
                scoring: {
                    medical: {{ config('recruitment.scoring_weights.medical') }},
                    interview: {{ config('recruitment.scoring_weights.interview') }},
                    fitness: {{ config('recruitment.scoring_weights.fitness') }},
                    eligibility: {{ config('recruitment.scoring_weights.eligibility') }},
                },
                req: {
                    min_age: {{ config('recruitment.age_min') }},
                    max_age: {{ config('recruitment.age_max_regular') }},
                    min_height_male: {{ config('recruitment.height_min_male') }},
                    min_height_female: {{ config('recruitment.height_min_female') }},
                    nationality: '{{ config('recruitment.nationality') }}',
                    marital_status: '',
                    education_levels: [],
                    national_id_required: true,
                }
            },
            openCreate() {
                this.editingId = null;
                this.form = {
                    name: '',
                    cycle_code: '',
                    start_date: '',
                    end_date: '',
                    deadline: '',
                    vacancies: '',
                    voucher_price: '',
                    ai_enabled: false,
                    scoring: {
                        medical: {{ config('recruitment.scoring_weights.medical') }},
                        interview: {{ config('recruitment.scoring_weights.interview') }},
                        fitness: {{ config('recruitment.scoring_weights.fitness') }},
                        eligibility: {{ config('recruitment.scoring_weights.eligibility') }},
                    },
                    req: {
                        min_age: {{ config('recruitment.age_min') }},
                        max_age: {{ config('recruitment.age_max_regular') }},
                        min_height_male: {{ config('recruitment.height_min_male') }},
                        min_height_female: {{ config('recruitment.height_min_female') }},
                        nationality: '{{ config('recruitment.nationality') }}',
                        marital_status: '',
                        education_levels: [],
                        national_id_required: true,
                    }
                };
                this.showModal = true;
            },
            openEdit(id, name, code, start, end, deadline, vacancies, voucherPrice, aiEnabled, req, scoring) {
                this.editingId = id;
                let r = req || {};
                let s = scoring || {};
                this.form = {
                    name: name,
                    cycle_code: code,
                    start_date: start,
                    end_date: end,
                    deadline: deadline,
                    vacancies: vacancies,
                    voucher_price: voucherPrice,
                    ai_enabled: aiEnabled,
                    scoring: {
                        medical: s.medical !== undefined ? s.medical : {{ config('recruitment.scoring_weights.medical') }},
                        interview: s.interview !== undefined ? s.interview : {{ config('recruitment.scoring_weights.interview') }},
                        fitness: s.fitness !== undefined ? s.fitness : {{ config('recruitment.scoring_weights.fitness') }},
                        eligibility: s.eligibility !== undefined ? s.eligibility : {{ config('recruitment.scoring_weights.eligibility') }},
                    },
                    req: {
                        min_age: r.min_age || 18,
                        max_age: r.max_age || 26,
                        min_height_male: r.min_height_male || 1.68,
                        min_height_female: r.min_height_female || 1.60,
                        nationality: r.nationality || 'Ghanaian',
                        marital_status: Array.isArray(r.marital_status) ? r.marital_status.join(', ') : (r.marital_status || ''),
                        education_levels: Array.isArray(r.education_levels) ? r.education_levels : [],
                        national_id_required: r.national_id_required !== false,
                    }
                };
                this.showModal = true;
            },
            restoreOld() {
                const el = document.getElementById('cycle-old-data');
                if (!el) return;
                try {
                    const old = JSON.parse(el.dataset.old || '{}');
                    if (old.name) this.form.name = old.name;
                    if (old.cycle_code) this.form.cycle_code = old.cycle_code;
                    if (old.start_date) this.form.start_date = old.start_date;
                    if (old.end_date) this.form.end_date = old.end_date;
                    if (old.application_deadline) this.form.deadline = old.application_deadline;
                    if (old.total_vacancies) this.form.vacancies = old.total_vacancies;
                    if (old.voucher_price !== undefined && old.voucher_price !== null && old.voucher_price !== '') this.form.voucher_price = old.voucher_price;
                    if (old.requirements) {
                        if (old.requirements.min_age) this.form.req.min_age = old.requirements.min_age;
                        if (old.requirements.max_age) this.form.req.max_age = old.requirements.max_age;
                        if (old.requirements.min_height_male) this.form.req.min_height_male = old.requirements.min_height_male;
                        if (old.requirements.min_height_female) this.form.req.min_height_female = old.requirements.min_height_female;
                        if (old.requirements.nationality) this.form.req.nationality = old.requirements.nationality;
                        if (old.requirements.marital_status) this.form.req.marital_status = old.requirements.marital_status;
                        if (old.requirements.education_levels) this.form.req.education_levels = old.requirements.education_levels;
                        if (old.requirements.national_id_required !== undefined) this.form.req.national_id_required = old.requirements.national_id_required === '1';
                    }
                    if (old.scoring_weights) {
                        if (old.scoring_weights.medical) this.form.scoring.medical = parseFloat(old.scoring_weights.medical);
                        if (old.scoring_weights.interview) this.form.scoring.interview = parseFloat(old.scoring_weights.interview);
                        if (old.scoring_weights.fitness) this.form.scoring.fitness = parseFloat(old.scoring_weights.fitness);
                        if (old.scoring_weights.eligibility) this.form.scoring.eligibility = parseFloat(old.scoring_weights.eligibility);
                    }
                    if (old.ai_enabled !== undefined) this.form.ai_enabled = old.ai_enabled === '1';
                } catch (e) {}
            }
    }
}
</script>
@endsection