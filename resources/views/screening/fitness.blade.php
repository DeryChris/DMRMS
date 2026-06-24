@extends('layouts.screening')

@section('title', 'Fitness Test - DMRMS')

@section('content')
<div x-data="{ search: '', applicant: null }" class="max-w-3xl mx-auto">
    <h1 class="font-heading font-bold text-2xl text-gray-800 mb-6">Fitness Test</h1>

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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Run Time (minutes)</label><input type="number" step="0.1" placeholder="e.g. 12.5" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Push-ups (count)</label><input type="number" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Sit-ups (count)</label><input type="number" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Pull-ups (count)</label><input type="number" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Overall Score</label>
                <div class="flex space-x-3">
                    <input type="number" placeholder="0-100" class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    <select class="w-40 border border-gray-300 rounded-lg px-4 py-3 text-sm"><option>Pass</option><option>Fail</option></select>
                </div>
            </div>
            <button type="submit" class="px-8 py-3 bg-gaf-green text-white rounded-lg font-semibold hover:bg-gaf-dark-green transition">Submit Fitness Results</button>
        </form>
    </div>
</div>
@endsection
