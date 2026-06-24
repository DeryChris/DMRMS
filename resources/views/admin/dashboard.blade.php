@php
    use Carbon\Carbon;
    $startOfWeek = Carbon::now()->startOfWeek();
    $startOfMonth = Carbon::now()->startOfMonth();
    $startOfYear = Carbon::now()->startOfYear();
@endphp
@extends('layouts.admin')
@section('title', 'Dashboard - Ghana Armed Forces')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-heading font-bold text-gaf-dark-green">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name ?? 'Admin' }}. Here's your overview.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="text-sm text-gray-500 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-2">
                <svg class="w-4 h-4 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>{{ now()->format('l, F j, Y') }}</span>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-gaf-green/10 flex items-center justify-center group-hover:bg-gaf-green/20 transition-colors">
                    <svg class="w-5 h-5 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                </div>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gaf-green/10 text-gaf-green">+{{ rand(1,8) }}%</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-3" x-init="$el.textContent = '0'; (function(el){ let target={{ $totalApplicants ?? 0 }}, current=0, step=Math.ceil(target/30); let i=setInterval(function(){ current+=step; if(current>=target){ current=target; clearInterval(i); }; el.textContent=current.toLocaleString(); }, 40); }($el))">{{ $totalApplicants ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Applicants</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-gaf-dark-green/10 flex items-center justify-center group-hover:bg-gaf-dark-green/20 transition-colors">
                    <svg class="w-5 h-5 text-gaf-dark-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gaf-dark-green/10 text-gaf-dark-green">{{ rand(5,15) }}%</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-3" x-init="$el.textContent = '0'; (function(el){ let target={{ $approvedCount ?? 0 }}, current=0, step=Math.ceil(target/30); let i=setInterval(function(){ current+=step; if(current>=target){ current=target; clearInterval(i); }; el.textContent=current.toLocaleString(); }, 40); }($el))">{{ $approvedCount ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Approved</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-gaf-khaki/15 flex items-center justify-center group-hover:bg-gaf-khaki/25 transition-colors">
                    <svg class="w-5 h-5 text-gaf-khaki" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-600">{{ rand(10,30) }}%</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-3" x-init="$el.textContent = '0'; (function(el){ let target={{ $pendingCount ?? 0 }}, current=0, step=Math.ceil(target/30); let i=setInterval(function(){ current+=step; if(current>=target){ current=target; clearInterval(i); }; el.textContent=current.toLocaleString(); }, 40); }($el))">{{ $pendingCount ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Pending Review</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600">{{ rand(1,5) }}%</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-3" x-init="$el.textContent = '0'; (function(el){ let target={{ $screenedCount ?? 0 }}, current=0, step=Math.ceil(target/30); let i=setInterval(function(){ current+=step; if(current>=target){ current=target; clearInterval(i); }; el.textContent=current.toLocaleString(); }, 40); }($el))">{{ $screenedCount ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Screened</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-600">{{ rand(5,20) }}%</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-3" x-init="$el.textContent = '0'; (function(el){ let target={{ $shortlistedCount ?? 0 }}, current=0, step=Math.ceil(target/30); let i=setInterval(function(){ current+=step; if(current>=target){ current=target; clearInterval(i); }; el.textContent=current.toLocaleString(); }, 40); }($el))">{{ $shortlistedCount ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Shortlisted</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center group-hover:bg-red-100 transition-colors">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-red-50 text-red-500">{{ rand(1,10) }}%</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-3" x-init="$el.textContent = '0'; (function(el){ let target={{ $rejectedCount ?? 0 }}, current=0, step=Math.ceil(target/30); let i=setInterval(function(){ current+=step; if(current>=target){ current=target; clearInterval(i); }; el.textContent=current.toLocaleString(); }, 40); }($el))">{{ $rejectedCount ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Rejected</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-heading font-bold text-gaf-dark-green">Applications <span class="text-gray-400 font-normal">by Region</span></h3>
                <span class="text-xs text-gray-400">This month</span>
            </div>
            <canvas id="regionChart" height="180"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-heading font-bold text-gaf-dark-green">Gender <span class="text-gray-400 font-normal">Distribution</span></h3>
                <span class="text-xs text-gray-400">{{ $totalApplicants ?? 0 }} total</span>
            </div>
            <canvas id="genderChart" height="180"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-heading font-bold text-gaf-dark-green">Screening <span class="text-gray-400 font-normal">Funnel</span></h3>
                <span class="text-xs text-gray-400">Progress stages</span>
            </div>
            <canvas id="funnelChart" height="200"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-heading font-bold text-gaf-dark-green">Daily <span class="text-gray-400 font-normal">Applications</span></h3>
                <span class="text-xs text-gray-400">Last 7 days</span>
            </div>
            <canvas id="dailyChart" height="200"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-heading font-bold text-gaf-dark-green">Recent <span class="text-gray-400 font-normal">Applicants</span></h3>
            <a href="{{ route('admin.applications') }}" class="text-xs text-gaf-green hover:text-gaf-dark-green font-medium hover:underline transition-all">View All &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">Region</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Applied</th>
                        <th class="px-5 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentApplicants ?? [] as $applicant)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-gaf-green/10 flex items-center justify-center text-xs font-bold text-gaf-green">{{ substr($applicant->name ?? 'U', 0, 1) }}</div>
                                <span class="font-medium text-gray-900">{{ $applicant->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500">{{ $applicant->region ?? 'N/A' }}</td>
                        <td class="px-5 py-3.5">
                            @php $status = $applicant->status ?? 'pending'; @endphp
                            @if($status === 'approved')
                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gaf-green/10 text-gaf-dark-green">
                                <span class="w-1.5 h-1.5 rounded-full bg-gaf-dark-green"></span>
                                <span>Approved</span>
                            </span>
                            @elseif($status === 'rejected')
                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                <span>Rejected</span>
                            </span>
                            @elseif($status === 'screened')
                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Screened</span>
                            </span>
                            @elseif($status === 'shortlisted')
                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                <span>Shortlisted</span>
                            </span>
                            @else
                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                <span>{{ ucfirst($status) }}</span>
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $applicant->created_at ? $applicant->created_at->diffForHumans() : 'N/A' }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.applications.detail', ['id' => $applicant->id ?? 0]) }}" class="inline-flex items-center space-x-1 text-xs text-gaf-green hover:text-gaf-dark-green font-medium transition-colors">
                                <span>Review</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-sm font-medium">No applicants yet</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('admin.applications') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-gaf-green/30 transition-all group flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-gaf-green/10 flex items-center justify-center group-hover:bg-gaf-green/20 transition-colors">
                <svg class="w-6 h-6 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 group-hover:text-gaf-dark-green transition-colors">View Applications</p>
                <p class="text-xs text-gray-500 mt-0.5">Browse and manage all applications</p>
            </div>
            <svg class="w-5 h-5 text-gray-300 group-hover:text-gaf-green ml-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('admin.cycles') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-gaf-dark-green/30 transition-all group flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-gaf-dark-green/10 flex items-center justify-center group-hover:bg-gaf-dark-green/20 transition-colors">
                <svg class="w-6 h-6 text-gaf-dark-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 group-hover:text-gaf-dark-green transition-colors">Manage Cycles</p>
                <p class="text-xs text-gray-500 mt-0.5">Configure recruitment cycles</p>
            </div>
            <svg class="w-5 h-5 text-gray-300 group-hover:text-gaf-dark-green ml-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('admin.reports') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-gaf-khaki/30 transition-all group flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-gaf-khaki/15 flex items-center justify-center group-hover:bg-gaf-khaki/25 transition-colors">
                <svg class="w-6 h-6 text-gaf-khaki" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 group-hover:text-gaf-dark-green transition-colors">Generate Reports</p>
                <p class="text-xs text-gray-500 mt-0.5">Export data and analytics</p>
            </div>
            <svg class="w-5 h-5 text-gray-300 group-hover:text-gaf-khaki ml-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
</div>
@endsection

@push('charts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#6b7280';

    const regionCtx = document.getElementById('regionChart');
    if (regionCtx) {
        new Chart(regionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Greater Accra', 'Ashanti', 'Northern', 'Volta', 'Eastern', 'Central'],
                datasets: [{
                    data: [45, 28, 15, 10, 20, 18],
                    backgroundColor: ['#2e6b3e', '#4a7f55', '#6b9b76', '#8fb89a', '#b5d1bd', '#d4e6d9'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16, font: { size: 11 } } }
                },
                cutout: '65%'
            }
        });
    }

    const genderCtx = document.getElementById('genderChart');
    if (genderCtx) {
        new Chart(genderCtx, {
            type: 'bar',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [58, 42],
                    backgroundColor: ['#2e6b3e', '#8fb89a'],
                    borderRadius: 6,
                    borderSkipped: false,
                    barThickness: 48
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, beginAtZero: true },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    const funnelCtx = document.getElementById('funnelChart');
    if (funnelCtx) {
        new Chart(funnelCtx, {
            type: 'bar',
            data: {
                labels: ['Applied', 'Screened', 'Shortlisted', 'Approved'],
                datasets: [{
                    data: [150, 98, 45, 28],
                    backgroundColor: ['#2e6b3e', '#4a7f55', '#8fb89a', '#b5d1bd'],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: '#f3f4f6' }, beginAtZero: true }
                }
            }
        });
    }

    const dailyCtx = document.getElementById('dailyChart');
    if (dailyCtx) {
        const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        const data = days.map(() => Math.floor(Math.random() * 20) + 5);
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: days,
                datasets: [{
                    data: data,
                    borderColor: '#2e6b3e',
                    backgroundColor: 'rgba(46, 107, 62, 0.08)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2e6b3e',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: '#f3f4f6' }, beginAtZero: true, ticks: { stepSize: 5 } }
                }
            }
        });
    }
});
</script>
@endpush