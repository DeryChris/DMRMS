@extends('layouts.admin')

@section('title', 'Reports - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <h1 class="font-heading font-bold text-2xl text-gray-800">Reports</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Report Builder</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Cycle</label>
                <select class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm">
                    <option value="">All Cycles</option>
                    @foreach($cycles as $c)
                        <option value="{{ $c->id }}">{{ $c->name ?? $c->cycle_code ?? $c->code }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label><input type="date" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">End Date</label><input type="date" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Format</label><select class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm"><option>PDF</option><option>Excel</option></select></div>
        </div>
        <button class="mt-4 px-6 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Generate Report</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center hover:shadow-md transition cursor-pointer">
            <svg class="w-10 h-10 mx-auto text-gaf-green mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <h4 class="font-heading font-semibold text-gray-800">Regional Distribution</h4>
            <p class="text-xs text-gray-500 mt-1">Download PDF</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center hover:shadow-md transition cursor-pointer">
            <svg class="w-10 h-10 mx-auto text-gaf-green mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <h4 class="font-heading font-semibold text-gray-800">Gender Distribution</h4>
            <p class="text-xs text-gray-500 mt-1">Download PDF</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center hover:shadow-md transition cursor-pointer">
            <svg class="w-10 h-10 mx-auto text-gaf-khaki mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <h4 class="font-heading font-semibold text-gray-800">Stage Analysis</h4>
            <p class="text-xs text-gray-500 mt-1">Download PDF</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Generated Reports</h3>
        <div class="space-y-3">
            @for($i = 1; $i <= 5; $i++)
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <div><p class="text-sm font-medium text-gray-800">Regional Report - 2026/01</p><p class="text-xs text-gray-500">Generated June {{ 10 + $i }}, 2026</p></div>
                </div>
                <a href="#" class="text-gaf-khaki text-sm font-medium hover:underline">Download</a>
            </div>
            @endfor
        </div>
    </div>
</div>
@endsection
