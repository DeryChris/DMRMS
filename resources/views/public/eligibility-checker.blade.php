@extends('layouts.app')

@section('title', 'eligibility Checker - DMRMS')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-10">
        <h1 class="font-heading font-bold text-3xl text-gaf-green mb-2">eligibility pre-Checker</h1>
        <p class="text-gray-600">Check if you meet the basic requirements before applying.</p>
    </div>

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
    }" class="bg-white rounded-xl shadow-lg p-8">
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
            <div class="mt-6 p-4 rounded-lg text-center" :class="overallPass ? 'bg-green-100 border-2 border-green-400' : 'bg-red-100 border-2 border-red-400'">
                <p class="font-heading font-bold text-xl" :class="overallPass ? 'text-green-800' : 'text-red-800'" x-text="overallPass ? 'You are eligible to apply!' : 'You do not meet the requirements.'"></p>
                <p class="text-xs text-gray-500 mt-2">This is a preliminary check only. Official eligibility is determined after application submission.</p>
            </div>
        </div>
    </div>
</div>
@endsection
