@extends('layouts.applicant')

@section('title', 'Application Form - Ghana Armed Forces')

@section('content')
@php
    $existing = $application;
    $defaultCycle = $cycles->first()?->id ?? optional($applicant->voucher)->cycle_id;
@endphp
<div x-data="{
    step: 1,
    totalsteps: 4,
    steps: [
        { label: 'Education', key: 'education' },
        { label: 'Health', key: 'health' },
        { label: 'Documents', key: 'documents' },
        { label: 'Review', key: 'review' }
    ],
    form: {
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
    next() { if(this.step < this.totalsteps) this.step++; },
    prev() { if(this.step > 1) this.step--; },
    saveDraft() {
        let form = document.getElementById('application-form');
        form.querySelector('input[name=action]').value = 'save';
        form.submit();
    },
    submitApp() {
        this.submitting = true;
        let form = document.getElementById('application-form');
        form.querySelector('input[name=action]').value = 'submit';
        form.submit();
    }
}" class="max-w-4xl mx-auto">
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">{{ session('success') }}</div>
    @endif
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
                        <li>Ensure documents are clear and legible</li>
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

        <div class="step-bar-wrapper" style="background:#f7f9fa;padding:10px 0;border-bottom:1px solid #cbd5e1;margin-bottom:24px;">
            <div style="display:flex;align-items:center;gap:0;">
                <template x-for="(s, i) in steps" :key="i">
                    <div style="display:flex;align-items:center;gap:8px;flex:1;">
                        <div :class="['step-num', step >= i + 1 ? 'active' : '']" x-text="i + 1"></div>
                        <span :class="['step-label', step >= i + 1 ? 'active' : '']" x-text="s.label"></span>
                        <div x-show="i < steps.length - 1" class="step-connector"></div>
                    </div>
                </template>
            </div>
        </div>

        <div class="card !rounded-none">
            <div class="form-card-header">
                <h2 x-text="'Step ' + step + ' of ' + totalsteps + ' — ' + steps[step - 1].label"></h2>
            </div>
            <div class="p-8">

            <div x-show="step === 1" x-cloak>
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

            <div x-show="step === 2" x-cloak>
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Physical & Health</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                        <input type="number" step="0.01" name="height" x-model="form.health.height" placeholder="e.g. 175" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                        <input type="number" step="0.1" name="weight" x-model="form.health.weight" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Health Conditions</label>
                        <div class="grid grid-cols-2 gap-2">
                            @php $conditions = ['Asthma', 'Diabetes', 'Hypertension', 'Epilepsy', 'Hearing Loss', 'Vision Impairment']; @endphp
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

            <div x-show="step === 3" x-cloak>
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Document Upload</h2>
                <p class="text-sm text-gray-500 mb-4">Upload your documents via the <a href="{{ route('applicant.documents') }}" class="text-gaf-green font-semibold underline">Documents page</a> after saving this application.</p>
            </div>

            <div x-show="step === 4" x-cloak>
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Review Your Application</h2>
                <div class="space-y-4 text-sm">
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
                    <label class="flex items-center space-x-3 mt-4">
                        <input type="checkbox" x-model="agreed" class="rounded border-gray-300">
                        <span class="text-sm text-gray-600">I confirm that all the information provided is true and accurate to the best of my knowledge.</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mt-6">
            <button type="button" @click="saveDraft()" class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition" {{ $existing && $existing->status !== 'draft' ? 'disabled' : '' }}>Save Draft</button>
            <div class="flex space-x-3">
                <button type="button" x-show="step > 1" @click="prev()" class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Previous</button>
                <button type="button" x-show="step < totalsteps" @click="next()" class="px-6 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Next</button>
                <button type="button" x-show="step === totalsteps" @click="submitApp()" :disabled="!agreed || submitting" class="px-6 py-3 bg-gaf-red text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition disabled:opacity-50" :class="!agreed || submitting ? 'cursor-not-allowed' : ''" x-text="submitting ? 'Submitting...' : 'Submit Application'"></button>
            </div>
        </div>
    </form>
</div>
@endsection
