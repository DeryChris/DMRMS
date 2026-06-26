@extends('layouts.screening')

@section('title', 'Verify Applicant - Ghana Armed Forces')

@section('content')
<div x-data="{
    verified: false,
    code: '{{ old('code', session('verified_code', '')) }}',
    applicant: null,
    error: '',
    loading: false,
    verifyCode() {
        this.error = '';
        if (!this.code.trim()) { this.error = 'Please enter a verification code.'; return; }
        this.loading = true;
        fetch('/screening/verify-entry', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ code: this.code })
        })
        .then(r => r.json())
        .then(data => {
            this.loading = false;
            if (data.error) { this.error = data.error; this.verified = false; }
            else { this.verified = true; this.applicant = data; }
        })
        .catch(() => { this.loading = false; this.error = 'Server error. Try again.'; });
    }
}" class="max-w-2xl mx-auto">
    <h1 class="font-heading font-bold text-2xl text-gray-800 mb-6 gradient-border pb-4">Verify Applicant</h1>

    <div class="glass-strong rounded-xl shadow-sm p-8 text-center">
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Enter Verification Code</label>
            <input type="text" x-model="code" @keydown.enter="verifyCode()" placeholder="e.g. GAF-2026-8472" class="w-full max-w-md border border-gray-300 rounded-lg px-4 py-4 text-lg text-center focus:ring-2 focus:ring-gaf-khaki">
            <p x-show="error" x-text="error" class="text-red-600 text-sm mt-2"></p>
        </div>
        <div class="flex justify-center space-x-4 mb-8">
            <button @click="verifyCode()" :disabled="loading" class="px-8 py-3 bg-gaf-green text-white rounded-lg font-semibold hover:bg-gaf-dark-green transition" :class="loading ? 'opacity-50' : ''">
                <span x-show="!loading">Verify</span>
                <span x-show="loading">Verifying...</span>
            </button>
        </div>
    </div>

    <div x-show="verified" x-cloak x-transition class="mt-6 glass-strong rounded-xl shadow-sm p-8 gradient-border-left">
        <template x-if="applicant">
            <div>
                <div class="flex items-center space-x-6">
                    <div class="w-20 h-20 bg-gaf-khaki rounded-full flex items-center justify-center text-white font-heading font-bold text-2xl" x-text="(applicant.name || '??').split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()"></div>
                    <div>
                        <h2 class="font-heading font-bold text-xl text-gray-800" x-text="applicant.name"></h2>
                        <p class="text-gray-500" x-text="applicant.gaf_id || 'No GAF ID'"></p>
                        <p class="text-xs text-gray-400" x-text="applicant.contact_number || ''"></p>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full mt-2 inline-block"
                              :class="applicant.status === 'shortlisted' || applicant.status === 'appointment_scheduled' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                              x-text="(applicant.status || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                    </div>
                </div>
                <div class="mt-6 text-center">
                    <form method="POST" action="{{ route('screening.checkin') }}">
                        @csrf
                        <input type="hidden" name="application_id" x-model="applicant.application_id">
                        <button type="submit" class="px-8 py-3 bg-green-600 text-white rounded-lg font-semibold text-lg hover:bg-green-700 transition">Confirm Entry &amp; Check In</button>
                    </form>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection
