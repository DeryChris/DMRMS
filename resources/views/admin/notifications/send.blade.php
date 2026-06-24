@extends('layouts.admin')
@section('title', 'Send Notification - DMRMS')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-heading font-bold text-gaf-dark-green">Send Notification</h1>
        <p class="text-sm text-gray-500 mt-1">Broadcast a notification to applicants or staff members.</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-4 mb-6 flex items-center space-x-3">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.notifications.send') }}" x-data="{ target: 'applicants' }" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Send To</label>
            <div class="flex space-x-3">
                <label class="flex items-center space-x-2 px-4 py-2.5 rounded-lg border-2 cursor-pointer transition" :class="target === 'applicants' ? 'border-gaf-green bg-gaf-green/5' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="target_type" value="applicants" x-model="target" class="text-gaf-green focus:ring-gaf-green">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="text-sm font-medium">Applicants</span>
                </label>
                <label class="flex items-center space-x-2 px-4 py-2.5 rounded-lg border-2 cursor-pointer transition" :class="target === 'admins' ? 'border-gaf-green bg-gaf-green/5' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="target_type" value="admins" x-model="target" class="text-gaf-green focus:ring-gaf-green">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="text-sm font-medium">Administrators</span>
                </label>
            </div>
        </div>

        <div x-show="target === 'applicants'" x-cloak>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Region <span class="text-gray-400 font-normal">(optional — leave blank for all regions)</span></label>
            <select name="region" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-gaf-green focus:border-gaf-green">
                <option value="">All Regions</option>
                @foreach($regions as $region)
                <option value="{{ $region }}">{{ $region }}</option>
                @endforeach
            </select>
        </div>

        <div x-show="target === 'admins'" x-cloak>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Admin Role <span class="text-gray-400 font-normal">(optional — leave blank for all roles)</span></label>
            <select name="role" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-gaf-green focus:border-gaf-green">
                <option value="">All Roles</option>
                @foreach($adminRoles as $val => $label)
                <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
            <input type="text" name="subject" required maxlength="255" placeholder="e.g., Recruitment Update" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-gaf-green focus:border-gaf-green">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
            <textarea name="message" required rows="6" placeholder="Type your notification message here..." class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-gaf-green focus:border-gaf-green resize-y"></textarea>
        </div>

        <div class="flex items-center justify-between pt-2">
            <p class="text-xs text-gray-400">Notifications will appear in recipients' dashboard bell.</p>
            <button type="submit" class="bg-gaf-green text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition shadow-sm">
                Send Notification
            </button>
        </div>
    </form>
</div>
@endsection