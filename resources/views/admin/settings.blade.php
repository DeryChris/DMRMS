@extends('layouts.admin')

@section('title', 'Settings - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <h1 class="font-heading font-bold text-2xl text-gray-800 gradient-border pb-4">Settings</h1>


    <form method="POST" action="{{ route('admin.settings.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf

        <div class="lg:col-span-2 space-y-6">
            <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
                <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">General Settings</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">System Name</label>
                        <input type="text" name="system_name" value="{{ $settings['system_name'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                        @error('system_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                        <input type="email" name="support_email" value="{{ $settings['support_email'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                        @error('support_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Applications Per Cycle</label>
                        <input type="number" name="max_applications" value="{{ $settings['max_applications'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                        @error('max_applications') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
                <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">Recruitment Configuration</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Age</label>
                            <input type="number" name="min_age" value="{{ $settings['min_age'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                            @error('min_age') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Age</label>
                            <input type="number" name="max_age" value="{{ $settings['max_age'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                            @error('max_age') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Height (Male) (m)</label>
                            <input type="number" step="0.01" name="min_height_male" value="{{ $settings['min_height_male'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                            @error('min_height_male') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Height (Female) (m)</label>
                            <input type="number" step="0.01" name="min_height_female" value="{{ $settings['min_height_female'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                            @error('min_height_female') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
                <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">Security Settings</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Session Lifetime (minutes)</label>
                        <input type="number" name="session_lifetime" value="{{ $settings['session_lifetime'] ?? 120 }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Min Length</label>
                        <input type="number" name="password_min_length" value="{{ $settings['password_min_length'] ?? 8 }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Login Attempts</label>
                        <input type="number" name="max_login_attempts" value="{{ $settings['max_login_attempts'] ?? 5 }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                </div>
                <div class="mt-4 space-y-3">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="registration_enabled" value="1" {{ ($settings['registration_enabled'] ?? true) ? 'checked' : '' }} class="w-4 h-4 text-gaf-green border-gray-300 rounded focus:ring-gaf-khaki">
                        <span class="text-sm text-gray-700">Enable Applicant Registration</span>
                    </label>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="mfa_required" value="1" {{ ($settings['mfa_required'] ?? false) ? 'checked' : '' }} class="w-4 h-4 text-gaf-green border-gray-300 rounded focus:ring-gaf-khaki">
                        <span class="text-sm text-gray-700">Require MFA for Admins</span>
                    </label>
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="maintenance_mode" value="1" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }} class="w-4 h-4 text-gaf-green border-gray-300 rounded focus:ring-gaf-khaki">
                        <span class="text-sm text-gray-700">Maintenance Mode</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
                <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">System Info</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Version</span><span class="font-medium">{{ $settings['system_name'] ?? '1.0.0' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">PHP Version</span><span class="font-medium">{{ $settings['php_version'] }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Laravel</span><span class="font-medium">{{ $settings['laravel_version'] }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Database</span><span class="font-medium">{{ $settings['db_connection'] }}</span></div>
                </dl>
            </div>

            <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
                <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">Actions</h2>
                <div class="space-y-3">
                    <button type="submit" class="w-full px-4 py-2 text-white rounded-lg text-sm font-medium transition" style="background:linear-gradient(135deg,#22c55e,#16a34a);">Save Changes</button>
                    <a href="{{ route('admin.settings') }}" class="block w-full text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">Reset</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
