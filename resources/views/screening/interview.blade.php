@extends('layouts.screening')

@section('title', 'Interview - DMRMS')

@section('content')
<div x-data="{ search: '', applicant: null }" class="max-w-3xl mx-auto">
    <h1 class="font-heading font-bold text-2xl text-gray-800 mb-6">Interview Assessment</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex space-x-3">
            <input type="text" x-model="search" placeholder="Search by GAF ID or name..." class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
            <button @click="applicant = search" class="px-6 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Search</button>
        </div>
    </div>

    <div x-show="applicant" x-cloak class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="flex items-center space-x-4 mb-6">
            <div class="w-14 h-14 bg-gaf-green rounded-full flex items-center justify-center text-white font-heading font-bold text-xl">JD</div>
            <div><h2 class="font-heading font-semibold text-xl text-gray-800">John Doe</h2><p class="text-sm text-gray-500">GAF-2026001</p></div>
        </div>
        <form class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Communication (1-10)</label>
                    <select class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        @for($i = 1; $i <= 10; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Confidence (1-10)</label>
                    <select class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        @for($i = 1; $i <= 10; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Suitability (1-10)</label>
                    <select class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        @for($i = 1; $i <= 10; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Overall Recommendation</label>
                <select class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    <option>Recommended</option>
                    <option>Not Recommended</option>
                    <option>Further Review Required</option>
                </select>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Notes / Observations</label>
                <textarea rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki" placeholder="Enter interview notes..."></textarea>
            </div>
            <button type="submit" class="px-8 py-3 bg-gaf-green text-white rounded-lg font-semibold hover:bg-gaf-dark-green transition">Submit Interview Results</button>
        </form>
    </div>
</div>
@endsection
