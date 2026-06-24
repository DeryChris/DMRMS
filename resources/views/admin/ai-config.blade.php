@extends('layouts.admin')

@section('title', 'AI Configuration - DMRMS')

@section('content')
<div x-data="{ aiEnabled: true, provider: 'openai' }" class="space-y-6">
    <h1 class="font-heading font-bold text-2xl text-gray-800">AI Configuration</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">AI Features</h3>
        <div class="space-y-4">
            <label class="flex items-center justify-between">
                <div><p class="text-sm font-medium text-gray-800">AI Document Verification</p><p class="text-xs text-gray-500">Automatically verify uploaded documents</p></div>
                <button @click="aiEnabled = !aiEnabled" class="relative w-12 h-6 rounded-full transition" :class="aiEnabled ? 'bg-gaf-green' : 'bg-gray-300'">
                    <span class="absolute w-5 h-5 bg-white rounded-full shadow top-0.5 transition-transform" :class="aiEnabled ? 'translate-x-6' : 'translate-x-0.5'"></span>
                </button>
            </label>
            <label class="flex items-center justify-between">
                <div><p class="text-sm font-medium text-gray-800">AI Eligibility Screening</p><p class="text-xs text-gray-500">Auto-check eligibility criteria</p></div>
                <button class="relative w-12 h-6 rounded-full bg-gaf-green transition"><span class="absolute w-5 h-5 bg-white rounded-full shadow top-0.5 translate-x-6"></span></button>
            </label>
            <label class="flex items-center justify-between">
                <div><p class="text-sm font-medium text-gray-800">AI Chatbot</p><p class="text-xs text-gray-500">Enable public-facing AI assistant</p></div>
                <button class="relative w-12 h-6 rounded-full bg-gaf-green transition"><span class="absolute w-5 h-5 bg-white rounded-full shadow top-0.5 translate-x-6"></span></button>
            </label>
            <label class="flex items-center justify-between">
                <div><p class="text-sm font-medium text-gray-800">AI Candidate Matching</p><p class="text-xs text-gray-500">Match applicants to best-fit roles</p></div>
                <button class="relative w-12 h-6 rounded-full bg-gray-300 transition"><span class="absolute w-5 h-5 bg-white rounded-full shadow top-0.5 translate-x-0.5"></span></button>
            </label>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Provider Settings</h3>
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">AI Provider</label>
                    <select x-model="provider" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        <option value="openai">OpenAI</option>
                        <option value="anthropic">Anthropic</option>
                        <option value="google">Google AI</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">API Key</label><input type="password" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Model</label><input type="text" value="gpt-4" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <button class="px-6 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Save Provider Settings</button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Usage Statistics</h3>
            <div class="space-y-4">
                <div class="flex justify-between py-2 border-b"><span class="text-sm text-gray-600">Tokens Used (Today)</span><span class="font-semibold">45,230</span></div>
                <div class="flex justify-between py-2 border-b"><span class="text-sm text-gray-600">Tokens Used (This Month)</span><span class="font-semibold">1,245,890</span></div>
                <div class="flex justify-between py-2 border-b"><span class="text-sm text-gray-600">Estimated Cost (Today)</span><span class="font-semibold">$2.26</span></div>
                <div class="flex justify-between py-2 border-b"><span class="text-sm text-gray-600">Estimated Cost (This Month)</span><span class="font-semibold">$62.29</span></div>
                <div class="flex justify-between py-2 border-b"><span class="text-sm text-gray-600">API Requests (Today)</span><span class="font-semibold">1,234</span></div>
                <div class="flex justify-between py-2"><span class="text-sm text-gray-600">API Requests (This Month)</span><span class="font-semibold">32,567</span></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Budget Settings</h3>
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Monthly Budget Cap ($)</label><input type="number" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Daily Budget Cap ($)</label><input type="number" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <button class="px-6 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Save Budget</button>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Rate Limiting</h3>
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Max Requests Per Minute</label><input type="number" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Max Tokens Per Request</label><input type="number" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <button class="px-6 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Save Rate Limits</button>
            </div>
        </div>
    </div>
</div>
@endsection
