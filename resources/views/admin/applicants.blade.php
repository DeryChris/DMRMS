@php use Carbon\Carbon; @endphp
@extends('layouts.admin')
@section('title', 'Manage Applicants - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gradient-border pb-4">
        <div>
            <h1 class="text-2xl font-heading font-bold text-gaf-dark-green">Manage Applicants</h1>
            <p class="text-sm text-gray-500 mt-1">View and manage applicant accounts</p>
        </div>
        <span class="text-sm text-gray-500 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-100">
            {{ $applicants->total() }} total
        </span>
    </div>

    <div class="glass-strong rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 font-medium mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, GAF ID, phone..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gaf-khaki/30 focus:border-gaf-khaki outline-none">
            </div>
            <div>
                <label class="block text-xs text-gray-500 font-medium mb-1">Region</label>
                <select name="region" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gaf-khaki/30 focus:border-gaf-khaki outline-none">
                    <option value="">All Regions</option>
                    @foreach($regions as $r)
                    <option value="{{ $r }}" {{ request('region') === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 font-medium mb-1">Status</label>
                <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gaf-khaki/30 focus:border-gaf-khaki outline-none">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Filter</button>
            <a href="{{ route('admin.applicants') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200 transition">Clear</a>
        </form>
    </div>

    <div class="glass-strong rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3 font-medium">Name</th>
                    <th class="px-5 py-3 font-medium">Email</th>
                    <th class="px-5 py-3 font-medium">GAF ID</th>
                    <th class="px-5 py-3 font-medium">Region</th>
                    <th class="px-5 py-3 font-medium">Contact</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Registered</th>
                    <th class="px-5 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($applicants as $applicant)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-gaf-green/10 flex items-center justify-center text-xs font-bold text-gaf-green">{{ substr($applicant->name, 0, 1) }}</div>
                            <span class="font-medium text-gray-900">{{ $applicant->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $applicant->email }}</td>
                    <td class="px-5 py-3.5 font-mono text-xs text-gray-500">{{ $applicant->application->gaf_id ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $applicant->region ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $applicant->contact_number ?? '—' }}</td>
                    <td class="px-5 py-3.5">
                        @php $statusColors = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-gray-100 text-gray-500', 'suspended' => 'bg-red-100 text-red-700', 'pending' => 'bg-yellow-100 text-yellow-700']; @endphp
                        <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$applicant->status] ?? 'bg-gray-100 text-gray-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current opacity-60"></span>
                            <span>{{ ucfirst($applicant->status) }}</span>
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $applicant->created_at->format('M j, Y') }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end space-x-1">
                            <button @click="$dispatch('open-edit', { id: {{ $applicant->id }}, first_name: '{{ $applicant->first_name }}', last_name: '{{ $applicant->last_name }}', email: '{{ $applicant->email }}', contact_number: '{{ $applicant->contact_number ?? '' }}', region: '{{ $applicant->region ?? '' }}', status: '{{ $applicant->status }}' })" class="relative group p-1.5 rounded-lg hover:bg-gaf-green/10 text-gaf-green transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Edit</span>
                            </button>
                            <form method="POST" action="{{ route('admin.applicants.toggle-status', $applicant) }}" class="inline" onsubmit="return confirm('{{ $applicant->status === 'active' ? 'Suspend' : 'Activate' }} this applicant?')">
                                @csrf @method('PUT')
                                <button type="submit" class="relative group p-1.5 rounded-lg {{ $applicant->status === 'active' ? 'hover:bg-red-50 text-red-400 hover:text-red-600' : 'hover:bg-green-50 text-green-500 hover:text-green-700' }} transition-colors" title="{{ $applicant->status === 'active' ? 'Suspend' : 'Activate' }}">
                                    @if($applicant->status === 'active')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">{{ $applicant->status === 'active' ? 'Suspend' : 'Activate' }}</span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.applicants.delete', $applicant) }}" class="inline" onsubmit="return confirm('Delete this applicant permanently? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="relative group p-1.5 rounded-lg hover:bg-red-50 text-red-300 hover:text-red-500 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Delete</span>
                                </button>
                            </form>
                            @if($applicant->application)
                            <a href="{{ route('admin.applications.detail', $applicant->application->id) }}" class="relative group p-1.5 rounded-lg hover:bg-gaf-dark-green/10 text-gaf-dark-green transition-colors" title="View Application">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Application</span>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                        <p class="text-sm font-medium">No applicants found</p>
                        <p class="text-xs text-gray-400 mt-1">Try adjusting your search or filters.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $applicants->links() }}
    </div>
