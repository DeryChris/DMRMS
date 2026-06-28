@extends('layouts.screening')

@section('title', 'Medical Screening - Ghana Armed Forces')

@section('content')
<div x-data="{
    q: '',
    applicant: null,
    error: '',
    loading: false,
    search() {
        this.error = '';
        this.applicant = null;
        if (!this.q.trim()) return;
        this.loading = true;
        fetch('{{ route('screening.search-applicant') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ q: this.q })
        })
        .then(r => r.json())
        .then(data => {
            this.loading = false;
            if (data.error) { this.error = data.error; }
            else { this.applicant = data; }
        })
        .catch(() => { this.loading = false; this.error = 'Server error.'; });
    }
}" class="max-w-3xl mx-auto">
    <h1 class="font-heading font-bold text-2xl text-gray-800 mb-6 gradient-border pb-4">Medical Screening</h1>

    <div class="glass-strong rounded-xl shadow-sm p-6 mb-6 gradient-border-left">
        <div class="flex space-x-3">
            <input type="text" x-model="q" @keydown.enter="search()" placeholder="Search by GAF ID or name..." class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
            <button @click="search()" :disabled="loading" class="px-6 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition" :class="loading ? 'opacity-50' : ''">
                <span x-show="!loading">Search</span>
                <span x-show="loading">Searching...</span>
            </button>
        </div>
        <p x-show="error" x-text="error" class="text-red-600 text-sm mt-2"></p>
    </div>

    <div x-show="applicant" x-cloak x-transition>
        <template x-if="applicant">
            <div class="glass-strong rounded-xl shadow-sm p-8 gradient-border-left">
                <div class="flex items-center space-x-4 mb-6 p-4 -mx-8 -mt-8 mb-6 section-gradient-light text-white rounded-t-xl" style="background:linear-gradient(135deg, var(--gaf-green), var(--gaf-dark-green));">
                    <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center text-white font-heading font-bold text-xl" x-text="(applicant.name || '??').split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase()"></div>
                    <div>
                        <h2 class="font-heading font-semibold text-xl text-white" x-text="applicant.name"></h2>
                        <p class="text-sm text-white/70" x-text="applicant.gaf_id"></p>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full mt-1 inline-block"
                              :class="applicant.medical_status === 'fit' ? 'bg-green-100 text-green-700' : (applicant.medical_status === 'unfit' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500')"
                              x-text="'Medical: ' + (applicant.medical_status || 'pending')"></span>
                    </div>
                </div>

                <form method="POST" action="{{ route('screening.medical.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="application_id" x-model="applicant.application_id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Blood Pressure</label>
                            <input type="text" name="blood_pressure" placeholder="e.g. 120/80" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Heart Rate (bpm)</label>
                            <input type="number" name="heart_rate" min="30" max="250" placeholder="e.g. 72" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vision (Left)</label>
                            <input type="text" name="vision_left" placeholder="e.g. 6/6" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vision (Right)</label>
                            <input type="text" name="vision_right" placeholder="e.g. 6/6" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hearing Test</label>
                            <select name="hearing_test" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                                <option value="">Select...</option>
                                <option value="pass">Pass</option>
                                <option value="fail">Fail</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                            <input type="number" name="height_cm" step="0.1" min="100" max="250" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                            <input type="number" name="weight_kg" step="0.1" min="30" max="200" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">BMI</label>
                            <input type="number" name="bmi" step="0.1" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Medical Status</label>
                        <select name="medical_status" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                            <option value="">Select result...</option>
                            <option value="fit">Fit</option>
                            <option value="unfit">Unfit</option>
                            <option value="pending">Pending Further Review</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki" placeholder="Enter medical observations..."></textarea>
                    </div>

                    <button type="submit" class="px-8 py-3 text-white rounded-lg font-semibold transition" style="background:linear-gradient(135deg,#22c55e,#16a34a);">Submit Medical Results</button>
                </form>
            </div>
        </template>
    </div>
</div>
@endsection
