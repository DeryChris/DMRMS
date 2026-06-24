@extends('layouts.admin')

@section('title', 'Applications - Ghana Armed Forces')

@section('content')
<div x-data="{ selectAll: false, selected: [] }" class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Applications</h1>
    </div>

    <form method="GET" action="{{ route('admin.applications') }}">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex flex-wrap gap-3">
                <select name="status" class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
                    <option value="">All Status</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <select name="cycle_id" class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
                    <option value="">All Cycles</option>
                    @foreach($cycles as $c)
                        <option value="{{ $c->id }}" {{ request('cycle_id') == $c->id ? 'selected' : '' }}>{{ $c->name ?? $c->code }}</option>
                    @endforeach
                </select>
                <select name="region" class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
                    <option value="">All Regions</option>
                    @foreach($regions as $r)
                        <option value="{{ $r }}" {{ request('region') === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" placeholder="Search by name or GAF ID..." value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <button type="submit" class="px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Filter</button>
                <a href="{{ route('admin.applications') }}" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">Clear</a>
            </div>
        </div>
    </form>

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
                @forelse($applications as $app)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4"><input type="checkbox" class="rounded border-gray-300"></td>
                    <td class="px-6 py-4 font-medium">{{ $app->gaf_id ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $app->applicant->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $app->cycle->code ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        @php $map = ['registered' => ['Registered', 'bg-gray-100 text-gray-500'], 'draft' => ['Draft', 'bg-gray-100 text-gray-500'], 'submitted' => ['Submitted', 'bg-blue-50 text-blue-600'], 'eligibility_passed' => ['Eligible', 'bg-green-100 text-green-700'], 'eligibility_failed' => ['Ineligible', 'bg-red-100 text-red-700'], 'shortlisted' => ['Shortlisted', 'bg-amber-50 text-amber-600'], 'appointment_scheduled' => ['Appointment Set', 'bg-indigo-50 text-indigo-600'], 'screening_completed' => ['Screened', 'bg-emerald-50 text-emerald-600'], 'selected' => ['Selected', 'bg-green-100 text-green-700'], 'rejected' => ['Rejected', 'bg-red-100 text-red-700']]; @endphp
                        @php [$label, $classes] = $map[$app->status] ?? [ucfirst($app->status), 'bg-gray-100 text-gray-500']; @endphp
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $classes }}">{{ $label }}</span>
                    </td>
                    <td class="px-6 py-4">{{ $app->applicant->region ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $app->created_at->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.applications.detail', $app->id) }}" class="text-gaf-khaki hover:underline text-sm font-medium">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-sm font-medium">No applications found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing {{ $applications->firstItem() ?? 0 }} to {{ $applications->lastItem() ?? 0 }} of {{ $applications->total() }} entries</p>
        {{ $applications->links() }}
    </div>
</div>
@endsection
