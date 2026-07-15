@extends('layouts.admin')

@section('title', 'Manage Barracks - Ghana Armed Forces')

@section('content')
<div x-data="barrackManager()" class="max-w-6xl mx-auto px-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 gradient-border pb-4">
        <div>
            <h1 class="font-heading font-bold text-2xl text-gray-800">Barracks / Camps</h1>
            <p class="text-gray-500 text-sm">Manage military barracks and camps assigned to each region.</p>
        </div>
        <button @click="openCreate()" class="inline-flex items-center space-x-2 bg-gaf-green text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add Barrack</span>
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Region</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Name</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Location</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($barracks as $barrack)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-800">{{ $barrack->region }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $barrack->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $barrack->location ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($barrack->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end space-x-1">
                            <button @click="openEdit({{ $barrack->id }}, '{{ $barrack->region }}', '{{ $barrack->name }}', '{{ $barrack->location ?? '' }}', {{ $barrack->is_active ? 'true' : 'false' }})" class="relative group p-1.5 rounded-lg hover:bg-gaf-green/10 text-gaf-green transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Edit</span>
                            </button>
                            <form action="{{ route('admin.barracks.destroy', $barrack) }}" method="POST" class="inline" onsubmit="return confirm('Delete this barrack?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="relative group p-1.5 rounded-lg hover:bg-red-50 text-red-400 hover:text-red-600 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Delete</span>
                                </button>
                            </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <p class="font-medium">No barracks configured yet.</p>
                            <p class="text-xs mt-1">Add barracks so applicants know where to report.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($barracks->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $barracks->links() }}
        </div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-start justify-center pt-12 pb-8 bg-black bg-opacity-50 overflow-y-auto" @click.outside="showModal = false">
        <div class="glass-strong rounded-xl shadow-lg w-full max-w-lg mx-4 p-8" style="background:rgba(255,255,255,0.95);backdrop-filter:blur(16px);">
            <h2 class="font-heading font-bold text-xl text-gray-800 mb-6" x-text="editingId ? 'Edit Barrack / Camp' : 'Add Barrack / Camp'"></h2>

            <form :action="editingId ? '{{ url('admin/barracks') }}/' + editingId : '{{ route('admin.barracks.store') }}'" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="editingId ? 'PUT' : 'POST'">

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Region</label>
                        <select name="region" x-model="form.region" class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-gaf-green/30 focus:border-gaf-green outline-none transition {{ $errors->has('region') ? 'border-red-500' : 'border-gray-300' }}" required>
                            <option value="">Select Region</option>
                            @foreach($regions as $region)
                            <option value="{{ $region }}" x-bind:selected="form.region === '{{ $region }}'">{{ $region }}</option>
                            @endforeach
                        </select>
                        @error('region') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Barrack / Camp Name</label>
                        <input type="text" name="name" x-model="form.name" placeholder="e.g. Burma Camp" class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-gaf-green/30 focus:border-gaf-green outline-none transition {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }}" required>
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Location <span class="text-gray-400">(optional)</span></label>
                        <input type="text" name="location" x-model="form.location" placeholder="e.g. Accra" class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-gaf-green/30 focus:border-gaf-green outline-none transition {{ $errors->has('location') ? 'border-red-500' : 'border-gray-300' }}">
                        @error('location') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center space-x-3">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="w-4 h-4 rounded border-gray-300 text-gaf-green focus:ring-gaf-green">
                        <label class="text-sm text-gray-700">Active</label>
                    </div>
                </div>

                <div class="flex space-x-3 pt-6">
                    <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition" x-text="editingId ? 'Update Barrack' : 'Save Barrack'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function barrackManager() {
    return {
        showModal: false,
        editingId: null,
        form: {
            region: '',
            name: '',
            location: '',
            is_active: true,
        },
        openCreate() {
            this.editingId = null;
            this.form = { region: '', name: '', location: '', is_active: true };
            this.showModal = true;
        },
        openEdit(id, region, name, location, isActive) {
            this.editingId = id;
            this.form = { region: region, name: name, location: location, is_active: isActive };
            this.showModal = true;
        }
    }
}
</script>
@endsection