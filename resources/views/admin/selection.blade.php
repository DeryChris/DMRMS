@extends('layouts.admin')

@section('title', 'Selection - DMRMS')

@section('content')
<div class="space-y-6">
    <h1 class="font-heading font-bold text-2xl text-gray-800">Selection Committee Review</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left font-medium text-gray-700"><input type="checkbox" class="rounded border-gray-300"></th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Applicant</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">GAF ID</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Eligibility</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Medical</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Fitness</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Interview</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Score</th>
                    <th class="px-6 py-4 text-right font-medium text-gray-700">Decision</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @for($i = 1; $i <= 12; $i++)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4"><input type="checkbox" class="rounded border-gray-300"></td>
                    <td class="px-6 py-4 font-medium">Applicant {{ $i }}</td>
                    <td class="px-6 py-4">GAF-{{ str_pad(2026000 + $i, 7, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-6 py-4"><span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-700">Eligible</span></td>
                    <td class="px-6 py-4"><span class="text-xs font-semibold px-2 py-1 rounded-full {{ $i % 3 === 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">{{ $i % 3 === 0 ? 'Unfit' : 'Fit' }}</span></td>
                    <td class="px-6 py-4"><span class="text-xs font-semibold px-2 py-1 rounded-full {{ $i % 4 === 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">{{ $i % 4 === 0 ? 'Fail' : 'Pass' }}</span></td>
                    <td class="px-6 py-4"><span class="text-xs font-semibold px-2 py-1 rounded-full {{ $i % 5 === 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">{{ $i % 5 === 0 ? 'Not Rec.' : 'Recommended' }}</span></td>
                    <td class="px-6 py-4 font-semibold">{{ rand(65, 98) }}%</td>
                    <td class="px-6 py-4 text-right space-x-1">
                        <button class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700">Admit</button>
                        <button class="text-xs bg-yellow-600 text-white px-3 py-1.5 rounded-lg hover:bg-yellow-700">Defer</button>
                        <button class="text-xs bg-red-600 text-white px-3 py-1.5 rounded-lg hover:bg-red-700">Reject</button>
                    </td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-heading font-semibold text-lg text-gray-800">Batch Selection</h3>
            <div class="flex space-x-3">
                <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">Admit Selected</button>
                <button class="px-4 py-2 bg-yellow-600 text-white rounded-lg text-sm font-medium hover:bg-yellow-700 transition">Defer Selected</button>
                <button class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">Reject Selected</button>
            </div>
        </div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Reason / Memo</label><textarea rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm"></textarea></div>
    </div>
</div>
@endsection
