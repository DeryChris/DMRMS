@extends('layouts.admin')

@section('title', 'Users - DMRMS')

@section('content')
<div x-data="{ showModal: false }" class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="font-heading font-bold text-2xl text-gray-800">User Management</h1>
        <button @click="showModal = true" class="px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">+ Add User</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr><th class="px-6 py-4 text-left font-medium text-gray-700">Name</th><th class="px-6 py-4 text-left font-medium text-gray-700">Email</th><th class="px-6 py-4 text-left font-medium text-gray-700">Role</th><th class="px-6 py-4 text-left font-medium text-gray-700">Status</th><th class="px-6 py-4 text-right font-medium text-gray-700">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @php $users = [['Admin User', 'admin@gaf.mil.gh', 'Super Admin', 'active'], ['Screening Officer', 'screening@gaf.mil.gh', 'Admin', 'active'], ['Reviewer', 'reviewer@gaf.mil.gh', 'Admin', 'suspended']]; @endphp
                @foreach($users as $u)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $u[0] }}</td>
                    <td class="px-6 py-4">{{ $u[1] }}</td>
                    <td class="px-6 py-4">{{ $u[2] }}</td>
                    <td class="px-6 py-4"><span class="text-xs font-semibold px-2 py-1 rounded-full {{ $u[3] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ ucfirst($u[3]) }}</span></td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button class="text-gaf-khaki hover:underline text-sm font-medium">Edit</button>
                        <button class="text-red-600 hover:underline text-sm font-medium">{{ $u[3] === 'active' ? 'Suspend' : 'Activate' }}</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click.outside="showModal = false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4 p-8">
            <h2 class="font-heading font-bold text-xl text-gray-800 mb-6">Create New User</h2>
            <form class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label><input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Password</label><input type="password" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Role</label><select class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm"><option>Admin</option><option>Super Admin</option></select></div>
                <div class="flex space-x-3 pt-2">
                    <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
