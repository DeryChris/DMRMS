@extends('layouts.admin')

@section('title', 'Applications - Ghana Armed Forces')

@section('content')
<div x-data="{ selectAll: false, selected: [] }" class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Applications</h1>
        <button class="px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Export</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
            <select class="border border-gray-300 rounded-lg px-4 py-2 text-sm"><option>All Status</option><option>Pending</option><option>Eligible</option><option>Shortlisted</option><option>Screened</option><option>Selected</option><option>Rejected</option></select>
            <select class="border border-gray-300 rounded-lg px-4 py-2 text-sm"><option>All Cycles</option><option>2026/01</option><option>2025/02</option></select>
            <select class="border border-gray-300 rounded-lg px-4 py-2 text-sm"><option>All Regions</option><option>Greater Accra</option><option>Ashanti</option><option>Eastern</option><option>Western</option><option>Central</option><option>Northern</option></select>
            <input type="date" class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
            <input type="date" class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" placeholder="Search by name or GAF ID..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left"><input type="checkbox" @click="selectAll = !selectAll; selected = selectAll ? ['all'] : []" class="rounded border-gray-300"></th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">GAF ID</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Name</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Cycle</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Status</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Region</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Date</th>
                    <th class="px-6 py-4 text-right font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @for($i = 1; $i <= 15; $i++)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4"><input type="checkbox" class="rounded border-gray-300"></td>
                    <td class="px-6 py-4 font-medium">GAF-{{ str_pad(2026000 + $i, 7, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-6 py-4">Applicant {{ $i }}</td>
                    <td class="px-6 py-4">2026/01</td>
                    <td class="px-6 py-4"><span class="text-xs font-semibold px-2 py-1 rounded-full {{ ['bg-yellow-100 text-yellow-700','bg-green-100 text-green-700','bg-blue-100 text-blue-700','bg-purple-100 text-purple-700','bg-red-100 text-red-700'][$i % 5] }}">{{ ['Pending','Eligible','Shortlisted','Screened','Rejected'][$i % 5] }}</span></td>
                    <td class="px-6 py-4">{{ ['Greater Accra','Ashanti','Eastern','Western','Central','Northern','Volta','Bono','Upper East','Upper West'][$i % 10] }}</td>
                    <td class="px-6 py-4">2026-06-{{ str_pad(1 + $i, 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.application-detail', ['id' => $i]) }}" class="text-gaf-khaki hover:underline text-sm font-medium">View</a>
                    </td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing 1 to 15 of 1,542 entries</p>
        <div class="flex space-x-2">
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Prev</button>
            <button class="px-3 py-2 bg-gaf-green text-white rounded-lg text-sm">1</button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">2</button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">3</button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Next</button>
        </div>
    </div>
</div>
@endsection
