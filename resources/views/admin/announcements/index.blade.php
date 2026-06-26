@extends('layouts.admin')

@section('title', 'Manage Announcements - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Announcements &amp; News</h1>
        <a href="{{ route('admin.announcements.create') }}" class="px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">+ New Post</a>
    </div>


    <form method="GET" class="flex flex-wrap gap-3 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <select name="category" class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
            <option value="">All Categories</option>
            @foreach($categories as $c)
                <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
        <select name="published" class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
            <option value="">All Status</option>
            <option value="yes" {{ request('published') === 'yes' ? 'selected' : '' }}>Published</option>
            <option value="no" {{ request('published') === 'no' ? 'selected' : '' }}>Draft</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">Filter</button>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Title</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Category</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Author</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Status</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Views</th>
                    <th class="px-6 py-4 text-left font-medium text-gray-700">Published</th>
                    <th class="px-6 py-4 text-right font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($announcements as $a)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $a->title }}</td>
                    <td class="px-6 py-4"><span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">{{ ucfirst($a->category) }}</span></td>
                    <td class="px-6 py-4 text-gray-500">{{ $a->author ?? '--' }}</td>
                    <td class="px-6 py-4">
                        @if($a->is_published)
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-700">Published</span>
                        @else
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-500">Draft</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $a->views_count ?? 0 }}</td>
                    <td class="px-6 py-4 text-gray-500 text-xs">{{ $a->published_at?->format('Y-m-d') ?? '--' }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-1">
                            <form method="POST" action="{{ route('admin.announcements.toggle-publish', $a->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="relative group p-1.5 rounded-lg hover:bg-{{ $a->is_published ? 'amber' : 'green' }}-50 text-{{ $a->is_published ? 'amber' : 'green' }}-600 hover:text-{{ $a->is_published ? 'amber' : 'green' }}-800 transition-colors" title="{{ $a->is_published ? 'Unpublish' : 'Publish' }}">
                                    @if($a->is_published)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    @endif
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">{{ $a->is_published ? 'Unpublish' : 'Publish' }}</span>
                                </button>
                            </form>
                            <a href="{{ route('admin.announcements.edit', $a->id) }}" class="relative group p-1.5 rounded-lg hover:bg-gaf-green/10 text-gaf-green transition-colors inline-flex items-center" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.announcements.destroy', $a->id) }}" class="inline" onsubmit="return confirm('Delete this announcement?')">
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
                        <p class="text-sm font-medium">No announcements yet</p>
                        <a href="{{ route('admin.announcements.create') }}" class="text-gaf-green text-sm hover:underline mt-1 inline-block">Create your first post</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing {{ $announcements->firstItem() ?? 0 }} to {{ $announcements->lastItem() ?? 0 }} of {{ $announcements->total() }} entries</p>
        {{ $announcements->links() }}
    </div>
</div>
@endsection
