@extends('layouts.admin')

@section('title', 'Audit Logs - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Audit Logs</h1>
        <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">Export Logs</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" placeholder="Search logs..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
            </div>
            <select class="border border-gray-300 rounded-lg px-4 py-2 text-sm"><option>All Actions</option><option>Login</option><option>Status Update</option><option>Document Verify</option><option>Application Submit</option></select>
            <input type="date" class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr><th class="px-6 py-4 text-left font-medium text-gray-700">User</th><th class="px-6 py-4 text-left font-medium text-gray-700">Action</th><th class="px-6 py-4 text-left font-medium text-gray-700">Details</th><th class="px-6 py-4 text-left font-medium text-gray-700">IP Address</th><th class="px-6 py-4 text-right font-medium text-gray-700">Timestamp</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @php
                    $logs = [
                        ['Admin User', 'Login', 'Successful login from web interface', '192.168.1.100', '2026-06-24 08:30:00'],
                        ['System', 'Status Update', 'Application GAF-2026001 set to Eligible', '10.0.0.1', '2026-06-24 08:25:00'],
                        ['Screening Officer', 'Document Verify', 'Verified National ID for GAF-2026001', '192.168.1.102', '2026-06-24 08:20:00'],
                        ['Applicant #1', 'Application Submit', 'Submitted application for 2026/01 cycle', '10.0.0.5', '2026-06-24 08:15:00'],
                        ['Admin User', 'User Create', 'Created new user: Screening Officer', '192.168.1.100', '2026-06-24 08:00:00'],
                        ['System', 'AI Process', 'AI eligibility check for 150 applications', '10.0.0.1', '2026-06-24 07:45:00'],
                        ['Reviewer', 'Status Update', 'Application GAF-2026003 marked Shortlisted', '192.168.1.103', '2026-06-24 07:30:00'],
                        ['Admin User', 'Logout', 'Successful logout', '192.168.1.100', '2026-06-23 18:00:00'],
                        ['Screening Officer', 'Login', 'Successful login from web interface', '192.168.1.102', '2026-06-23 07:55:00'],
                        ['System', 'Cycle Create', 'Created new cycle: 2026/01', '10.0.0.1', '2026-06-23 07:00:00'],
                    ];
                @endphp
                @foreach($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $log[0] }}</td>
                    <td class="px-6 py-4"><span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-700">{{ $log[1] }}</span></td>
                    <td class="px-6 py-4 text-gray-500 max-w-xs truncate">{{ $log[2] }}</td>
                    <td class="px-6 py-4 text-gray-500 font-mono text-xs">{{ $log[3] }}</td>
                    <td class="px-6 py-4 text-right text-gray-500 text-xs">{{ $log[4] }}</td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing 1 to 10 of 1,245 entries</p>
        <div class="flex space-x-2">
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Prev</button>
            <button class="px-3 py-2 bg-gaf-green text-white rounded-lg text-sm">1</button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">2</button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">3</button>
            <button class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Next</button>
        </div>
    </div>
</div>
@endsection
