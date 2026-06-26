@extends('layouts.admin')

@section('title', 'Scheduling - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gradient-border pb-4">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Scheduling</h1>
        <span class="text-sm text-gray-500 bg-white px-3 py-1.5 rounded-lg border">
            {{ $shortlistedCount }} shortlisted awaiting scheduling
        </span>
    </div>


    <div class="glass-strong rounded-xl shadow-sm p-6">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Create Screening Slots</h3>
        <form method="POST" action="{{ route('admin.scheduling.create-slots') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="scheduled_date" required min="{{ now()->format('Y-m-d') }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                    <input type="time" name="scheduled_time" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
                    <input type="text" name="venue" placeholder="e.g. Burma Camp" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                    <input type="number" name="capacity" min="1" max="500" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
            </div>
            <button type="submit" class="mt-4 px-6 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Create & Auto-Assign Slots</button>
        </form>
    </div>

    <div class="glass-strong rounded-xl shadow-sm p-6">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Calendar View</h3>
        <div class="grid grid-cols-7 gap-2">
            @php $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']; @endphp
            @foreach($days as $d)
                <div class="text-center text-xs font-medium text-gray-500 py-2">{{ $d }}</div>
            @endforeach
            @for($i = 1; $i <= now()->daysInMonth; $i++)
                @php $dateStr = now()->startOfMonth()->addDays($i - 1)->format('Y-m-d'); @endphp
                <div class="text-center py-3 rounded-lg text-sm {{ in_array($dateStr, $scheduledDates) ? 'bg-gradient-teal text-white font-semibold' : 'hover:bg-gray-100 border border-gray-200' }}" {{ in_array($dateStr, $scheduledDates) ? 'style=background:linear-gradient(135deg,#0D9488,#115E59);' : '' }}>
                    {{ $i }}
                    @if(in_array($dateStr, $scheduledDates))
                    <div class="w-1.5 h-1.5 bg-white rounded-full mx-auto mt-1"></div>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    <div class="glass-strong rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 pt-6 pb-2 flex items-center justify-between">
            <h3 class="font-heading font-semibold text-lg text-gray-800">Upcoming Appointments</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="table-header-gradient">
                    <th class="px-6 py-4 text-left text-white/90">Applicant</th>
                    <th class="px-6 py-4 text-left text-white/90">GAF ID</th>
                    <th class="px-6 py-4 text-left text-white/90">Date</th>
                    <th class="px-6 py-4 text-left text-white/90">Time</th>
                    <th class="px-6 py-4 text-left text-white/90">Venue</th>
                    <th class="px-6 py-4 text-left text-white/90">Slot</th>
                    <th class="px-6 py-4 text-left text-white/90">Status</th>
                    <th class="px-6 py-4 text-right text-white/90">Check-in</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($appointments as $apt)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $apt->application->applicant->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $apt->application->gaf_id ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $apt->scheduled_date?->format('Y-m-d') }}</td>
                    <td class="px-6 py-4">{{ $apt->scheduled_time }}</td>
                    <td class="px-6 py-4">{{ $apt->venue }}</td>
                    <td class="px-6 py-4">SLOT-{{ str_pad($apt->slot_number, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-6 py-4">
                        @php $sMap = config('recruitment.appointment_statuses'); @endphp
                        @php $label = $sMap[$apt->status]['label'] ?? ucfirst(str_replace('_', ' ', $apt->status)); $classes = $sMap[$apt->status]['color'] ?? 'bg-gray-100 text-gray-500'; @endphp
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $classes }}">{{ $label }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($apt->status === 'completed')
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-100 px-2 py-1 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Done
                            </span>
                        @elseif($apt->checked_in_at)
                            <span class="text-xs text-gray-500">{{ $apt->checked_in_at->format('H:i') }}</span>
                        @else
                            <span class="text-xs text-gray-400">--</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                        <p class="text-sm font-medium">No appointments scheduled</p>
                        <p class="text-xs mt-1">Create slots above to auto-assign shortlisted applicants.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
