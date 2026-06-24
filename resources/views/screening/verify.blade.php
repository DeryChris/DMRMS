@extends('layouts.screening')

@section('title', 'Verify Applicant - Ghana Armed Forces')

@section('content')
<div x-data="{ verified: false, code: '' }" class="max-w-2xl mx-auto">
    <h1 class="font-heading font-bold text-2xl text-gray-800 mb-6">Verify Applicant</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Enter Verification Code or Scan QR</label>
            <input type="text" x-model="code" placeholder="e.g. GAF-2026-8472" class="w-full max-w-md border border-gray-300 rounded-lg px-4 py-4 text-lg text-center focus:ring-2 focus:ring-gaf-khaki">
        </div>
        <div class="flex justify-center space-x-4 mb-8">
            <button @click="verified = code.length > 0" class="px-8 py-3 bg-gaf-green text-white rounded-lg font-semibold hover:bg-gaf-dark-green transition">Verify</button>
            <button class="px-8 py-3 border-2 border-dashed border-gray-300 text-gray-500 rounded-lg font-medium hover:border-gaf-khaki hover:text-gaf-khaki transition">
                <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                Scan QR Code
            </button>
        </div>

        <div class="bg-gray-100 rounded-lg h-48 flex items-center justify-center mb-6">
            <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
        </div>
    </div>

    <div x-show="verified" x-cloak x-transition class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="flex items-center space-x-6">
            <div class="w-20 h-20 bg-gaf-khaki rounded-full flex items-center justify-center text-white font-heading font-bold text-2xl">JD</div>
            <div>
                <h2 class="font-heading font-bold text-xl text-gray-800">John Doe</h2>
                <p class="text-gray-500">GAF-2026001</p>
                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-700 mt-2 inline-block">Eligible</span>
            </div>
        </div>
        <div class="mt-6 text-center">
            <button class="px-8 py-3 bg-green-600 text-white rounded-lg font-semibold text-lg hover:bg-green-700 transition">Confirm Entry</button>
        </div>
    </div>
</div>
@endsection
