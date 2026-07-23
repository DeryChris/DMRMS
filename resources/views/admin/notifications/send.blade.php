@extends('layouts.admin')
@section('title', 'Send Notification - DMRMS')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-heading font-bold text-gaf-dark-green">Send Notification</h1>
        <p class="text-sm text-gray-500 mt-1">Broadcast a notification to applicants or staff members.</p>
    </div>

    <form method="POST" action="{{ route('admin.notifications.send') }}" x-data="{ target: 'applicants', regionsOpen: false, rolesOpen: false, selectedRegions: [], selectedRoles: [], toggleRegion(val) { let i = this.selectedRegions.indexOf(val); if (i === -1) this.selectedRegions.push(val); else this.selectedRegions.splice(i, 1); }, toggleRole(val) { let i = this.selectedRoles.indexOf(val); if (i === -1) this.selectedRoles.push(val); else this.selectedRoles.splice(i, 1); }, selectAllRegions(v) { this.selectedRegions = v ? [''] : []; }, selectAllRoles(v) { this.selectedRoles = v ? [''] : []; }, regionLabel() { if (this.selectedRegions.includes('')) return 'All Regions'; if (this.selectedRegions.length === 0) return 'Filter by region...'; if (this.selectedRegions.length <= 2) return this.selectedRegions.join(', '); return this.selectedRegions.length + ' regions selected'; }, roleLabel() { if (this.selectedRoles.includes('')) return 'All Roles'; if (this.selectedRoles.length === 0) return 'Filter by role...'; if (this.selectedRoles.length <= 2) return this.selectedRoles.map(v => ({super_admin:'Super Admin',admin:'Admin',recruitment_officer:'Recruitment Officer',screening_officer:'Screening Officer',scheduling_officer:'Scheduling Officer'})[v]).join(', '); return this.selectedRoles.length + ' roles selected'; } }" x-init="{{ $errors->any() && old('target_type') ? '$nextTick(() => { target = \''.old('target_type', 'applicants').'\'; const r = '.json_encode(old('regions', [])).'; if(r.length>0) selectedRegions = r.filter(v=>v); const rs = '.json_encode(old('roles', [])).'; if(rs.length>0) selectedRoles = rs.filter(v=>v); })' : '' }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
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
            <label class="block text-sm font-semibold text-gray-700 mb-2">Regions <span class="text-gray-400 font-normal">(optional — select one or more)</span></label>
            <div class="relative">
                <button type="button" @click="regionsOpen = !regionsOpen" @click.outside="regionsOpen = false" class="w-full flex items-center justify-between border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white hover:border-gray-400 transition text-left">
                    <span :class="selectedRegions.length === 0 ? 'text-gray-400' : 'text-gray-700'" x-text="regionLabel()">Filter by region...</span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="regionsOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="regionsOpen" x-cloak x-transition class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto p-1.5">
                    <label class="flex items-center space-x-2.5 px-3 py-2 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" :checked="selectedRegions.includes('')" @change="selectAllRegions($el.checked)" class="rounded border-gray-300 text-gaf-green focus:ring-gaf-green">
                        <span class="text-sm font-medium text-gray-700">All Regions</span>
                    </label>
                    <hr class="border-gray-100 my-1">
                    <template x-for="r in {{ Js::from($regions) }}" :key="r">
                        <label class="flex items-center space-x-2.5 px-3 py-2 rounded hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" :checked="selectedRegions.includes(r)" @change="toggleRegion(r)" class="rounded border-gray-300 text-gaf-green focus:ring-gaf-green">
                            <span class="text-sm text-gray-600" x-text="r"></span>
                        </label>
                    </template>
                </div>
                <template x-for="r in selectedRegions" :key="r">
                    <input type="hidden" name="regions[]" :value="r">
                </template>
            </div>
        </div>

        <div x-show="target === 'admins'" x-cloak>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Admin Roles <span class="text-gray-400 font-normal">(optional — select one or more)</span></label>
            <div class="relative">
                <button type="button" @click="rolesOpen = !rolesOpen" @click.outside="rolesOpen = false" class="w-full flex items-center justify-between border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white hover:border-gray-400 transition text-left">
                    <span :class="selectedRoles.length === 0 ? 'text-gray-400' : 'text-gray-700'" x-text="roleLabel()">Filter by role...</span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="rolesOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="rolesOpen" x-cloak x-transition class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto p-1.5">
                    <label class="flex items-center space-x-2.5 px-3 py-2 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" :checked="selectedRoles.includes('')" @change="selectAllRoles($el.checked)" class="rounded border-gray-300 text-gaf-green focus:ring-gaf-green">
                        <span class="text-sm font-medium text-gray-700">All Roles</span>
                    </label>
                    <hr class="border-gray-100 my-1">
                    @foreach($adminRoles as $val => $label)
                    <label class="flex items-center space-x-2.5 px-3 py-2 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" :checked="selectedRoles.includes('{{ $val }}')" @change="toggleRole('{{ $val }}')" class="rounded border-gray-300 text-gaf-green focus:ring-gaf-green">
                        <span class="text-sm text-gray-600">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                <template x-for="r in selectedRoles" :key="r">
                    <input type="hidden" name="roles[]" :value="r">
                </template>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
            <input type="text" name="subject" required maxlength="255" placeholder="e.g., Recruitment Update" value="{{ old('subject') }}" class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-gaf-green focus:border-gaf-green {{ $errors->has('subject') ? 'border-red-500' : 'border-gray-300' }}">
            @error('subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
            <textarea name="message" required rows="6" placeholder="Type your notification message here..." class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-gaf-green focus:border-gaf-green resize-y {{ $errors->has('message') ? 'border-red-500' : 'border-gray-300' }}">{{ old('message') }}</textarea>
            @error('message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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