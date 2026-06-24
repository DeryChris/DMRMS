@extends('layouts.admin')

@section('title', 'Admin Dashboard - DMRMS')

@section('content')
<div x-data="{
    kpis: { total: 0, eligible: 0, shortlisted: 0, screened: 0, selected: 0, ai: 0 },
    init() {
        let targets = { total: 15420, eligible: 8200, shortlisted: 3200, screened: 1800, selected: 750, ai: 4230 };
        let duration = 2000, steps = 60, interval = duration / steps, step = 0;
        let timer = setInterval(() => {
            step++;
            for(let key in this.kpis) this.kpis[key] = Math.round((targets[key] / steps) * step);
            if(step >= steps) clearInterval(timer);
        }, interval);
    }
}" class="space-y-6">
    <h1 class="font-heading font-bold text-2xl text-gray-800">Dashboard</h1>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4"><p class="text-xs text-gray-500 uppercase">Total Applicants</p><p class="text-2xl font-heading font-bold text-gaf-green mt-1" x-text="kpis.total.toLocaleString()">0</p></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4"><p class="text-xs text-gray-500 uppercase">Eligible</p><p class="text-2xl font-heading font-bold text-green-600 mt-1" x-text="kpis.eligible.toLocaleString()">0</p></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4"><p class="text-xs text-gray-500 uppercase">Shortlisted</p><p class="text-2xl font-heading font-bold text-gaf-khaki mt-1" x-text="kpis.shortlisted.toLocaleString()">0</p></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4"><p class="text-xs text-gray-500 uppercase">Screened</p><p class="text-2xl font-heading font-bold text-gaf-khaki mt-1" x-text="kpis.screened.toLocaleString()">0</p></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4"><p class="text-xs text-gray-500 uppercase">Selected</p><p class="text-2xl font-heading font-bold text-gaf-red mt-1" x-text="kpis.selected.toLocaleString()">0</p></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4"><p class="text-xs text-gray-500 uppercase">AI Processed</p><p class="text-2xl font-heading font-bold text-purple-600 mt-1" x-text="kpis.ai.toLocaleString()">0</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"><h3 class="font-heading font-semibold text-gray-800 mb-4">Regional Distribution</h3><canvas id="regionalchart" height="200"></canvas></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"><h3 class="font-heading font-semibold text-gray-800 mb-4">Gender Distribution</h3><canvas id="genderchart" height="200"></canvas></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"><h3 class="font-heading font-semibold text-gray-800 mb-4">Application Funnel</h3><canvas id="funnelchart" height="200"></canvas></div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"><h3 class="font-heading font-semibold text-gray-800 mb-4">Daily Applications</h3><canvas id="dailychart" height="200"></canvas></div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-heading font-semibold text-gray-800 mb-4">Recent Applicants</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="pb-3 font-medium">GAF ID</th><th class="pb-3 font-medium">Name</th><th class="pb-3 font-medium">Cycle</th><th class="pb-3 font-medium">Status</th><th class="pb-3 font-medium">Region</th><th class="pb-3 font-medium">Date</th></tr></thead>
                <tbody>
                    @foreach(['GAF-2026001','GAF-2026002','GAF-2026003','GAF-2026004','GAF-2026005','GAF-2026006','GAF-2026007','GAF-2026008','GAF-2026009','GAF-2026010'] as $i => $id)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 font-medium">{{ $id }}</td>
                        <td class="py-3">Applicant {{ $i + 1 }}</td>
                        <td class="py-3">2026/01</td>
                        <td class="py-3"><span class="text-xs font-semibold px-2 py-1 rounded-full {{ ['bg-green-100 text-green-700', 'bg-yellow-100 text-yellow-700', 'bg-blue-100 text-blue-700', 'bg-purple-100 text-purple-700', 'bg-red-100 text-red-700'][$i % 5] }}">{{ ['Eligible', 'Pending', 'Shortlisted', 'Screened', 'Rejected'][$i % 5] }}</span></td>
                        <td class="py-3">{{ ['Greater Accra','Ashanti','Eastern','Western','Central','Northern','Volta','Bono','Upper East','Upper West'][$i] }}</td>
                        <td class="py-3">2026-06-{{ str_pad(10 + $i, 2, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex space-x-3">
        <a href="{{ route('admin.applications') }}" class="px-6 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">View All Applications</a>
        <a href="{{ route('admin.cycles') }}" class="px-6 py-3 bg-gaf-red text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">Manage Cycles</a>
        <a href="{{ route('admin.reports') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Generate Reports</a>
    </div>
</div>
@endsection

@push('charts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('regionalchart'), {
        type: 'doughnut',
        data: { labels: ['Greater Accra','Ashanti','Eastern','Western','Central','Others'], datasets: [{ data: [3200,2800,1900,1500,1200,2820], backgroundColor: ['#C8102E','#003087','#0072b0','#FFD700','#10b981','#6b7280'] }] }
    });
    new Chart(document.getElementById('genderchart'), {
        type: 'bar',
        data: { labels: ['Male','Female'], datasets: [{ label: 'Applicants', data: [10200, 5220], backgroundColor: ['#003087','#C8102E'] }] },
        options: { scales: { y: { beginAtZero: true } } }
    });
    new Chart(document.getElementById('funnelchart'), {
        type: 'bar',
        data: { labels: ['Registered','Submitted','Eligible','Shortlisted','Screened','Selected'], datasets: [{ data: [15420, 12000, 8200, 3200, 1800, 750], backgroundColor: ['#C8102E','#003087','#0072b0','#FFD700','#10b981','#6b7280'] }] },
        options: { scales: { y: { beginAtZero: true } } }
    });
    new Chart(document.getElementById('dailychart'), {
        type: 'line',
        data: { labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], datasets: [{ label: 'Applications', data: [320, 450, 380, 520, 410, 290, 180], borderColor: '#003087', tension: 0.3, fill: false }] },
        options: { scales: { y: { beginAtZero: true } } }
    });
});
</script>
@endpush
