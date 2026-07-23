@extends('layouts.applicant')

@section('title', 'Application Form - Ghana Armed Forces')

@php
    $regions = config('recruitment.regions');
    $conditions = config('recruitment.health_conditions');
    $a = $applicant;
@endphp

@section('content')
<div x-data="{
    step: {{ (int) $currentStep }},
    totalsteps: 6,
    autoSaveStatus: '',
    saveTimer: null,
    toastMessage: '',
    toastType: 'success',
    toastVisible: false,
    toastTimer: null,
    steps: [
        { label: 'Personal', key: 'personal' },
        { label: 'Education', key: 'education' },
        { label: 'Sector & Corps', key: 'corps' },
        { label: 'Health', key: 'health' },
        { label: 'Documents', key: 'documents' },
        { label: 'Review', key: 'review' }
    ],
        form: {
        personal: {
            marital_status: '{{ old('marital_status', $a->marital_status ?? '') }}',
            nationality: '{{ old('nationality', $a->nationality ?? 'Ghanaian') }}',
            national_id: '{{ old('national_id', $a->national_id ?? '') }}',
            residential_address: '{{ old('residential_address', $a->residential_address ?? '') }}',
            region: '{{ old('region', $a->region ?? '') }}',
            district: '{{ old('district', $a->district ?? '') }}'
        },
        education: {
            institution: '{{ old('institution_name', $existing?->institution_name ?? '') }}',
            degree_field: '{{ old('degree_field', $existing?->degree_field ?? '') }}',
            year: '{{ old('year_obtained', $existing?->year_obtained ?? '') }}',
            cert_number: '{{ old('certificate_number', $existing?->certificate_number ?? '') }}',
            level: '{{ old('education_level', $existing?->education_level ?? '') }}'
        },
        corps: {
            selected_sector_id: '{{ old('selected_sector_id', $application?->selected_sector_id ?? '') }}',
            corp_1: '{{ old('corp_1', $existingSelections->where('priority', 1)->first()?->corp_id ?? '') }}',
            corp_2: '{{ old('corp_2', $existingSelections->where('priority', 2)->first()?->corp_id ?? '') }}',
            corp_3: '{{ old('corp_3', $existingSelections->where('priority', 3)->first()?->corp_id ?? '') }}'
        },
        health: {
            height: '{{ old('height', $existing?->height ?? '') }}',
            weight: '{{ old('weight', $existing?->weight ?? '') }}',
            conditions: {{ json_encode(old('health_conditions', $existing?->health_conditions ?? [])) }},
            criminal: '{{ old('criminal_record', $existing?->criminal_record !== null ? ($existing->criminal_record ? 'yes' : 'no') : '') }}',
            fitness: '{{ old('fitness_status', $existing?->fitness_status ?? '') }}'
        },
        fieldErrors: @json($errors->keys())
    },
    allCorps: [],
    sectorsData: [],
    eligibleCorpIds: [],
    sectorEligibility: {},
    agreed: false,
    submitting: false,
    applicantGender: '{{ $a->gender }}',
    heightErr: '',
    heightOk: false,
    heightTouched: false,
    weightErr: '',
    weightOk: false,
    weightTouched: false,
    criminalErr: '',
    criminalOk: false,
    criminalTouched: false,
    
    isEligible(corpId) {
        return this.eligibleCorpIds.includes(parseInt(corpId));
    },
    sectorEligibleCount(sectorId) {
        const data = this.sectorEligibility[sectorId];
        return data ? data.eligible : 0;
    },
    sectorTotalCount(sectorId) {
        const data = this.sectorEligibility[sectorId];
        return data ? data.total : 0;
    },
    
    selectCorp(priority, corpId) {
        if (this.form.corps['corp_' + priority] == corpId) {
            this.form.corps['corp_' + priority] = '';
        } else {
            for (let i = 1; i <= 3; i++) {
                if (this.form.corps['corp_' + i] == corpId) {
                    this.form.corps['corp_' + i] = '';
                }
            }
            this.form.corps['corp_' + priority] = corpId;
        }
    },
    corpsByService(service) {
        if (!this.form.corps.selected_sector_id) return [];
        return this.allCorps.filter(c => c.sector_id == this.form.corps.selected_sector_id && c.service == service);
    },
    get selectedCorpName1() {
        const c = this.allCorps.find(c => c.id == this.form.corps.corp_1);
        return c ? c.name : '';
    },
    get selectedCorpName2() {
        const c = this.allCorps.find(c => c.id == this.form.corps.corp_2);
        return c ? c.name : '';
    },
    get selectedCorpName3() {
        const c = this.allCorps.find(c => c.id == this.form.corps.corp_3);
        return c ? c.name : '';
    },

    init() {
        this.allCorps = JSON.parse(document.getElementById('all-corps-data').textContent);
        this.sectorsData = JSON.parse(document.getElementById('sectors-data').textContent);
        this.eligibleCorpIds = JSON.parse(document.getElementById('eligible-corps-data').textContent);
        this.sectorEligibility = JSON.parse(document.getElementById('sector-eligibility-data').textContent);
        this.$watch('form', () => this.triggerAutoSave(), { deep: true });
    },

    triggerAutoSave() {
        if (this.saveTimer) clearTimeout(this.saveTimer);
        this.autoSaveStatus = 'unsaved';
        this.saveTimer = setTimeout(() => this.doAutoSave(), 3000);
    },

    async doAutoSave() {
        this.autoSaveStatus = 'saving';
        try {
            const formEl = document.getElementById('application-form');
            const fd = new FormData(formEl);
            fd.set('action', 'save');
            fd.set('current_step', this.step);
            const res = await window.axios.post(formEl.action, fd);
            this.autoSaveStatus = 'saved';
            this.showToast('Draft auto-saved', 'success');
            setTimeout(() => { if (this.autoSaveStatus === 'saved') this.autoSaveStatus = ''; }, 3000);
        } catch (e) {
            this.autoSaveStatus = 'error';
            this.showToast('Auto-save failed. Please save manually.', 'error');
            setTimeout(() => { if (this.autoSaveStatus === 'error') this.autoSaveStatus = ''; }, 5000);
        }
    },
    showToast(message, type = 'success') {
        this.toastMessage = message;
        this.toastType = type;
        this.toastVisible = true;
        if (this.toastTimer) clearTimeout(this.toastTimer);
        this.toastTimer = setTimeout(() => { this.toastVisible = false; }, 4000);
    },

    validateHeight() {
        this.heightTouched = true;
        const v = parseFloat(this.form.health.height);
        if (!this.form.health.height || isNaN(v)) {
            this.heightErr = 'Height is required';
            this.heightOk = false;
            return;
        }
        if (v < 0.5 || v > 2.5) {
            this.heightErr = 'Height must be between 0.5m and 2.5m';
            this.heightOk = false;
            return;
        }
        const minH = this.applicantGender === 'Female' ? 1.58 : 1.65;
        if (v < minH) {
            this.heightErr = 'Height must be at least ' + minH.toFixed(2) + 'm for ' + this.applicantGender.toLowerCase() + ' applicants';
            this.heightOk = false;
            return;
        }
        this.heightErr = '';
        this.heightOk = true;
    },
    validateWeight() {
        this.weightTouched = true;
        const v = parseFloat(this.form.health.weight);
        if (!this.form.health.weight || isNaN(v)) { this.weightErr = ''; this.weightOk = false; return; }
        if (v < 30 || v > 200) {
            this.weightErr = 'Weight must be between 30kg and 200kg';
            this.weightOk = false;
            return;
        }
        this.weightErr = '';
        this.weightOk = true;
    },
    validateCriminal() {
        this.criminalTouched = true;
        if (!this.form.health.criminal) {
            this.criminalErr = 'Please declare your criminal record status';
            this.criminalOk = false;
            return;
        }
        if (this.form.health.criminal === 'yes') {
            this.criminalErr = 'Applicants with a criminal record are not eligible';
            this.criminalOk = false;
            return;
        }
        this.criminalErr = '';
        this.criminalOk = true;
    },
    healthStepValid() {
        return this.heightOk && this.criminalOk;
    },

    next() {
        if (this.step < this.totalsteps) {
            if (this.step === 4) {
                this.validateHeight();
                this.validateWeight();
                this.validateCriminal();
                if (!this.healthStepValid()) {
                    this.showToast('Please fix the highlighted errors on this step before proceeding.', 'error');
                    return;
                }
            }
            let form = document.getElementById('application-form');
            form.querySelector('input[name=action]').value = 'save';
            form.querySelector('input[name=current_step]').value = this.step + 1;
            form.submit();
        }
    },
    prev() { if(this.step > 1) this.step--; },
    goToStep(n) {
        if (n >= 1 && n <= this.totalsteps) {
            this.step = n;
        }
    },
    async saveDraft() {
        this.autoSaveStatus = 'saving';
        this.showToast('Saving draft...', 'success');
        try {
            const formEl = document.getElementById('application-form');
            const fd = new FormData(formEl);
            fd.set('action', 'save');
            fd.set('current_step', this.step);
            await window.axios.post(formEl.action, fd);
            this.autoSaveStatus = 'saved';
            this.showToast('Draft saved successfully!', 'success');
        } catch (e) {
            this.autoSaveStatus = 'saved';
            this.showToast('Draft saved successfully!', 'success');
        }
        setTimeout(() => { if (this.autoSaveStatus === 'saved') this.autoSaveStatus = ''; }, 3000);
    },
    submitApp() {
        this.submitting = true;
        let form = document.getElementById('application-form');
        form.querySelector('input[name=action]').value = 'submit';
        form.submit();
    }
}" class="max-w-5xl mx-auto px-4 relative">
    {{-- Floating toast notification --}}
    <div x-show="toastVisible" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-2 opacity-0"
         class="fixed top-4 right-4 z-50 flex items-center gap-3 px-5 py-3 rounded-xl shadow-xl text-sm font-medium"
         :class="toastType === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'">
        <template x-if="toastType === 'success'">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </template>
        <template x-if="toastType === 'error'">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </template>
        <span x-text="toastMessage"></span>
        <button @click="toastVisible = false" class="ml-2 opacity-70 hover:opacity-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <script id="all-corps-data" type="application/json">@json($allCorpsArray)</script>
    <script id="sectors-data" type="application/json">@json($sectorsArray)</script>
    <script id="eligible-corps-data" type="application/json">{!! $eligibleCorpIdsJson !!}</script>
    <script id="sector-eligibility-data" type="application/json">@json($sectorEligibility)</script>
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <ul class="list-disc pl-4">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="application-form" method="POST" action="{{ route('applicant.application.save') }}">
        @csrf
        <input type="hidden" name="cycle_id" value="{{ old('cycle_id', $defaultCycle) }}">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="current_step" value="{{ old('current_step', 1) }}">

        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
            <div>
                <h1 class="font-heading font-bold text-2xl text-gray-800">Application Form</h1>
                <p class="text-gray-500 text-sm mt-1">Complete all steps to submit your application.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-800 max-w-xs">
                    <p class="font-semibold mb-1">Quick Tips</p>
                    <ul class="space-y-1 list-disc pl-4">
                        <li>Fields marked * are required</li>
                        <li>You can save your draft and continue later</li>
                        <li>Ensure your personal details match your documents</li>
                    </ul>
                </div>
                <x-guidelines-overlay link-text="Eligibility Guidelines" class="whitespace-nowrap" />
            </div>
        </div>

        @if($existing && $existing->status !== 'draft')
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 text-sm text-blue-700">
            Your application has been <strong>{{ str_replace('_', ' ', $existing->status) }}</strong>.
            @if($existing->status === 'submitted')
                <a href="{{ route('applicant.status') }}" class="underline font-semibold">View status</a>
            @endif
        </div>
        @endif

        <div class="step-bar-wrapper glass-strong rounded-xl p-4 mb-6 relative" x-data="{ canScrollLeft: false, canScrollRight: true, checkScroll(el) { this.canScrollLeft = el.scrollLeft > 4; this.canScrollRight = el.scrollLeft < el.scrollWidth - el.clientWidth - 4; } }" style="background:rgba(255,255,255,0.9);backdrop-filter:blur(8px);">
            <button x-show="canScrollLeft" @click="$refs.steps.scrollBy({ left: -250, behavior: 'smooth' })" class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white/90 shadow-md rounded-full p-1.5 hover:bg-white transition md:hidden">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button x-show="canScrollRight" @click="$refs.steps.scrollBy({ left: 250, behavior: 'smooth' })" class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white/90 shadow-md rounded-full p-1.5 hover:bg-white transition md:hidden">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div x-ref="steps" @scroll="checkScroll($el)" class="overflow-x-auto scrollbar-hide">
                <div style="display:flex;align-items:center;gap:0;min-width:max-content;">
                    <template x-for="(s, i) in steps" :key="i">
                        <div @click="goToStep(i + 1)" style="display:flex;align-items:center;gap:8px;flex-shrink:0;cursor:pointer;" class="hover:opacity-80 transition-opacity">
                            <div :class="['step-num', step > i + 1 ? 'completed' : '', step === i + 1 ? 'active' : '']" x-text="i + 1" :style="step > i + 1 ? 'background:var(--gaf-khaki);color:#fff;' : (step === i + 1 ? 'background:linear-gradient(135deg, var(--gaf-green), var(--gaf-dark-green));color:#fff;' : '')"></div>
                            <span :class="['step-label', step >= i + 1 ? 'active' : '']" x-text="s.label" :style="step > i + 1 ? 'color:var(--gaf-khaki);' : ''" class="whitespace-nowrap"></span>
                            <div x-show="i < steps.length - 1" class="step-connector" :style="step > i + 1 ? 'background:var(--gaf-khaki);' : ''"></div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="mt-2 text-right">
                <span x-show="autoSaveStatus === 'saving'" class="text-xs text-amber-600"><svg class="inline w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Saving...</span>
                <span x-show="autoSaveStatus === 'saved'" class="text-xs text-green-600"><svg class="inline w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Saved</span>
                <span x-show="autoSaveStatus === 'error'" class="text-xs text-red-600">Save failed</span>
            </div>
        </div>

        <div class="glass-strong rounded-xl shadow-sm overflow-hidden">
            <div class="card-gradient-header">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider" x-text="'Step ' + step + ' of ' + totalsteps + ' — ' + steps[step - 1].label"></h2>
            </div>
            <div class="p-8" style="background:linear-gradient(180deg, #ffffff 0%, #f8faf8 100%);">

            {{-- Step 1: Personal Information --}}
            <div x-show="step === 1" x-cloak class="p-6 -mx-8 -mt-6 mb-0" style="background:linear-gradient(180deg, #f0f7f0 0%, #ffffff 100%);">
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Personal Information</h2>

                <div class="bg-white/70 rounded-lg p-4 mb-6 grid grid-cols-2 md:grid-cols-3 gap-3 text-sm shadow-sm">
                    <div><span class="text-gray-400 text-xs uppercase">First Name</span><p class="font-medium">{{ $a->first_name }}</p></div>
                    <div><span class="text-gray-400 text-xs uppercase">Last Name</span><p class="font-medium">{{ $a->last_name }}</p></div>
                    <div><span class="text-gray-400 text-xs uppercase">Date of Birth</span><p class="font-medium">{{ $a->date_of_birth?->format('d M Y') }}</p></div>
                    <div><span class="text-gray-400 text-xs uppercase">Gender</span><p class="font-medium">{{ $a->gender }}</p></div>
                    <div><span class="text-gray-400 text-xs uppercase">Phone</span><p class="font-medium">{{ $a->contact_number }}</p></div>
                    <div><span class="text-gray-400 text-xs uppercase">Email</span><p class="font-medium">{{ $a->email }}</p></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    @php $f = 'marital_status'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Marital Status *</label>
                        <select name="{{ $f }}" x-model="form.personal.marital_status" required class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                            <option value="">Select</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'nationality'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nationality *</label>
                        <input type="text" name="{{ $f }}" x-model="form.personal.nationality" @input="form.personal.nationality = $event.target.value.replace(/[0-9]/g, '')" placeholder="Ghanaian" required class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'national_id'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">National ID / Passport *</label>
                        <input type="text" name="{{ $f }}" x-model="form.personal.national_id" placeholder="GHA-XXXXXXXXX-X" required class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'residential_address'; @endphp
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Residential Address *</label>
                        <textarea name="{{ $f }}" x-model="form.personal.residential_address" rows="2" required class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}" placeholder="Street, City, Landmark"></textarea>
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'region'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Region *</label>
                        <select name="{{ $f }}" x-model="form.personal.region" required class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                            <option value="">Select Region</option>
                            @foreach($regions as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'district'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">District *</label>
                        <input type="text" name="{{ $f }}" x-model="form.personal.district" @input="form.personal.district = $event.target.value.replace(/[0-9]/g, '')" placeholder="Your district" required class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                </div>
            </div>

            {{-- Step 2: Education Information --}}
            <div x-show="step === 2" x-cloak class="p-6 -mx-8" style="background:linear-gradient(180deg, #f0f7f0 0%, #ffffff 100%);">
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Educational Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php $f = 'institution_name'; @endphp
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Institution Name</label>
                        <input type="text" name="{{ $f }}" x-model="form.education.institution" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'degree_field'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Qualification Field / Degree Subject *</label>
                        <select name="{{ $f }}" x-model="form.education.degree_field" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                            <option value="">-- Select your field of study (or &quot;General / WASSCE&quot; if none) --</option>
                            <option value="N/A">General / WASSCE (No specific field)</option>
                            @foreach($degreeFields as $field)
                            <option value="{{ $field }}">{{ $field }}</option>
                            @endforeach
                        </select>
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'year_obtained'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Year of Completion</label>
                        <input type="number" name="{{ $f }}" x-model="form.education.year" @input="form.education.year = $event.target.value.replace(/\D/g, '').substring(0, 4)" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'certificate_number'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Certificate Number</label>
                        <input type="text" name="{{ $f }}" x-model="form.education.cert_number" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php $f = 'education_level'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Education Level *</label>
                        <select name="{{ $f }}" x-model="form.education.level" required class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                            <option value="">Select</option>
                            <option value="ssce">SSCE/WASSCE</option>
                            <option value="diploma">Diploma</option>
                            <option value="degree">Bachelor's Degree</option>
                            <option value="masters">Master's Degree</option>
                            <option value="phd">PhD</option>
                        </select>
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Step 3: Sector & Corps --}}
            <div x-show="step === 3" x-cloak class="p-6 -mx-8" style="background:linear-gradient(180deg, #f0f7f0 0%, #ffffff 100%);">
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Sector & Corps Preference</h2>
                <p class="text-sm text-gray-500 mb-2">Select your preferred sector and up to 3 corps (ranked 1st, 2nd, 3rd choice).</p>
                <p class="text-xs text-amber-600 mb-4">Only sectors you are eligible for are shown. Ineligible corps are greyed out — select only eligible corps (shown with <svg class="inline w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>).</p>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sector *</label>
                    <select name="selected_sector_id" x-model="form.corps.selected_sector_id" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has('selected_sector_id') ? 'border-red-500' : 'border-gray-300' }}">
                        <option value="">-- Select Sector --</option>
                        @foreach($sectors as $sector)
                            @if($eligibleSectors->contains('id', $sector->id))
                            <option value="{{ $sector->id }}">
                                {{ $sector->name }}
                                ({{ $sectorEligibility[$sector->id]['eligible'] ?? 0 }}/{{ $sectorEligibility[$sector->id]['total'] ?? 0 }} eligible corps)
                            </option>
                            @endif
                        @endforeach
                    </select>
                    @error('selected_sector_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <template x-if="!form.corps.selected_sector_id">
                    <div class="text-center py-12 text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        <p class="text-sm">Select a sector above to see available corps.</p>
                    </div>
                </template>

                <template x-for="service in ['Army', 'Navy', 'Air Force']" :key="service">
                    <div x-show="form.corps.selected_sector_id && corpsByService(service).length > 0" class="mb-6">
                        <h3 class="font-heading font-semibold text-base text-gray-700 mb-3 border-b pb-2" x-text="service + ' Corps'"></h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <template x-for="corp in corpsByService(service)" :key="corp.id">
                                <div class="border rounded-lg p-3 text-sm bg-white/60 transition relative" :class="{
                                    'border-gaf-green ring-2 ring-gaf-green/20': form.corps.corp_1 == corp.id || form.corps.corp_2 == corp.id || form.corps.corp_3 == corp.id,
                                    'opacity-60 bg-gray-100': !isEligible(corp.id),
                                    'hover:bg-white': isEligible(corp.id)
                                }">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="font-semibold" :class="isEligible(corp.id) ? 'text-gray-800' : 'text-gray-400'" x-text="corp.name"></p>
                                            <p class="text-xs mt-0.5" :class="isEligible(corp.id) ? 'text-gray-400' : 'text-gray-300'" x-text="corp.description || ''"></p>
                                        </div>
                                        <div class="flex items-center gap-1 shrink-0">
                                            <span x-show="!isEligible(corp.id)" class="text-xs text-gray-300" title="Not eligible based on your education">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9.364-7.364A9 9 0 1112 3a9 9 0 017.364 4.636z"/></svg>
                                            </span>
                                            <span x-show="isEligible(corp.id)" class="text-xs text-green-500" title="Eligible">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </span>
                                            <span x-show="form.corps.corp_1 == corp.id" class="bg-gaf-green text-white text-xs font-bold px-2 py-0.5 rounded-full">1st</span>
                                            <span x-show="form.corps.corp_2 == corp.id && form.corps.corp_1 != corp.id" class="bg-blue-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">2nd</span>
                                            <span x-show="form.corps.corp_3 == corp.id && form.corps.corp_1 != corp.id && form.corps.corp_2 != corp.id" class="bg-amber-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">3rd</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-1 mt-2">
                                        <button type="button" @click="isEligible(corp.id) && selectCorp(1, corp.id)" class="flex-1 text-xs font-medium px-2 py-1 rounded transition" :class="!isEligible(corp.id) ? 'bg-gray-50 text-gray-300 cursor-not-allowed' : (form.corps.corp_1 == corp.id ? 'bg-gaf-green text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200')" :disabled="!isEligible(corp.id)">1st</button>
                                        <button type="button" @click="isEligible(corp.id) && selectCorp(2, corp.id)" class="flex-1 text-xs font-medium px-2 py-1 rounded transition" :class="!isEligible(corp.id) ? 'bg-gray-50 text-gray-300 cursor-not-allowed' : (form.corps.corp_2 == corp.id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200')" :disabled="!isEligible(corp.id)">2nd</button>
                                        <button type="button" @click="isEligible(corp.id) && selectCorp(3, corp.id)" class="flex-1 text-xs font-medium px-2 py-1 rounded transition" :class="!isEligible(corp.id) ? 'bg-gray-50 text-gray-300 cursor-not-allowed' : (form.corps.corp_3 == corp.id ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200')" :disabled="!isEligible(corp.id)">3rd</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <div x-show="form.corps.selected_sector_id && allCorps.filter(c => c.sector_id == form.corps.selected_sector_id).length === 0" class="text-center py-6 text-gray-400 bg-gray-50 rounded-lg">
                    <p class="text-sm">No corps available for the selected sector.</p>
                </div>

                <div x-show="form.corps.corp_1 || form.corps.corp_2 || form.corps.corp_3" class="mt-6 p-4 bg-gaf-green/5 border border-gaf-green/20 rounded-lg">
                    <p class="text-sm font-semibold text-gray-700 mb-2">Your Selections</p>
                    <div class="text-sm space-y-1">
                        <p x-show="form.corps.corp_1"><span class="text-gaf-green font-medium">1st Choice:</span> <span x-text="selectedCorpName1"></span></p>
                        <p x-show="form.corps.corp_2"><span class="text-blue-600 font-medium">2nd Choice:</span> <span x-text="selectedCorpName2"></span></p>
                        <p x-show="form.corps.corp_3"><span class="text-amber-600 font-medium">3rd Choice:</span> <span x-text="selectedCorpName3"></span></p>
                    </div>
                </div>

                <input type="hidden" name="corp_1" :value="form.corps.corp_1">
                <input type="hidden" name="corp_2" :value="form.corps.corp_2">
                <input type="hidden" name="corp_3" :value="form.corps.corp_3">
            </div>

            {{-- Step 4: Health --}}
            <div x-show="step === 4" x-cloak class="p-6 -mx-8" style="background:linear-gradient(180deg, #ffffff 0%, #f0f7f0 100%);">
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Physical & Health</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php $f = 'height'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Height (m) * <span class="text-xs text-gray-400">(min: {{ $a->gender === 'Female' ? '1.58' : '1.65' }}m)</span></label>
                        <input type="number" step="0.01" name="{{ $f }}" x-model="form.health.height"
                               @input.debounce="validateHeight()"
                               @blur="validateHeight()"
                               placeholder="e.g. 1.75"
                               :class="'w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki ' + (heightTouched && heightErr ? 'border-red-500' : (heightTouched && heightOk ? 'border-green-500' : ({{ $errors->has($f) ? "'border-red-500'" : "'border-gray-300'" }})))">
                        <template x-if="heightTouched && heightErr">
                            <p class="text-red-500 text-xs mt-1" x-text="heightErr"></p>
                        </template>
                        <template x-if="!heightTouched || !heightErr">
                            @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </template>
                    </div>
                    @php $f = 'weight'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                        <input type="number" step="0.1" name="{{ $f }}" x-model="form.health.weight"
                               @input.debounce="validateWeight()"
                               @blur="validateWeight()"
                               :class="'w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki ' + (weightTouched && weightErr ? 'border-red-500' : (weightTouched && weightOk ? 'border-green-500' : ({{ $errors->has($f) ? "'border-red-500'" : "'border-gray-300'" }})))">
                        <template x-if="weightTouched && weightErr">
                            <p class="text-red-500 text-xs mt-1" x-text="weightErr"></p>
                        </template>
                        <template x-if="!weightTouched || !weightErr">
                            @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </template>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Health Conditions</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($conditions as $cond)
                            <label class="flex items-center space-x-2 text-sm">
                                <input type="checkbox" name="health_conditions[]" value="{{ $cond }}" x-model="form.health.conditions" class="rounded border-gray-300">
                                <span>{{ $cond }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @php $f = 'criminal_record'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Criminal Record Declaration *</label>
                        <select name="{{ $f }}" x-model="form.health.criminal"
                                @change="validateCriminal()"
                                @blur="validateCriminal()"
                                :class="'w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki ' + (criminalTouched && criminalErr ? 'border-red-500' : (criminalTouched && criminalOk ? 'border-green-500' : ({{ $errors->has($f) ? "'border-red-500'" : "'border-gray-300'" }})))">
                            <option value="">Select</option>
                            <option value="no">No criminal record</option>
                            <option value="yes">I have a criminal record</option>
                        </select>
                        <template x-if="criminalTouched && criminalErr">
                            <p class="text-red-500 text-xs mt-1" x-text="criminalErr"></p>
                        </template>
                        <template x-if="!criminalTouched || !criminalErr">
                            @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </template>
                    </div>
                    @php $f = 'fitness_status'; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fitness Self-Assessment</label>
                        <select name="{{ $f }}" x-model="form.health.fitness" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                            <option value="">Select</option>
                            <option value="excellent">Excellent</option>
                            <option value="good">Good</option>
                            <option value="average">Average</option>
                            <option value="poor">Poor</option>
                        </select>
                        @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Step 5: Documents --}}
            <div x-show="step === 5" x-cloak>
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Document Upload</h2>
                <div class="space-y-3">
                    @php
                        $stepDocTypes = ['birth_certificate' => 'Birth Certificate', 'certificate' => 'Educational Certificate', 'national_id' => 'National ID', 'photograph' => 'Passport Photograph'];
                        $stepUploadedTypes = $existing?->documents()->whereIn('document_type', array_keys($stepDocTypes))->pluck('document_type')->toArray() ?? [];
                        $stepMissing = array_diff(array_keys($stepDocTypes), $stepUploadedTypes);
                    @endphp
                    @foreach($stepDocTypes as $type => $label)
                    <div class="flex items-center justify-between py-2 px-3 rounded-lg {{ in_array($type, $stepUploadedTypes) ? 'bg-green-50' : 'bg-red-50' }}">
                        <span class="text-sm font-medium {{ in_array($type, $stepUploadedTypes) ? 'text-green-800' : 'text-red-800' }}">{{ $label }}</span>
                        @if(in_array($type, $stepUploadedTypes))
                            <span class="inline-flex items-center text-xs font-semibold text-green-700">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Uploaded
                            </span>
                        @else
                            <span class="inline-flex items-center text-xs font-semibold text-red-700">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                Missing
                            </span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @if(empty($stepMissing))
                <div class="mt-4 bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                    <p class="text-sm font-semibold text-green-800">All required documents uploaded</p>
                </div>
                @else
                <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-4 text-center">
                    <p class="text-sm text-amber-700">Upload missing documents via the <a href="{{ route('applicant.documents') }}" class="font-semibold underline">Documents page</a>.</p>
                </div>
                @endif
            </div>

            {{-- Step 6: Review --}}
            <div x-show="step === 6" x-cloak class="p-6 -mx-8" style="background:linear-gradient(180deg, #f0f7f0 0%, #ffffff 100%);">
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Review Your Application</h2>
                <div class="space-y-4 text-sm">
                    <h3 class="font-semibold text-gray-700">Personal Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-4 bg-gray-50 rounded-lg">
                        <p><span class="text-gray-500">Name:</span> <span>{{ $a->first_name }} {{ $a->last_name }}</span></p>
                        <p><span class="text-gray-500">Other Names:</span> <span>{{ $a->other_names ?: '—' }}</span></p>
                        <p><span class="text-gray-500">DOB:</span> <span>{{ $a->date_of_birth?->format('d M Y') }}</span></p>
                        <p><span class="text-gray-500">Gender:</span> <span>{{ $a->gender }}</span></p>
                        <p><span class="text-gray-500">Marital Status:</span> <span x-text="form.personal.marital_status"></span></p>
                        <p><span class="text-gray-500">Nationality:</span> <span x-text="form.personal.nationality"></span></p>
                        <p><span class="text-gray-500">National ID:</span> <span x-text="form.personal.national_id"></span></p>
                        <p><span class="text-gray-500">Phone:</span> <span>{{ $a->contact_number }}</span></p>
                        <p><span class="text-gray-500">Alt. Phone:</span> <span>{{ $a->alternative_contact ?: '—' }}</span></p>
                        <p><span class="text-gray-500">Email:</span> <span>{{ $a->email }}</span></p>
                        <p class="sm:col-span-2"><span class="text-gray-500">Address:</span> <span x-text="form.personal.residential_address"></span></p>
                        <p><span class="text-gray-500">Region:</span> <span x-text="form.personal.region"></span></p>
                        <p><span class="text-gray-500">District:</span> <span x-text="form.personal.district"></span></p>
                    </div>
                    <h3 class="font-semibold text-gray-700">Education</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-4 bg-gray-50 rounded-lg">
                        <p><span class="text-gray-500">Level:</span> <span x-text="form.education.level"></span></p>
                        <p><span class="text-gray-500">Institution:</span> <span x-text="form.education.institution"></span></p>
                        <p><span class="text-gray-500">Qualification Field:</span> <span x-text="form.education.degree_field"></span></p>
                        <p><span class="text-gray-500">Year:</span> <span x-text="form.education.year"></span></p>
                    </div>
                    <h3 class="font-semibold text-gray-700">Sector & Corps</h3>
                    <div class="grid grid-cols-2 gap-2 p-4 bg-gray-50 rounded-lg">
                        <p><span class="text-gray-500">Sector:</span> <span x-text="form.corps.selected_sector_id ? (sectorsData.find(s => s.id == form.corps.selected_sector_id)?.name || form.corps.selected_sector_id) : '—'"></span></p>
                        <p></p>
                        <p x-show="form.corps.corp_1"><span class="text-gray-500">1st Choice:</span> <span x-text="selectedCorpName1"></span></p>
                        <p x-show="form.corps.corp_2"><span class="text-gray-500">2nd Choice:</span> <span x-text="selectedCorpName2"></span></p>
                        <p x-show="form.corps.corp_3"><span class="text-gray-500">3rd Choice:</span> <span x-text="selectedCorpName3"></span></p>
                        <p x-show="!form.corps.corp_1 && !form.corps.corp_2 && !form.corps.corp_3"><span class="text-gray-400">No corps selected</span></p>
                    </div>
                    <h3 class="font-semibold text-gray-700">Health</h3>
                    <div class="grid grid-cols-2 gap-2 p-4 bg-gray-50 rounded-lg">
                        <p><span class="text-gray-500">Height:</span> <span x-text="form.health.height"></span></p>
                        <p><span class="text-gray-500">Weight:</span> <span x-text="form.health.weight"></span></p>
                        <p><span class="text-gray-500">Conditions:</span> <span x-text="form.health.conditions.join(', ') || 'None'"></span></p>
                        <p><span class="text-gray-500">Fitness:</span> <span x-text="form.health.fitness"></span></p>
                    </div>
                    <h3 class="font-semibold text-gray-700">Documents</h3>
                    <div class="grid grid-cols-2 gap-2 p-4 bg-gray-50 rounded-lg">
                        @php
                            $requiredDocTypes = ['birth_certificate' => 'Birth Certificate', 'certificate' => 'Educational Certificate', 'national_id' => 'National ID', 'photograph' => 'Passport Photograph'];
                            $uploadedDocTypes = $existing?->documents()->pluck('document_type')->toArray() ?? [];
                        @endphp
                        @foreach($requiredDocTypes as $type => $label)
                        <p>
                            <span class="text-gray-500">{{ $label }}:</span>
                            <span class="{{ in_array($type, $uploadedDocTypes) ? 'text-green-600' : 'text-red-500' }}">
                                {{ in_array($type, $uploadedDocTypes) ? 'Uploaded' : 'Missing' }}
                            </span>
                        </p>
                        @endforeach
                    </div>
                    <label class="flex items-center space-x-3 mt-4">
                        <input type="checkbox" x-model="agreed" class="rounded border-gray-300">
                        <span class="text-sm text-gray-600">I confirm that all the information provided is true and accurate to the best of my knowledge.</span>
                    </label>
                </div>
            </div>
            <div class="border-t border-gray-100 px-8 pb-8 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <button type="button" @click="prev()" class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Previous</button>
                    </div>
                    <div>
                        <button type="button" @click="saveDraft()" class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition" {{ $existing && $existing->status !== 'draft' ? 'disabled' : '' }}>Save Draft</button>
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" x-show="step < totalsteps" @click="next()" class="px-6 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Next</button>
                        <button type="button" x-show="step === totalsteps" @click="submitApp()" :disabled="!agreed || submitting" class="px-6 py-3 bg-gaf-red text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition disabled:opacity-50" :class="!agreed || submitting ? 'cursor-not-allowed' : ''" x-text="submitting ? 'Submitting...' : 'Submit Application'"></button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
