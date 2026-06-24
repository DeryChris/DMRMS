@extends('layouts.screening')

@section('title', 'Screening Dashboard - DMRMS')

@section('today-count', '24')
@section('checked-in-count', '12')
@section('pending-count', '10')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="font-heading font-semibold text-lg text-gray-800 mb-4">Quick Verify</h2>
        <div class="flex space-x-3">
            <input type="text" placeholder="Enter verification code or GAF ID" class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
            <button class="px-6 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Verify</button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="font-heading font-semibold text-lg text-gray-800">Today's Schedule</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr><th class="px-6 py-3 text-left font-medium text-gray-700">Slot</th><th class="px-6 py-3 text-left font-medium text-gray-700">Applicant</th><th class="px-6 py-3 text-left font-medium text-gray-700">GAF ID</th><th class="px-6 py-3 text-left font-medium text-gray-700">Time</th><th class="px-6 py-3 text-left font-medium text-gray-700">Status</th><th class="px-6 py-3 text-right font-medium text-gray-700">Action</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @for($i = 1; $i <= 8; $i++)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3">SLOT-{{ str_pad(400 + $i, 4, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-6 py-3 font-medium">Applicant {{ $i }}</td>
                    <td class="px-6 py-3">GAF-{{ str_pad(2026000 + $i, 7, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-6 py-3">{{ ['8:00','8:30','9:00','9:30','10:00','10:30','11:00','11:30'][$i - 1] }} AM</td>
                    <td class="px-6 py-3">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ ['bg-green-100 text-green-700','bg-yellow-100 text-yellow-700','bg-gray-100 text-gray-700','bg-red-100 text-red-700'][$i % 4] }}">
                            {{ ['Checked In','Pending','Scheduled','Missed'][$i % 4] }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-right">
                        <a href="{{ route('screening.verify') }}" class="text-gaf-khaki text-sm font-medium hover:underline">Verify</a>
                    </td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>
@endsection
