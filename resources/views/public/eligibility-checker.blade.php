@extends('layouts.app')

@section('title', 'Eligibility Checker - Ghana Armed Forces')

@php $unsplashPhoto = $unsplashPhoto ?? unsplash_hero(); @endphp

@section('hero')
<div class="relative overflow-hidden" style="min-height:200px;">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $unsplashPhoto['regular_url'] ?? '' }}');"></div>
    <div class="absolute inset-0" style="background:linear-gradient(135deg, rgba(20,83,45,0.9) 0%, rgba(15,47,31,0.85) 70%, rgba(155,34,38,0.75) 100%);"></div>
    <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center">
        <h1 class="font-heading font-bold text-3xl text-white mb-2">Eligibility Pre-Checker</h1>
        <p class="text-gaf-khaki/80">Check if you meet the basic requirements before applying.</p>
    </div>
    @if($unsplashPhoto && ($unsplashPhoto['attribution']['name'] ?? '') !== 'Unsplash')
    <div class="absolute bottom-2 right-4 z-20 text-xs text-white/40">
        Photo by <a href="{{ ($unsplashPhoto['attribution']['link'] ?? '#') }}?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">{{ $unsplashPhoto['attribution']['name'] ?? 'Unknown' }}</a> on <a href="https://unsplash.com/?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">Unsplash</a>
    </div>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
    <div x-data="{
        dob: '',
        nationality: '',
        gender: '',
        height: '',
        education: '',
        marital: '',
        showResults: false,
        get age() {
            if(!this.dob) return 0;
            let gd = new Date(this.dob);
            let diff = Date.now() - gd.getTime();
            return Math.floor(diff / (1000 * 60 * 60 * 24 * 365.25));
        },
        get agePass() { return this.age >= 18 && this.age <= 35; },
        get nationalityPass() { return this.nationality === 'ghanaian'; },
        get genderPass() { return this.gender === 'male' || this.gender === 'female'; },
        get heightPass() { let h = parseFloat(this.height); return this.gender === 'male' ? h >= 1.68 : h >= 1.60; },
        get educationPass() { return this.education === 'ssce' || this.education === 'tertiary'; },
        get overallPass() { return this.agePass && this.nationalityPass && this.genderPass && this.heightPass && this.educationPass; },
        check() { this.showResults = true; }
    }" class="bg-white/90 glass-strong rounded-xl shadow-lg p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                <input type="date" x-model="dob" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                <p class="text-xs text-gray-400 mt-1" x-show="dob">Age: <span x-text="age"></span> years</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
                <select x-model="nationality" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    <option value="">Select...</option>
                    <option value="ghanaian">Ghanaian</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                <select x-model="gender" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    <option value="">Select...</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Height (m)</label>
                <input type="number" step="0.01" x-model="height" placeholder="e.g. 1.75" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Education Level</label>
                <select x-model="education" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    <option value="">Select...</option>
                    <option value="below_ssce">Below SSCE/WASSCE</option>
                    <option value="ssce">SSCE/WASSCE</option>
                    <option value="tertiary">Tertiary</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Marital Status</label>
                <select x-model="marital" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    <option value="">Select...</option>
                    <option value="single">Single</option>
                    <option value="married">Married</option>
                    <option value="divorced">Divorced</option>
                    <option value="widowed">Widowed</option>
                </select>
            </div>
        </div>
        <button @click="check" class="w-full mt-6 bg-gaf-red text-white py-3 rounded-lg font-semibold text-lg hover:bg-red-700 transition">Check eligibility</button>

        <div x-show="showResults" x-cloak class="mt-8 space-y-3">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Results</h3>
            <div class="flex items-center justify-between p-3 rounded-lg" :class="agePass ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <span class="text-sm">Age (18-35)</span>
                <span :class="agePass ? 'text-green-600' : 'text-red-600'" class="font-semibold text-sm" x-text="agePass ? 'PASS' : 'FAIL'"></span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-lg" :class="nationalityPass ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <span class="text-sm">Ghanaian Nationality</span>
                <span :class="nationalityPass ? 'text-green-600' : 'text-red-600'" class="font-semibold text-sm" x-text="nationalityPass ? 'PASS' : 'FAIL'"></span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-lg" :class="genderPass ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <span class="text-sm">Gender</span>
                <span :class="genderPass ? 'text-green-600' : 'text-red-600'" class="font-semibold text-sm" x-text="genderPass ? 'PASS' : 'FAIL'"></span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-lg" :class="heightPass ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <span class="text-sm">Height Requirement</span>
                <span :class="heightPass ? 'text-green-600' : 'text-red-600'" class="font-semibold text-sm" x-text="heightPass ? 'PASS' : 'FAIL'"></span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-lg" :class="educationPass ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <span class="text-sm">Education Level</span>
                <span :class="educationPass ? 'text-green-600' : 'text-red-600'" class="font-semibold text-sm" x-text="educationPass ? 'PASS' : 'FAIL'"></span>
            </div>
            <div class="mt-6 p-6 rounded-lg text-center text-white" :class="overallPass ? 'bg-gradient-success' : 'bg-gradient-red'">
                <p class="font-heading font-bold text-xl" x-text="overallPass ? 'You are eligible to apply!' : 'You do not meet the requirements.'"></p>
                <p class="text-xs text-gray-500 mt-2">This is a preliminary check only. Official eligibility is determined after application submission.</p>
            </div>
        </div>
    </div>
</div>
@endsection
