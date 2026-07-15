@extends('layouts.admin')

@section('title', 'Users - Ghana Armed Forces')

@section('content')
<div x-data="{ showModal: false, editing: null, editId: null }" class="space-y-6">
    <div class="flex items-center justify-between gradient-border pb-4">
        <h1 class="font-heading font-bold text-2xl text-gray-800">User Management</h1>
        <button @click="showModal = true; editing = null; editId = null" class="px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">+ Add User</button>
    </div>


    <div class="glass-strong rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="table-header-gradient"><th class="px-6 py-4 text-left text-white/90">Name</th><th class="px-6 py-4 text-left text-white/90">Email</th><th class="px-6 py-4 text-left text-white/90">Role</th><th class="px-6 py-4 text-left text-white/90">Status</th><th class="px-6 py-4 text-right text-white/90">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $u)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $u->name }}</td>
                    <td class="px-6 py-4">{{ $u->email }}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full gold-accent" style="background:rgba(212,175,55,0.15);color:var(--gaf-khaki);">{{ ucfirst(str_replace('_', ' ', $u->role ?? 'admin')) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $u->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($u->status ?? 'active') }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-1">
                            <button @click="showModal = true; editing = true; editId = {{ $u->id }}; $nextTick(() => { document.getElementById('edit-first_name-{{ $u->id }}').value = '{{ $u->first_name }}'; document.getElementById('edit-last_name-{{ $u->id }}').value = '{{ $u->last_name }}'; document.getElementById('edit-email-{{ $u->id }}').value = '{{ $u->email }}'; document.getElementById('edit-role-{{ $u->id }}').value = '{{ $u->role }}'; })" class="relative group p-1.5 rounded-lg hover:bg-gaf-khaki/10 text-gaf-khaki transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Edit</span>
                            </button>
                            <form method="POST" action="{{ route('admin.users.toggle-status', $u->id) }}" class="inline" onsubmit="return confirm('{{ $u->status === 'active' ? 'Suspend' : 'Activate' }} this user?')">
                                @csrf @method('PUT')
                                <button type="submit" class="relative group p-1.5 rounded-lg {{ $u->status === 'active' ? 'hover:bg-red-50 text-red-400 hover:text-red-600' : 'hover:bg-green-50 text-green-500 hover:text-green-700' }} transition-colors" title="{{ $u->status === 'active' ? 'Suspend' : 'Activate' }}">
                                    @if($u->status === 'active')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">{{ $u->status === 'active' ? 'Suspend' : 'Activate' }}</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        <p class="text-sm font-medium">No users found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.outside="showModal = false">
        <div class="glass-strong rounded-xl shadow-lg w-full max-w-md mx-4 p-8" style="background:rgba(255,255,255,0.95);backdrop-filter:blur(16px);">
            <h2 class="font-heading font-bold text-xl text-gray-800 mb-6" x-text="editing ? 'Edit User' : 'Create New User'"></h2>

            <form x-show="!editing && !editId" method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">First Name</label><input type="text" name="first_name" value="{{ old('first_name') }}" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has('first_name') ? 'border-red-500' : 'border-gray-300' }}" required>@error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label><input type="text" name="last_name" value="{{ old('last_name') }}" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has('last_name') ? 'border-red-500' : 'border-gray-300' }}" required>@error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }}" required>@error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Password</label><input type="password" name="password" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has('password') ? 'border-red-500' : 'border-gray-300' }}" required>@error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" class="w-full border rounded-lg px-4 py-3 text-sm {{ $errors->has('role') ? 'border-red-500' : 'border-gray-300' }}">@error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="recruitment_officer">Recruitment Officer</option>
                        <option value="screening_officer">Screening Officer</option>
                        <option value="scheduling_officer">Scheduling Officer</option>
                    </select>
                </div>
                <div class="flex space-x-3 pt-2">
                    <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Create User</button>
                </div>
            </form>

            <template x-if="editId">
                <form method="POST" :action="`{{ url('admin/users') }}/${editId}`" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">First Name</label><input type="text" :id="`edit-first_name-${editId}`" name="first_name" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has('first_name') ? 'border-red-500' : 'border-gray-300' }}" required>@error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label><input type="text" :id="`edit-last_name-${editId}`" name="last_name" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has('last_name') ? 'border-red-500' : 'border-gray-300' }}" required>@error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" :id="`edit-email-${editId}`" name="email" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }}" required>@error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Password (leave blank to keep current)</label><input type="password" name="password" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has('password') ? 'border-red-500' : 'border-gray-300' }}">@error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror</div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select :id="`edit-role-${editId}`" name="role" class="w-full border rounded-lg px-4 py-3 text-sm {{ $errors->has('role') ? 'border-red-500' : 'border-gray-300' }}">@error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="recruitment_officer">Recruitment Officer</option>
                            <option value="screening_officer">Screening Officer</option>
                            <option value="scheduling_officer">Scheduling Officer</option>
                        </select>
                    </div>
                    <div class="flex space-x-3 pt-2">
                        <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="flex-1 px-4 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Update User</button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
@endsection
