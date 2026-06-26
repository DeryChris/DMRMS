@php
    use Carbon\Carbon;
@endphp
@extends('layouts.admin')
@section('title', 'KPIs - Ghana Armed Forces')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-heading font-bold text-gaf-dark-green">Key Performance Indicators</h1>
            <p class="text-sm text-gray-500 mt-1">Data-driven recruitment analytics</p>
        </div>
        <form method="GET" action="{{ route('admin.kpi') }}" class="flex items-center space-x-2">
            <select name="cycle_id" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                <option value="">All Cycles</option>
                @foreach($cycles as $c)
                <option value="{{ $c->id }}" {{ $cycleId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-gaf-green/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-3">{{ number_format($totalApplicants) }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Applicants</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-3">{{ number_format($eligibleCount) }}</p>
            <p class="text-xs text-gray-500 mt-1">Eligible Applicants</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-3">{{ number_format($rejectedCount) }}</p>
            <p class="text-xs text-gray-500 mt-1">Rejected Applicants</p>
            @if($rejectionBreakdown->isNotEmpty())
            <div class="mt-2 pt-2 border-t border-gray-100 space-y-1">
                @foreach($rejectionBreakdown as $status => $count)
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                    <span class="font-medium">{{ $count }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-3">{{ $regionStats->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Regions Represented</p>
            <div class="mt-2 pt-2 border-t border-gray-100">
                <div class="flex justify-between text-xs text-gray-500">
                    <span>Top: {{ $regionStats->first()?->region ?? 'N/A' }}</span>
                    <span class="font-medium">{{ $regionStats->first()?->total ?? 0 }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-3">{{ number_format($maleCount) }} / {{ number_format($femaleCount) }}</p>
            <p class="text-xs text-gray-500 mt-1">Gender Distribution (M/F)</p>
            @if($totalApplicants > 0)
            <div class="mt-2 pt-2 border-t border-gray-100">
                <div class="flex justify-between text-xs text-gray-500">
                    <span>Ratio</span>
                    <span class="font-medium">{{ $maleCount > 0 ? round($femaleCount / $maleCount * 100, 1) : 0 }}% F</span>
                </div>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="flex items-end space-x-2 mt-3">
                <p class="text-3xl font-bold text-gray-900">{{ $successRate }}%</p>
                <span class="text-xs {{ $successRate >= 10 ? 'text-green-600' : 'text-red-500' }} mb-1">{{ $successRate >= 10 ? '↑' : '↓' }}</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Success Rate (Selected / Submitted)</p>
            <div class="mt-2 pt-2 border-t border-gray-100">
                <div class="flex justify-between text-xs text-gray-500">
                    <span>{{ number_format($selectedCount + $recruitedCount) }} selected of {{ number_format($submittedCount) }} submitted</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-heading font-bold text-gaf-dark-green">Stage Funnel</h3>
                <span class="text-xs text-gray-400">Application pipeline</span>
            </div>
            <canvas id="funnelChart" height="200"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-heading font-bold text-gaf-dark-green">Regional Distribution</h3>
                <span class="text-xs text-gray-400">{{ $regionStats->count() }} regions</span>
            </div>
            <canvas id="regionChart" height="200"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-heading font-bold text-gaf-dark-green">Cycle Comparison</h3>
            <span class="text-xs text-gray-400">All cycles</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-5 py-3 font-medium">Cycle</th>
                        <th class="px-5 py-3 font-medium text-right">Total</th>
                        <th class="px-5 py-3 font-medium text-right">Eligible</th>
                        <th class="px-5 py-3 font-medium text-right">Selected</th>
                        <th class="px-5 py-3 font-medium text-right">Rejected</th>
                        <th class="px-5 py-3 font-medium text-right">Success Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($cycleComparison as $cc)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-3.5 font-medium text-gray-900">{{ $cc['name'] ?? 'N/A' }}</td>
                        <td class="px-5 py-3.5 text-right">{{ number_format($cc['total']) }}</td>
                        <td class="px-5 py-3.5 text-right">{{ number_format($cc['eligible']) }}</td>
                        <td class="px-5 py-3.5 text-right">{{ number_format($cc['selected']) }}</td>
                        <td class="px-5 py-3.5 text-right">{{ number_format($cc['rejected']) }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <span class="font-semibold {{ $cc['success_rate'] >= 10 ? 'text-green-600' : 'text-red-500' }}">{{ $cc['success_rate'] }}%</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                            <p class="text-sm font-medium">No cycles yet</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('charts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#6b7280';

    const funnelCtx = document.getElementById('funnelChart');
    if (funnelCtx) {
        new Chart(funnelCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($stageFunnel->keys()->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))->values()) !!},
                datasets: [{
                    data: {!! json_encode($stageFunnel->values()) !!},
                    backgroundColor: ['#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#c084fc', '#d8b4fe', '#e9d5ff', '#f0abfc', '#f472b6', '#fb923c', '#22c55e'],
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, beginAtZero: true },
                    y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });
    }

    const regionCtx = document.getElementById('regionChart');
    if (regionCtx) {
        new Chart(regionCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($regionStats->pluck('region')) !!},
                datasets: [{
                    data: {!! json_encode($regionStats->pluck('total')) !!},
                    backgroundColor: ['#6366f1', '#06b6d4', '#f43f5e', '#22c55e', '#f97316', '#a855f7', '#ec4899', '#eab308', '#14b8a6', '#3b82f6', '#d946ef', '#84cc16', '#f59e0b', '#0ea5e9', '#8b5cf6', '#10b981'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12, font: { size: 10 } } }
                },
                cutout: '60%'
            }
        });
    }
});
</script>
@endpush
