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
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $log->applicant?->name ?? $log->administrator?->name ?? 'System' }}</td>
                    <td class="px-6 py-4"><span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-700">{{ $log->action }}</span></td>
                    <td class="px-6 py-4 text-gray-500 max-w-xs truncate">{{ is_array($log->details) ? json_encode($log->details) : $log->details }}</td>
                    <td class="px-6 py-4 text-gray-500 font-mono text-xs">{{ $log->ip_address ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-right text-gray-500 text-xs">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        <p class="text-sm font-medium">No audit logs found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries</p>
        {{ $logs->links() }}
    </div>
</div>
@endsection
