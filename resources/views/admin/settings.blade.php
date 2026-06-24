@extends('layouts.admin')

@section('title', 'Settings - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <h1 class="font-heading font-bold text-2xl text-gray-800">Settings</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">General Settings</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">System Name</label>
                        <input type="text" value="{{ $settings['system_name'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                        <input type="email" value="{{ $settings['support_email'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Applications Per Cycle</label>
                        <input type="number" value="{{ $settings['max_applications'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">Recruitment Configuration</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Age</label>
                            <input type="number" value="{{ $settings['min_age'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Age</label>
                            <input type="number" value="{{ $settings['max_age'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Height (Male)</label>
                            <input type="number" step="0.01" value="{{ $settings['min_height_male'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Height (Female)</label>
                            <input type="number" step="0.01" value="{{ $settings['min_height_female'] }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">System Info</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Version</span><span class="font-medium">{{ $settings['system_name'] ?? '1.0.0' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">PHP Version</span><span class="font-medium">{{ $settings['php_version'] }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Laravel</span><span class="font-medium">{{ $settings['laravel_version'] }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Database</span><span class="font-medium">{{ $settings['db_connection'] }}</span></div>
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">Actions</h2>
                <div class="space-y-3">
                    <button class="w-full px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Save Changes</button>
                    <button class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">Clear Cache</button>
                    <button class="w-full px-4 py-2 border border-red-300 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 transition">Reset to Defaults</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
