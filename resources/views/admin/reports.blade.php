@extends('layouts.admin')

@section('title', 'Reports - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <h1 class="font-heading font-bold text-2xl text-gray-800 gradient-border pb-4">Reports</h1>

    <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Report Builder</h3>
        <form method="POST" action="{{ route('admin.reports.export') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Cycle</label>
                    <select name="cycle_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm">
                        <option value="">All Cycles</option>
                        @foreach($cycles as $c)
                            <option value="{{ $c->id }}">{{ $c->name ?? $c->cycle_code ?? $c->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label><input type="date" name="start_date" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">End Date</label><input type="date" name="end_date" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Format</label>
                    <select name="format" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm">
                        <option value="pdf">PDF</option>
                        <option value="csv">Excel</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="mt-4 px-6 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Generate Report</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-xl shadow-sm p-6 text-center hover:shadow-md transition cursor-pointer text-white" style="background:linear-gradient(135deg, #14532d, #166534);">
            <svg class="w-10 h-10 mx-auto text-white/80 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <h4 class="font-heading font-semibold text-white">Regional Distribution</h4>
            <p class="text-xs text-white/70 mt-1">{{ $regionStats->count() }} regions tracked</p>
        </div>
        <div class="rounded-xl shadow-sm p-6 text-center hover:shadow-md transition cursor-pointer text-white" style="background:linear-gradient(135deg, #D4AF37, #b8942f);">
            <svg class="w-10 h-10 mx-auto text-white/80 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <h4 class="font-heading font-semibold text-white">Gender Distribution</h4>
            <p class="text-xs text-white/70 mt-1">{{ $maleCount }} Male / {{ $femaleCount }} Female</p>
        </div>
        <div class="rounded-xl shadow-sm p-6 text-center hover:shadow-md transition cursor-pointer text-white" style="background:linear-gradient(135deg, #0D9488, #115E59);">
            <svg class="w-10 h-10 mx-auto text-white/80 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <h4 class="font-heading font-semibold text-white">Stage Analysis</h4>
            <p class="text-xs text-white/70 mt-1">{{ $stageStats->sum() }} total applications</p>
        </div>
    </div>

    <div class="glass-strong rounded-xl shadow-sm p-6">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Application Stages Breakdown</h3>
        <div class="space-y-3">
            @forelse($stageStats as $status => $count)
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 rounded-full bg-gaf-green"></div>
                    <p class="text-sm font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $status) }}</p>
                </div>
                <span class="text-sm font-semibold text-gray-900">{{ number_format($count) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-500 text-center py-4">No applications yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
