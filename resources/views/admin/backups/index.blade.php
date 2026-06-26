@extends('layouts.admin')

@section('title', 'Backup Management - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Backup Management</h1>
        <form method="POST" action="{{ route('admin.backups.create') }}" class="inline" onsubmit="return confirm('Create a new database backup? This may take a moment.')">
            @csrf
            <button type="submit" class="px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">+ Create Backup</button>
        </form>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gaf-green">{{ $totalBackups }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Backups</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gaf-green">{{ $storageUsed > 0 ? round($storageUsed / 1048576, 2) : 0 }} MB</p>
            <p class="text-xs text-gray-500 mt-1">Storage Used</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gaf-green">{{ $lastBackup?->created_at?->diffForHumans() ?? 'N/A' }}</p>
            <p class="text-xs text-gray-500 mt-1">Last Backup</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gaf-green">{{ $todayBackups }}</p>
            <p class="text-xs text-gray-500 mt-1">Today's Backups</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Filename</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Size</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Type</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Status</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Created By</th>
                    <th class="px-6 py-4 text-right font-medium text-gray-700">Date</th>
                    <th class="px-6 py-4 text-right font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($backups as $backup)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-sm">{{ $backup->filename }}</td>
                    <td class="px-6 py-4 text-gray-500 text-xs">{{ $backup->size_for_humans }}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-700">{{ ucfirst($backup->type) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $backup->status === 'completed' ? 'bg-green-100 text-green-700' : ($backup->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">{{ ucfirst($backup->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-xs">{{ $backup->creator?->name ?? 'System' }}</td>
                    <td class="px-6 py-4 text-right text-gray-500 text-xs">{{ $backup->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-1">
                            @if($backup->status === 'completed')
                            <a href="{{ route('admin.backups.download', $backup) }}" class="relative group p-1.5 rounded-lg hover:bg-gaf-khaki/10 text-gaf-khaki transition-colors inline-flex items-center" title="Download">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Download</span>
                            </a>
                            @endif
                            <form method="POST" action="{{ route('admin.backups.destroy', $backup) }}" class="inline" onsubmit="return confirm('Delete this backup?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="relative group p-1.5 rounded-lg hover:bg-red-50 text-red-300 hover:text-red-500 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        <p class="text-sm font-medium">No backups yet. Create your first backup.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing {{ $backups->firstItem() ?? 0 }} to {{ $backups->lastItem() ?? 0 }} of {{ $backups->total() }} backups</p>
        {{ $backups->links() }}
    </div>
</div>
@endsection
