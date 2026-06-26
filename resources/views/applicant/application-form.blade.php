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
    totalsteps: 5,
    autoSaveStatus: '',
    saveTimer: null,
    steps: [
        { label: 'Personal', key: 'personal' },
        { label: 'Education', key: 'education' },
        { label: 'Health', key: 'health' },
        { label: 'Documents', key: 'documents' },
        { label: 'Review', key: 'review' }
    ],
    form: {
        personal: {
            other_names: '{{ old('other_names', $a->other_names ?? '') }}',
            marital_status: '{{ old('marital_status', $a->marital_status ?? '') }}',
            nationality: '{{ old('nationality', $a->nationality ?? 'Ghanaian') }}',
            national_id: '{{ old('national_id', $a->national_id ?? '') }}',
            residential_address: '{{ old('residential_address', $a->residential_address ?? '') }}',
            region: '{{ old('region', $a->region ?? '') }}',
            district: '{{ old('district', $a->district ?? '') }}',
            alternative_contact: '{{ old('alternative_contact', $a->alternative_contact ?? '') }}'
        },
        education: {
            institution: '{{ old('institution_name', $existing?->institution_name ?? '') }}',
            qualification: '{{ old('qualification', $existing?->qualification ?? '') }}',
            year: '{{ old('year_obtained', $existing?->year_obtained ?? '') }}',
            cert_number: '{{ old('certificate_number', $existing?->certificate_number ?? '') }}',
            level: '{{ old('education_level', $existing?->education_level ?? '') }}'
        },
        health: {
            height: '{{ old('height', $existing?->height ?? '') }}',
            weight: '{{ old('weight', $existing?->weight ?? '') }}',
            conditions: {{ json_encode(old('health_conditions', $existing?->health_conditions ?? [])) }},
            criminal: '{{ old('criminal_record', $existing?->criminal_record !== null ? ($existing->criminal_record ? 'yes' : 'no') : '') }}',
            fitness: '{{ old('fitness_status', $existing?->fitness_status ?? '') }}'
        }
    },
    agreed: false,
    submitting: false,

    init() {
        this.$watch('form', () => this.triggerAutoSave(), { deep: true });
    },

    triggerAutoSave() {
        if (this.saveTimer) clearTimeout(this.saveTimer);
        this.autoSaveStatus = 'unsaved';
        this.saveTimer = setTimeout(() => this.doAutoSave(), 3000);
    },

    async doAutoSave() {
        this.autoSaveStatus = 'saving';
        const formEl = document.getElementById('application-form');
        const fd = new FormData(formEl);
        fd.set('action', 'save');
        fd.set('current_step', this.step);
        try {
            const res = await fetch(formEl.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('Save failed');
            this.autoSaveStatus = 'saved';
            setTimeout(() => { if (this.autoSaveStatus === 'saved') this.autoSaveStatus = ''; }, 3000);
        } catch (e) {
            this.autoSaveStatus = 'error';
            setTimeout(() => { if (this.autoSaveStatus === 'error') this.autoSaveStatus = ''; }, 5000);
        }
    },

    next() {
        if (this.step < this.totalsteps) {
            let form = document.getElementById('application-form');
            form.querySelector('input[name=action]').value = 'save';
            form.querySelector('input[name=current_step]').value = this.step + 1;
            form.submit();
        }
    },
    prev() { if(this.step > 1) this.step--; },
    saveDraft() {
        let form = document.getElementById('application-form');
        form.querySelector('input[name=action]').value = 'save';
        form.querySelector('input[name=current_step]').value = this.step;
        form.submit();
    },
    submitApp() {
        this.submitting = true;
        let form = document.getElementById('application-form');
        form.querySelector('input[name=action]').value = 'submit';
        form.submit();
    }
}" class="max-w-5xl mx-auto px-4">
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

        <div class="step-bar-wrapper glass-strong rounded-xl p-4 mb-6" style="background:rgba(255,255,255,0.9);backdrop-filter:blur(8px);">
            <div style="display:flex;align-items:center;gap:0;">
                <template x-for="(s, i) in steps" :key="i">
                    <div style="display:flex;align-items:center;gap:8px;flex:1;">
                        <div :class="['step-num', step > i + 1 ? 'completed' : '', step === i + 1 ? 'active' : '']" x-text="i + 1" :style="step > i + 1 ? 'background:var(--gaf-khaki);color:#fff;' : (step === i + 1 ? 'background:linear-gradient(135deg, var(--gaf-green), var(--gaf-dark-green));color:#fff;' : '')"></div>
                        <span :class="['step-label', step >= i + 1 ? 'active' : '']" x-text="s.label" :style="step > i + 1 ? 'color:var(--gaf-khaki);' : ''"></span>
                        <div x-show="i < steps.length - 1" class="step-connector" :style="step > i + 1 ? 'background:var(--gaf-khaki);' : ''"></div>
                    </div>
                </template>
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Other Names <span class="text-gray-400">(optional)</span></label>
                        <input type="text" name="other_names" x-model="form.personal.other_names" placeholder="Middle names if any" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Marital Status *</label>
                        <select name="marital_status" x-model="form.personal.marital_status" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                            <option value="">Select</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nationality *</label>
                        <input type="text" name="nationality" x-model="form.personal.nationality" placeholder="Ghanaian" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">National ID / Passport *</label>
                        <input type="text" name="national_id" x-model="form.personal.national_id" placeholder="GHA-XXXXXXXXX-X" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Residential Address *</label>
                        <textarea name="residential_address" x-model="form.personal.residential_address" rows="2" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki" placeholder="Street, City, Landmark"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Region *</label>
                        <select name="region" x-model="form.personal.region" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                            <option value="">Select Region</option>
                            @foreach($regions as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">District *</label>
                        <input type="text" name="district" x-model="form.personal.district" placeholder="Your district" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alternative Phone <span class="text-gray-400">(optional)</span></label>
                        <input type="tel" name="alternative_contact" x-model="form.personal.alternative_contact" placeholder="0244000001" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                </div>
            </div>

            {{-- Step 2: Education Information --}}
            <div x-show="step === 2" x-cloak class="p-6 -mx-8" style="background:linear-gradient(180deg, #f0f7f0 0%, #ffffff 100%);">
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Educational Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Institution Name</label>
                        <input type="text" name="institution_name" x-model="form.education.institution" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Qualification</label>
                        <input type="text" name="qualification" x-model="form.education.qualification" placeholder="e.g. WASSCE, BSc" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Year of Completion</label>
                        <input type="number" name="year_obtained" x-model="form.education.year" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Certificate Number</label>
                        <input type="text" name="certificate_number" x-model="form.education.cert_number" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Education Level *</label>
                        <select name="education_level" x-model="form.education.level" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                            <option value="">Select</option>
                            <option value="ssce">SSCE/WASSCE</option>
                            <option value="diploma">Diploma</option>
                            <option value="degree">Bachelor's Degree</option>
                            <option value="masters">Master's Degree</option>
                            <option value="phd">PhD</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 3: Health --}}
            <div x-show="step === 3" x-cloak class="p-6 -mx-8" style="background:linear-gradient(180deg, #ffffff 0%, #f0f7f0 100%);">
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Physical & Health</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Height (m)</label>
                        <input type="number" step="0.01" name="height" x-model="form.health.height" placeholder="e.g. 1.75" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                        <input type="number" step="0.1" name="weight" x-model="form.health.weight" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Criminal Record Declaration</label>
                        <select name="criminal_record" x-model="form.health.criminal" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                            <option value="">Select</option>
                            <option value="no">No criminal record</option>
                            <option value="yes">I have a criminal record</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fitness Self-Assessment</label>
                        <select name="fitness_status" x-model="form.health.fitness" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                            <option value="">Select</option>
                            <option value="excellent">Excellent</option>
                            <option value="good">Good</option>
                            <option value="average">Average</option>
                            <option value="poor">Poor</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 4: Documents --}}
            <div x-show="step === 4" x-cloak>
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Document Upload</h2>
                <p class="text-sm text-gray-500 mb-4">Upload your documents via the <a href="{{ route('applicant.documents') }}" class="text-gaf-green font-semibold underline">Documents page</a> after saving this application.</p>
            </div>

            {{-- Step 5: Review --}}
            <div x-show="step === 5" x-cloak class="p-6 -mx-8" style="background:linear-gradient(180deg, #f0f7f0 0%, #ffffff 100%);">
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Review Your Application</h2>
                <div class="space-y-4 text-sm">
                    <h3 class="font-semibold text-gray-700">Personal Information</h3>
                    <div class="grid grid-cols-2 gap-2 p-4 bg-gray-50 rounded-lg">
                        <p><span class="text-gray-500">Name:</span> <span>{{ $a->first_name }} {{ $a->last_name }}</span></p>
                        <p><span class="text-gray-500">Other Names:</span> <span x-text="form.personal.other_names || '—'"></span></p>
                        <p><span class="text-gray-500">DOB:</span> <span>{{ $a->date_of_birth?->format('d M Y') }}</span></p>
                        <p><span class="text-gray-500">Gender:</span> <span>{{ $a->gender }}</span></p>
                        <p><span class="text-gray-500">Marital Status:</span> <span x-text="form.personal.marital_status"></span></p>
                        <p><span class="text-gray-500">Nationality:</span> <span x-text="form.personal.nationality"></span></p>
                        <p><span class="text-gray-500">National ID:</span> <span x-text="form.personal.national_id"></span></p>
                        <p><span class="text-gray-500">Phone:</span> <span>{{ $a->contact_number }}</span></p>
                        <p><span class="text-gray-500">Alt. Phone:</span> <span x-text="form.personal.alternative_contact || '—'"></span></p>
                        <p><span class="text-gray-500">Email:</span> <span>{{ $a->email }}</span></p>
                        <p class="md:col-span-2"><span class="text-gray-500">Address:</span> <span x-text="form.personal.residential_address"></span></p>
                        <p><span class="text-gray-500">Region:</span> <span x-text="form.personal.region"></span></p>
                        <p><span class="text-gray-500">District:</span> <span x-text="form.personal.district"></span></p>
                    </div>
                    <h3 class="font-semibold text-gray-700">Education</h3>
                    <div class="grid grid-cols-2 gap-2 p-4 bg-gray-50 rounded-lg">
                        <p><span class="text-gray-500">Level:</span> <span x-text="form.education.level"></span></p>
                        <p><span class="text-gray-500">Institution:</span> <span x-text="form.education.institution"></span></p>
                        <p><span class="text-gray-500">Qualification:</span> <span x-text="form.education.qualification"></span></p>
                        <p><span class="text-gray-500">Year:</span> <span x-text="form.education.year"></span></p>
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
        </div>

        <div class="flex items-center justify-between mt-6">
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
    </form>
</div>
@endsection
