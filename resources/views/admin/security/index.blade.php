@extends('layouts.admin')

@section('title', 'Security - Ghana Armed Forces')

@section('content')
<div x-data="{ activeTab: 'password' }" class="space-y-6">
    <h1 class="font-heading font-bold text-2xl text-gray-800">Security Management</h1>


    <div class="flex space-x-1 bg-white rounded-xl shadow-sm border border-gray-200 p-1">
        <button @click="activeTab = 'password'" :class="activeTab === 'password' ? 'bg-gaf-green text-white' : 'text-gray-600 hover:bg-gray-100'" class="px-4 py-2 rounded-lg text-sm font-medium transition">Password Policy</button>
        <button @click="activeTab = 'mfa'" :class="activeTab === 'mfa' ? 'bg-gaf-green text-white' : 'text-gray-600 hover:bg-gray-100'" class="px-4 py-2 rounded-lg text-sm font-medium transition">MFA Settings</button>
        <button @click="activeTab = 'sessions'" :class="activeTab === 'sessions' ? 'bg-gaf-green text-white' : 'text-gray-600 hover:bg-gray-100'" class="px-4 py-2 rounded-lg text-sm font-medium transition">Active Sessions</button>
        <button @click="activeTab = 'logins'" :class="activeTab === 'logins' ? 'bg-gaf-green text-white' : 'text-gray-600 hover:bg-gray-100'" class="px-4 py-2 rounded-lg text-sm font-medium transition">Login Monitoring</button>
        <button @click="activeTab = 'ipaccess'" :class="activeTab === 'ipaccess' ? 'bg-gaf-green text-white' : 'text-gray-600 hover:bg-gray-100'" class="px-4 py-2 rounded-lg text-sm font-medium transition">IP Access</button>
    </div>

    {{-- Password Policy --}}
    <div x-show="activeTab === 'password'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">Password Policy</h2>
        <form method="POST" action="{{ route('admin.security.password-policy') }}" class="space-y-4 max-w-lg">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Length</label>
                <input type="number" name="min_length" value="{{ old('min_length', $passwordPolicy['min_length']) }}" class="w-full px-4 py-2 border rounded-lg text-sm {{ $errors->has('min_length') ? 'border-red-500' : 'border-gray-300' }}">
                @error('min_length') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Expiry (days, 0 = never)</label>
                <input type="number" name="expiry_days" value="{{ old('expiry_days', $passwordPolicy['expiry_days']) }}" class="w-full px-4 py-2 border rounded-lg text-sm {{ $errors->has('expiry_days') ? 'border-red-500' : 'border-gray-300' }}">
                @error('expiry_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-2">
                <label class="flex items-center space-x-3">
                    <input type="checkbox" name="require_special" value="1" {{ $passwordPolicy['require_special'] ? 'checked' : '' }} class="w-4 h-4 text-gaf-green border-gray-300 rounded">
                    <span class="text-sm text-gray-700">Require special characters (!@#$%)</span>
                </label>
                <label class="flex items-center space-x-3">
                    <input type="checkbox" name="require_numbers" value="1" {{ $passwordPolicy['require_numbers'] ? 'checked' : '' }} class="w-4 h-4 text-gaf-green border-gray-300 rounded">
                    <span class="text-sm text-gray-700">Require numbers</span>
                </label>
                <label class="flex items-center space-x-3">
                    <input type="checkbox" name="require_uppercase" value="1" {{ $passwordPolicy['require_uppercase'] ? 'checked' : '' }} class="w-4 h-4 text-gaf-green border-gray-300 rounded">
                    <span class="text-sm text-gray-700">Require uppercase letters</span>
                </label>
            </div>
            <button type="submit" class="px-6 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Save Password Policy</button>
        </form>
    </div>

    {{-- MFA Settings --}}
    <div x-show="activeTab === 'mfa'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">MFA / Two-Factor Authentication</h2>
        <form method="POST" action="{{ route('admin.security.mfa') }}" class="space-y-4 max-w-lg">
            @csrf
            <label class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-800">Force MFA for All Admins</p>
                    <p class="text-xs text-gray-500">Requires two-factor authentication for all admin accounts</p>
                </div>
                <button type="button" @click="$el.previousElementSibling.querySelector('input').click(); $el.previousElementSibling.querySelector('input').checked = !$el.previousElementSibling.querySelector('input').checked" class="relative w-12 h-6 rounded-full transition" :class="$el.previousElementSibling.querySelector('input').checked ? 'bg-gaf-green' : 'bg-gray-300'">
                    <span class="absolute w-5 h-5 bg-white rounded-full shadow top-0.5 transition-transform" :class="$el.previousElementSibling.querySelector('input').checked ? 'translate-x-6' : 'translate-x-0.5'"></span>
                </button>
                <div class="hidden">
                    <input type="checkbox" name="forced" value="1" {{ $mfaSettings['forced'] ? 'checked' : '' }}>
                </div>
            </label>
            <p class="text-xs text-gray-400 italic">MFA implementation requires additional setup (email-based OTP or authenticator app integration).</p>
            <button type="submit" class="px-6 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Save MFA Settings</button>
        </form>
    </div>

    {{-- Active Sessions --}}
    <div x-show="activeTab === 'sessions'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">Active Sessions</h2>
        @if($sessions->isEmpty())
        <p class="text-sm text-gray-500 text-center py-8">No active sessions found.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">User</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">IP Address</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">User Agent</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Last Activity</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($sessions as $session)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $session->name ?? 'Unknown' }}</td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $session->ip_address ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate">{{ $session->user_agent_short }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $session->last_activity_humans }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.security.sessions.terminate', $session->id) }}" class="inline" onsubmit="return confirm('Terminate this session?')">
                                @csrf
                                <button type="submit" class="relative group p-1.5 rounded-lg hover:bg-red-50 text-red-400 hover:text-red-600 transition-colors" title="Terminate">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Terminate</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Login Monitoring --}}
    <div x-show="activeTab === 'logins'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">Failed Login Attempts</h2>
        @if($failedLogins->isEmpty())
        <p class="text-sm text-gray-500 text-center py-8">No failed login attempts recorded.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Email</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">IP Address</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Guard</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-700">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($failedLogins as $attempt)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $attempt->email ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $attempt->ip_address ?? 'N/A' }}</td>
                        <td class="px-4 py-3"><span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-100 text-red-700">{{ $attempt->guard ?? 'web' }}</span></td>
                        <td class="px-4 py-3 text-right text-gray-500 text-xs">{{ \Carbon\Carbon::parse($attempt->created_at)->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- IP Access Control --}}
    <div x-show="activeTab === 'ipaccess'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="font-heading font-bold text-lg text-gray-800 mb-4">IP Access Control</h2>
        <form method="POST" action="{{ route('admin.security.ip-access') }}" class="space-y-4 max-w-lg">
            @csrf
            <label class="flex items-center space-x-3 mb-4">
                <input type="checkbox" name="enabled" value="1" {{ $ipAccess['enabled'] ? 'checked' : '' }} class="w-4 h-4 text-gaf-green border-gray-300 rounded">
                <span class="text-sm font-medium text-gray-700">Enable IP Access Control</span>
            </label>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Whitelist (one IP per line)</label>
                <textarea name="whitelist" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm font-mono">{{ implode("\n", $ipAccess['whitelist'] ?? []) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Blacklist (one IP per line)</label>
                <textarea name="blacklist" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm font-mono">{{ implode("\n", $ipAccess['blacklist'] ?? []) }}</textarea>
            </div>
            <p class="text-xs text-gray-400">Enter IP addresses in CIDR notation (e.g., 192.168.1.0/24 or 10.0.0.1)</p>
            <button type="submit" class="px-6 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Save IP Access</button>
        </form>
    </div>
</div>
@endsection