</div>

<div id="applicant-old-data" data-old="{{ json_encode(old()) }}" data-edit-id="{{ request()->route('applicant') ?? '' }}" class="hidden"></div>

<div x-data="{ open: false, form: { id: null, first_name: '', last_name: '', email: '', contact_number: '', region: '', status: 'active' } }"
     x-init="{{ $errors->any() && old('first_name') ? '$nextTick(() => { const d = document.getElementById(\'applicant-old-data\'); if(!d)return; try{const o=JSON.parse(d.dataset.old||\'{}\'); open=true; form.first_name=o.first_name||\'\'; form.last_name=o.last_name||\'\'; form.email=o.email||\'\'; form.contact_number=o.contact_number||\'\'; form.region=o.region||\'\'; form.status=o.status||\'active\'; }catch(e){} })' : '' }}"
     x-on:open-edit.window="open = true; form.id = $event.detail.id; form.first_name = $event.detail.first_name; form.last_name = $event.detail.last_name; form.email = $event.detail.email; form.contact_number = $event.detail.contact_number; form.region = $event.detail.region; form.status = $event.detail.status"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background: rgba(0,0,0,0.5);">
    <div @click.outside="open = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-heading font-bold text-gaf-dark-green">Edit Applicant</h3>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form :action="`/admin/applicants/${form.id}`" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 font-medium mb-1">First Name</label>
                    <input type="text" name="first_name" x-model="form.first_name" @input="form.first_name = $event.target.value.replace(/[0-9]/g, '')" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gaf-khaki/30 focus:border-gaf-khaki outline-none {{ $errors->has('first_name') ? 'border-red-500' : 'border-gray-200' }}">
                    @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 font-medium mb-1">Last Name</label>
                    <input type="text" name="last_name" x-model="form.last_name" @input="form.last_name = $event.target.value.replace(/[0-9]/g, '')" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gaf-khaki/30 focus:border-gaf-khaki outline-none {{ $errors->has('last_name') ? 'border-red-500' : 'border-gray-200' }}">
                    @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 font-medium mb-1">Email</label>
                <input type="email" name="email" x-model="form.email" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gaf-khaki/30 focus:border-gaf-khaki outline-none {{ $errors->has('email') ? 'border-red-500' : 'border-gray-200' }}">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 font-medium mb-1">Contact Number</label>
                    <input type="text" name="contact_number" x-model="form.contact_number" @input="form.contact_number = $event.target.value.replace(/\D/g, '').substring(0, 10)" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gaf-khaki/30 focus:border-gaf-khaki outline-none {{ $errors->has('contact_number') ? 'border-red-500' : 'border-gray-200' }}">
                    @error('contact_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 font-medium mb-1">Region</label>
                    <select name="region" x-model="form.region" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gaf-khaki/30 focus:border-gaf-khaki outline-none {{ $errors->has('region') ? 'border-red-500' : 'border-gray-200' }}">
                        <option value="">Select Region</option>
                        @foreach($regions as $r)
                        <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                    @error('region') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 font-medium mb-1">Status</label>
                <select name="status" x-model="form.status" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gaf-khaki/30 focus:border-gaf-khaki outline-none {{ $errors->has('status') ? 'border-red-500' : 'border-gray-200' }}">
                    @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" @click="open = false" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
