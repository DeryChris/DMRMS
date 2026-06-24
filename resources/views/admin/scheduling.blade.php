@extends('layouts.admin')

@section('title', 'Scheduling - DMRMS')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Scheduling</h1>
        <div class="flex space-x-3">
            <button class="px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">+ Create Slot</button>
            <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">Manual Allocation</button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Create Screening Slot</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Date</label><input type="date" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Time</label><input type="time" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Venue</label><input type="text" placeholder="e.g. Burma Camp" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label><input type="number" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
        </div>
        <button class="mt-4 px-6 py-2 bg-gaf-red text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">Create Slot</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Calendar View</h3>
        <div class="grid grid-cols-7 gap-2">
            @php $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']; @endphp
            @foreach($days as $d)
                <div class="text-center text-xs font-medium text-gray-500 py-2">{{ $d }}</div>
            @endforeach
            @for($i = 1; $i <= 30; $i++)
                <div class="text-center py-3 rounded-lg text-sm {{ $i === 15 ? 'bg-gaf-red text-white font-semibold' : 'hover:bg-gray-100 border border-gray-200' }}">
                    {{ $i }}
                    @if(in_array($i, [15,16,17,22,23,24]))
                    <div class="w-1.5 h-1.5 bg-gaf-green rounded-full mx-auto mt-1"></div>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <h3 class="font-heading font-semibold text-lg text-gray-800 px-6 pt-6 pb-2">Upcoming Appointments</h3>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr><th class="px-6 py-4 text-left font-medium text-gray-700">Applicant</th><th class="px-6 py-4 text-left font-medium text-gray-700">Date</th><th class="px-6 py-4 text-left font-medium text-gray-700">Time</th><th class="px-6 py-4 text-left font-medium text-gray-700">Venue</th><th class="px-6 py-4 text-left font-medium text-gray-700">Slot</th><th class="px-6 py-4 text-left font-medium text-gray-700">Status</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @for($i = 1; $i <= 8; $i++)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">Applicant {{ $i }}</td>
                    <td class="px-6 py-4">2026-07-{{ str_pad(10 + $i, 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-6 py-4">{{ ['8:00 AM','9:00 AM','10:00 AM','11:00 AM','1:00 PM','2:00 PM','3:00 PM','8:00 AM'][$i - 1] }}</td>
                    <td class="px-6 py-4">Burma Camp</td>
                    <td class="px-6 py-4">SLOT-{{ str_pad(400 + $i, 4, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-6 py-4"><span class="text-xs font-semibold px-2 py-1 rounded-full {{ ['bg-blue-100 text-blue-700','bg-green-100 text-green-700','bg-yellow-100 text-yellow-700','bg-gray-100 text-gray-700'][$i % 4] }}">{{ ['Scheduled','Confirmed','Pending','Attended'][$i % 4] }}</span></td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>
@endsection
