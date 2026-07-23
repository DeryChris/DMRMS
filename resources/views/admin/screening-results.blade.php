@extends('layouts.admin')

@section('title', 'Screening Results - Ghana Armed Forces')

@section('content')
<div x-data="screeningForm()" class="space-y-6">
    <h1 class="font-heading font-bold text-2xl text-gray-800 gradient-border pb-4 mb-6">Screening</h1>

    <div x-show="!success" x-cloak>
        {{-- Step Indicator --}}
        <div class="flex items-center justify-between mb-8 px-2">
            <div class="flex items-center flex-1">
                <template x-for="(s, i) in steps" :key="i">
                    <div class="flex items-center">
                        <div class="flex items-center space-x-2">
                            <div :class="currentStep > i ? 'bg-green-600 text-white' : (currentStep === i ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-500')" class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors">
                                <span x-show="currentStep > i">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span x-show="currentStep <= i" x-text="i + 1"></span>
                            </div>
                            <span class="text-xs font-medium" :class="currentStep === i ? 'text-gaf-green' : 'text-gray-400'" x-text="s"></span>
                        </div>
                        <template x-if="i < steps.length - 1">
                            <div class="w-12 h-0.5 mx-2" :class="currentStep > i ? 'bg-green-600' : 'bg-gray-200'"></div>
                        </template>
                    </div>
                </template>
            </div>
            <div class="text-xs whitespace-nowrap">
                <span x-show="autoSaveStatus === 'saving'" class="text-amber-600"><svg class="inline w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Saving...</span>
                <span x-show="autoSaveStatus === 'saved'" class="text-green-600"><svg class="inline w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Saved</span>
                <span x-show="autoSaveStatus === 'unsaved' && currentStep > 0 && currentStep < 4" class="text-amber-600">Unsaved</span>
            </div>
        </div>

        {{-- Error / Success messages --}}
        <div x-show="error" x-text="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-sm mb-4" x-cloak></div>
        <div x-show="stepMsg" x-text="stepMsg" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg text-sm mb-4" x-cloak></div>

        {{-- STEP 1: Verify Code --}}
        <div x-show="currentStep === 0" x-cloak class="glass-strong rounded-xl shadow-sm p-8 gradient-border-left">
            <h2 class="font-heading font-semibold text-lg text-gray-800 mb-4">Step 1: Verify Applicant</h2>
            <p class="text-sm text-gray-500 mb-6">Enter the applicant's verification code to look up their information.</p>
            <div class="flex space-x-3 max-w-xl">
                <input type="text" x-model="code" @keydown.enter="verifyCode" placeholder="Enter verification code..." class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki uppercase">
                <button @click="verifyCode" :disabled="saving || !code.trim()" class="px-6 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition" :class="(saving || !code.trim()) ? 'opacity-50' : ''">
                    <span x-show="!saving">Verify</span>
                    <span x-show="saving">Verifying...</span>
                </button>
            </div>

            <template x-if="applicant">
                <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 bg-green-600 rounded-full flex items-center justify-center text-white font-heading font-bold text-xl" x-text="(applicant.name || '??').split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase()"></div>
                            <div>
                                <h3 class="font-heading font-semibold text-lg text-gray-800" x-text="applicant.name"></h3>
                                <p class="text-sm text-gray-500" x-text="'GAF ID: ' + (applicant.gaf_id || 'N/A')"></p>
                                <p class="text-sm text-gray-500" x-show="applicant.contact_number" x-text="'Contact: ' + applicant.contact_number"></p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded-full">VERIFIED</span>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button @click="currentStep = 1" class="px-6 py-2 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Proceed to Medical Screening</button>
                    </div>
                </div>
            </template>
        </div>

        {{-- STEP 2: Medical Examination --}}
        <div x-show="currentStep === 1" x-cloak class="glass-strong rounded-xl shadow-sm p-8 gradient-border-left">
            <div class="flex items-center space-x-4 mb-6 p-4 -mx-8 -mt-8 mb-6 text-white rounded-t-xl" style="background:linear-gradient(135deg, var(--gaf-green), var(--gaf-dark-green));">
                <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center text-white font-heading font-bold text-xl" x-text="(applicant.name || '??').split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase()"></div>
                <div>
                    <h2 class="font-heading font-semibold text-xl text-white" x-text="applicant.name"></h2>
                    <p class="text-sm text-white/70" x-text="applicant.gaf_id"></p>
                </div>
            </div>

            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Medical Examination</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Blood Pressure</label>
                    <input type="text" x-model="form.medical.blood_pressure" placeholder="e.g. 120/80" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Heart Rate (bpm)</label>
                    <input type="number" x-model="form.medical.heart_rate" @input="form.medical.heart_rate = $event.target.value.replace(/\D/g, '')" min="30" max="250" placeholder="e.g. 72" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vision (Left)</label>
                    <input type="text" x-model="form.medical.vision_left" placeholder="e.g. 6/6" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vision (Right)</label>
                    <input type="text" x-model="form.medical.vision_right" placeholder="e.g. 6/6" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hearing Test</label>
                    <select x-model="form.medical.hearing_test" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        <option value="">Select...</option>
                        <option value="pass">Pass</option>
                        <option value="fail">Fail</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Height (m)</label>
                    <input type="number" x-model="form.medical.height_cm" @input="form.medical.height_cm = $event.target.value.replace(/[^0-9.]/g, '')" step="0.01" min="0.5" max="2.5" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                    <input type="number" x-model="form.medical.weight_kg" @input="form.medical.weight_kg = $event.target.value.replace(/[^0-9.]/g, '')" step="0.1" min="30" max="200" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">BMI</label>
                    <input type="number" x-model="form.medical.bmi" @input="form.medical.bmi = $event.target.value.replace(/[^0-9.]/g, '')" step="0.1" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Medical Status</label>
                <select x-model="form.medical.medical_status" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    <option value="">Select result...</option>
                    <option value="fit">Fit</option>
                    <option value="unfit">Unfit</option>
                    <option value="pending">Pending Further Review</option>
                </select>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea x-model="form.medical.notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki" placeholder="Enter medical observations..."></textarea>
            </div>
            <div class="mt-6 flex justify-between">
                <button @click="currentStep = 0" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition">Back</button>
                <button @click="saveMedical" :disabled="saving" class="px-8 py-2 text-white rounded-lg font-semibold transition" :class="saving ? 'opacity-50' : ''" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                    <span x-show="!saving">Save &amp; Continue</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
        </div>

        {{-- STEP 3: Fitness Assessment --}}
        <div x-show="currentStep === 2" x-cloak class="glass-strong rounded-xl shadow-sm p-8 gradient-border-left">
            <div class="flex items-center space-x-4 mb-6 p-4 -mx-8 -mt-8 mb-6 text-white rounded-t-xl" style="background:linear-gradient(135deg, var(--gaf-green), var(--gaf-dark-green));">
                <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center text-white font-heading font-bold text-xl" x-text="(applicant.name || '??').split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase()"></div>
                <div>
                    <h2 class="font-heading font-semibold text-xl text-white" x-text="applicant.name"></h2>
                    <p class="text-sm text-white/70" x-text="applicant.gaf_id"></p>
                </div>
            </div>

            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Fitness Assessment</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Run Time (seconds)</label>
                    <input type="number" x-model="form.fitness.run_time_seconds" @input="form.fitness.run_time_seconds = $event.target.value.replace(/\D/g, '')" min="0" placeholder="e.g. 750" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Push-ups (count)</label>
                    <input type="number" x-model="form.fitness.push_ups" @input="form.fitness.push_ups = $event.target.value.replace(/\D/g, '')" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sit-ups (count)</label>
                    <input type="number" x-model="form.fitness.sit_ups" @input="form.fitness.sit_ups = $event.target.value.replace(/\D/g, '')" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pull-ups (count)</label>
                    <input type="number" x-model="form.fitness.pull_ups" @input="form.fitness.pull_ups = $event.target.value.replace(/\D/g, '')" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shuttle Run (seconds)</label>
                    <input type="number" x-model="form.fitness.shuttle_run" @input="form.fitness.shuttle_run = $event.target.value.replace(/[^0-9.]/g, '')" step="0.1" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
                    <select x-model="form.fitness.fitness_grade" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        <option value="">--</option>
                        <option value="a">A (Excellent)</option>
                        <option value="b">B (Good)</option>
                        <option value="c">C (Average)</option>
                        <option value="d">D (Below Average)</option>
                        <option value="f">F (Fail)</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Overall Score (0-100)</label>
                <input type="number" x-model="form.fitness.fitness_score" @input="form.fitness.fitness_score = $event.target.value.replace(/\D/g, '')" required min="0" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea x-model="form.fitness.notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki" placeholder="Enter fitness observations..."></textarea>
            </div>
            <div class="mt-6 flex justify-between">
                <button @click="currentStep = 1" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition">Back</button>
                <button @click="saveFitness" :disabled="saving" class="px-8 py-2 text-white rounded-lg font-semibold transition" :class="saving ? 'opacity-50' : ''" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                    <span x-show="!saving">Save &amp; Continue</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
        </div>

        {{-- STEP 4: Interview Assessment --}}
        <div x-show="currentStep === 3" x-cloak class="glass-strong rounded-xl shadow-sm p-8 gradient-border-left">
            <div class="flex items-center space-x-4 mb-6 p-4 -mx-8 -mt-8 mb-6 text-white rounded-t-xl" style="background:linear-gradient(135deg, var(--gaf-green), var(--gaf-dark-green));">
                <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center text-white font-heading font-bold text-xl" x-text="(applicant.name || '??').split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase()"></div>
                <div>
                    <h2 class="font-heading font-semibold text-xl text-white" x-text="applicant.name"></h2>
                    <p class="text-sm text-white/70" x-text="applicant.gaf_id"></p>
                </div>
            </div>

            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Interview Assessment</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Communication (1-10)</label>
                    <select x-model="form.interview.communication" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        @for($i = 1; $i <= 10; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confidence (1-10)</label>
                    <select x-model="form.interview.confidence" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        @for($i = 1; $i <= 10; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Appearance (1-10)</label>
                    <select x-model="form.interview.appearance" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        @for($i = 1; $i <= 10; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Knowledge (1-10)</label>
                    <select x-model="form.interview.knowledge" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        @for($i = 1; $i <= 10; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attitude (1-10)</label>
                    <select x-model="form.interview.attitude" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        @for($i = 1; $i <= 10; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Overall Score (0-100)</label>
                    <input type="number" x-model="form.interview.interview_score" @input="form.interview.interview_score = $event.target.value.replace(/\D/g, '')" required min="0" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Decision</label>
                <select x-model="form.interview.interview_decision" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    <option value="">Select decision...</option>
                    <option value="pass">PASS - Recommended</option>
                    <option value="fail">FAIL - Not Recommended</option>
                </select>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes / Observations</label>
                <textarea x-model="form.interview.notes" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki" placeholder="Enter interview notes..."></textarea>
            </div>
            <div class="mt-6 flex justify-between">
                <button @click="currentStep = 2" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition">Back</button>
                <button @click="saveInterview" :disabled="saving" class="px-8 py-2 text-white rounded-lg font-semibold transition" :class="saving ? 'opacity-50' : ''" style="background:linear-gradient(135deg,#22c55e,#16a34a);">
                    <span x-show="!saving">Submit &amp; Review</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
        </div>

        {{-- STEP 5: Review & Submit --}}
        <div x-show="currentStep === 4" x-cloak class="glass-strong rounded-xl shadow-sm p-8 gradient-border-left">
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto bg-green-600 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h2 class="font-heading font-semibold text-xl text-gray-800 mt-4">Screening Complete</h2>
                <p class="text-sm text-gray-500 mt-1" x-text="applicant ? applicant.name + ' (' + applicant.gaf_id + ')' : ''"></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-green-700 uppercase tracking-wide">Medical</p>
                    <p class="font-heading font-bold text-lg mt-1" x-text="form.medical.medical_status ? form.medical.medical_status.toUpperCase() : 'PENDING'"></p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-blue-700 uppercase tracking-wide">Fitness</p>
                    <p class="font-heading font-bold text-lg mt-1" x-text="form.fitness.fitness_score ? form.fitness.fitness_score + ' pts' : 'PENDING'"></p>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-purple-700 uppercase tracking-wide">Interview</p>
                    <p class="font-heading font-bold text-lg mt-1" x-text="form.interview.interview_decision ? form.interview.interview_decision.toUpperCase() : 'PENDING'"></p>
                </div>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600 mb-4">Results have been saved and the application status has been updated.</p>
                <a href="{{ route('admin.screening-results') }}" class="inline-flex items-center space-x-2 bg-gaf-green text-white px-6 py-3 rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Start New Screening</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Success state after completing all steps --}}
    <div x-show="success" x-cloak class="glass-strong rounded-xl shadow-sm p-8 gradient-border-left text-center">
        <div class="w-20 h-20 mx-auto bg-green-600 rounded-full flex items-center justify-center mb-4">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h2 class="font-heading font-bold text-2xl text-gray-800 mb-2">Screening Results Recorded</h2>
        <p class="text-gray-500 mb-6" x-text="'Applicant: ' + (applicant ? applicant.name : '') + ' — Status: ' + (finalStatus || 'N/A')"></p>
        <a href="{{ route('admin.screening-results') }}" class="inline-flex items-center space-x-2 bg-gaf-green text-white px-6 py-3 rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Start New Screening</span>
        </a>
    </div>

    {{-- Results Table --}}
    <div class="glass-strong rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-heading font-semibold text-lg text-gray-800">Past Screening Results</h3>
            <span class="text-xs text-gray-500">{{ $results->count() }} records</span>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="table-header-gradient">
                    <th class="px-6 py-4 text-left text-white/90">Applicant</th>
                    <th class="px-6 py-4 text-left text-white/90">GAF ID</th>
                    <th class="px-6 py-4 text-left text-white/90">Medical</th>
                    <th class="px-6 py-4 text-left text-white/90">Fitness</th>
                    <th class="px-6 py-4 text-left text-white/90">Interview</th>
                    <th class="px-6 py-4 text-left text-white/90">Overall</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($results as $r)
                <tr class="hover:bg-gray-50" style="border-left:3px solid {{ optional($r)->overall_status === 'pass' ? '#22c55e' : (optional($r)->overall_status === 'fail' ? '#ef4444' : '#D4AF37') }};">
                    <td class="px-6 py-4 font-medium text-left">{{ $r->application->applicant->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-left">{{ $r->application->gaf_id ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-left">{!! status_badge($r->medical_result ?? 'pending', 'screening') !!}</td>
                    <td class="px-6 py-4 text-left">{!! status_badge($r->fitness_result ?? 'pending', 'screening') !!}</td>
                    <td class="px-6 py-4 text-left">{!! status_badge($r->interview_result ?? 'pending', 'screening') !!}</td>
                    <td class="px-6 py-4 text-left">{!! status_badge($r->overall_status ?? 'pending', 'screening') !!}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400"><p class="text-sm font-medium">No screening results yet</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection