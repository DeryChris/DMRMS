@php use App\Services\Security\PasswordPolicyService; @endphp
@php $policy = app(PasswordPolicyService::class); @endphp
@extends('layouts.admin')

@section('title', 'My Profile - Admin')

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Profile Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="bg-gaf-dark-green px-6 py-5">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-gaf-khaki rounded-full flex items-center justify-center text-gaf-dark-green font-heading font-bold text-xl shadow-md">
                    {{ substr($user->name ?? 'A', 0, 1) }}
                </div>
                <div class="text-white">
                    <h1 class="font-heading font-bold text-xl">{{ $user->name }}</h1>
                    <p class="text-white/70 text-sm">{{ $user->email }}</p>
                </div>
                <div class="ml-auto">
                    <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full {{ $user->role === 'super_admin' ? 'bg-purple-200 text-purple-800' : ($user->role === 'admin' ? 'bg-gaf-khaki text-gaf-dark-green' : 'bg-blue-100 text-blue-700') }}">
                        {{ ucwords(str_replace('_', ' ', $user->role)) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="px-6 py-3 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm bg-gray-50 border-t border-gray-100">
            <div>
                <span class="text-gray-400">Username</span>
                <p class="font-medium text-gray-800">{{ $user->username }}</p>
            </div>
            <div>
                <span class="text-gray-400">Status</span>
                <p class="font-medium">
                    <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full {{ $user->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </p>
            </div>
            <div>
                <span class="text-gray-400">Last Login</span>
                <p class="font-medium text-gray-800">{{ $user->last_login?->format('d M Y H:i') ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="text-gray-400">Password Last Changed</span>
                <p class="font-medium text-gray-800">{{ $user->password_changed_at?->format('d M Y') ?? 'Never' }}</p>
            </div>
        </div>
    </div>

    {{-- Update Profile Information --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-heading font-semibold text-base text-gray-800">Profile Information</h2>
            <p class="text-xs text-gray-400 mt-0.5">Update your name details.</p>
        </div>
        <form method="POST" action="{{ route('admin.profile.update') }}" class="px-6 py-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                @php $f = 'first_name'; @endphp
                <div>
                    <label for="{{ $f }}" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" id="{{ $f }}" name="{{ $f }}" value="{{ old($f, $user->first_name) }}" oninput="this.value = this.value.replace(/[0-9]/g, '')" required class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @php $f = 'last_name'; @endphp
                <div>
                    <label for="{{ $f }}" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" id="{{ $f }}" name="{{ $f }}" value="{{ old($f, $user->last_name) }}" oninput="this.value = this.value.replace(/[0-9]/g, '')" required class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <button type="submit" class="bg-gaf-green text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">
                Save Changes
            </button>
        </form>
    </div>

    {{-- Update Password --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-heading font-semibold text-base text-gray-800">Update Password</h2>
            <p class="text-xs text-gray-400 mt-0.5">Ensure your account is using a strong password.</p>
        </div>
        <form method="POST" action="{{ route('password.update') }}" class="px-6 py-5">
            @csrf
            @method('PUT')
            <div class="space-y-4 mb-5">
                @php $f = 'current_password'; @endphp
                <div>
                    <label for="{{ $f }}" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <input type="password" id="{{ $f }}" name="{{ $f }}" required class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @php $f = 'password'; @endphp
                <div>
                    <label for="{{ $f }}" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" id="{{ $f }}" name="{{ $f }}" required class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-400 mt-1">Minimum {{ $policy->getMinLength() }} characters{{ $policy->getValidationRules()[1] ?? '' ? ' with uppercase, numbers, and special characters' : '' }}.</p>
                </div>
                @php $f = 'password_confirmation'; @endphp
                <div>
                    <label for="{{ $f }}" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" id="{{ $f }}" name="{{ $f }}" required class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <button type="submit" class="bg-gaf-green text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">
                Update Password
            </button>
        </form>
    </div>
</div>
@endsection
