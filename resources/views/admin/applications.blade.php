@extends('layouts.admin')

@section('title', 'Applications - Ghana Armed Forces')

@section('content')
<div x-data="{ selectAll: false, selected: [] }" class="space-y-6">
    <div class="flex items-center justify-between gradient-border pb-4">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Applications</h1>
        <form action="{{ route('admin.documents.bulk-verify-needs-review') }}" method="POST" onsubmit="return confirm('Verify ALL needs_review documents across all applications? This cannot be undone.')">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-amber-500 text-white hover:bg-amber-600 transition-colors shadow-sm" title="Auto-verify all documents currently marked as needs_review">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Verify All Needs Review
            </button>
        </form>
    </div>

    <form method="GET" action="{{ route('admin.applications') }}">
        <div class="glass-strong rounded-xl shadow-sm p-4">
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

    <div class="glass-strong rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="table-header-gradient">
                    <th class="px-6 py-4 text-left text-white"><input type="checkbox" @click="selectAll = !selectAll; selected = selectAll ? ['all'] : []" class="rounded border-gray-300"></th>
                    <th class="px-6 py-4 text-left text-white/90">GAF ID</th>
                    <th class="px-6 py-4 text-left text-white/90">Name</th>
                    <th class="px-6 py-4 text-left text-white/90">Cycle</th>
                    <th class="px-6 py-4 text-left text-white/90">Sector</th>
                    <th class="px-6 py-4 text-left text-white/90">Status</th>
                    <th class="px-6 py-4 text-left text-white/90">Region</th>
                    <th class="px-6 py-4 text-left text-white/90">Date</th>
                    <th class="px-6 py-4 text-right text-white/90">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($applications as $app)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4"><input type="checkbox" class="rounded border-gray-300"></td>
                    <td class="px-6 py-4 font-medium">{{ $app->gaf_id ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $app->applicant->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $app->cycle->code ?? 'N/A' }}</td>
                    <td class="px-6 py-4"><span class="text-xs">{{ $app->selectedSector?->name ?? '—' }}</span></td>
                    <td class="px-6 py-4">
                        @php $map = config('recruitment.statuses'); @endphp
                        {!! status_badge($app->status) !!}
                    </td>
                    <td class="px-6 py-4">{{ $app->applicant->region ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ $app->created_at->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.applications.detail', $app->id) }}" class="relative group p-1.5 rounded-lg hover:bg-gaf-khaki/10 text-gaf-khaki transition-colors inline-flex items-center" title="View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">View</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-sm font-medium">No applications found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing {{ $applications->firstItem() ?? 0 }} to {{ $applications->lastItem() ?? 0 }} of {{ $applications->total() }} entries</p>
        {{ $applications->links() }}
    </div>
</div>
@endsection
