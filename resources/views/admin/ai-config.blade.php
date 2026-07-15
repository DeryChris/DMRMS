@extends('layouts.admin')

@section('title', 'AI & Automation Configuration - Ghana Armed Forces')

@section('content')
<div x-data="{ aiEnabled: {{ $aiSettings['ai_enabled'] ? 'true' : 'false' }}, provider: '{{ $aiSettings['provider'] }}' }" class="space-y-6">
    <h1 class="font-heading font-bold text-2xl text-gray-800 gradient-border pb-4">AI & Automation Configuration</h1>

    <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
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

    <form method="POST" action="{{ route('admin.ai-config.save') }}" class="space-y-6">
        @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Provider Settings</h3>
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">AI Provider</label>
                    <select name="provider" x-model="provider" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        <option value="openai" {{ $aiSettings['provider'] === 'openai' ? 'selected' : '' }}>OpenAI</option>
                        <option value="anthropic" {{ $aiSettings['provider'] === 'anthropic' ? 'selected' : '' }}>Anthropic</option>
                        <option value="google" {{ $aiSettings['provider'] === 'google' ? 'selected' : '' }}>Google AI</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Model</label><input type="text" name="model" value="{{ $aiSettings['model'] }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div class="flex items-center space-x-2 text-sm">
                    <span class="w-2.5 h-2.5 rounded-full {{ $aiHealth ?? true ? 'bg-green-500' : 'bg-red-500' }} inline-block"></span>
                    <span class="text-gray-600">{{ $aiHealth ?? true ? 'Connected' : 'Disconnected' }}</span>
                </div>
            </div>
        </div>

        <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Usage Statistics</h3>
            <div class="space-y-4">
                <div class="flex justify-between py-2 border-b"><span class="text-sm text-gray-600">Tokens Used (Today)</span><span class="font-semibold">{{ number_format($aiStats->tokens_today ?? 0) }}</span></div>
                <div class="flex justify-between py-2 border-b"><span class="text-sm text-gray-600">Tokens Used (This Month)</span><span class="font-semibold">{{ number_format($aiStats->tokens_month ?? 0) }}</span></div>
                <div class="flex justify-between py-2 border-b"><span class="text-sm text-gray-600">Tokens Used (Total)</span><span class="font-semibold">{{ number_format($aiStats->tokens_total ?? 0) }}</span></div>
                <div class="flex justify-between py-2 border-b"><span class="text-sm text-gray-600">API Requests (Today)</span><span class="font-semibold">{{ number_format($aiStats->requests_today ?? 0) }}</span></div>
                <div class="flex justify-between py-2"><span class="text-sm text-gray-600">API Requests (This Month)</span><span class="font-semibold">{{ number_format($aiStats->requests_month ?? 0) }}</span></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Budget Settings</h3>
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Monthly Budget Cap ($)</label><input type="number" name="monthly_budget_cap" value="{{ $aiSettings['monthly_budget_cap'] }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Daily Budget Cap ($)</label><input type="number" name="daily_budget_cap" value="{{ $aiSettings['daily_budget_cap'] }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
            </div>
        </div>
        <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Rate Limiting</h3>
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Max Requests Per Minute</label><input type="number" name="max_requests_per_minute" value="{{ $aiSettings['max_requests_per_minute'] }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Max Tokens Per Request</label><input type="number" name="max_tokens_per_request" value="{{ $aiSettings['max_tokens_per_request'] }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Auto-Decision</h3>
            <p class="text-xs text-gray-500 mb-4">Automatically process final decisions when screening is completed.</p>
            <div class="space-y-4">
                <label class="flex items-center justify-between">
                    <div><p class="text-sm font-medium text-gray-800">Auto Final Decision</p><p class="text-xs text-gray-500">Run scoring and assignment immediately after screening</p></div>
                    <input type="hidden" name="auto_final_decision" value="0">
                    <input type="checkbox" name="auto_final_decision" value="1" {{ ($aiSettings['auto_final_decision'] ?? true) ? 'checked' : '' }} class="w-5 h-5 text-gaf-green border-gray-300 rounded focus:ring-gaf-khaki">
                </label>
            </div>
        </div>

        <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Auto-Recruit</h3>
            <p class="text-xs text-gray-500 mb-4">Automatically recruit selected candidates after a configurable delay.</p>
            <div class="space-y-4">
                <label class="flex items-center justify-between">
                    <div><p class="text-sm font-medium text-gray-800">Auto-Recruit</p><p class="text-xs text-gray-500">Send "selected" notification first, then auto-recruit after delay</p></div>
                    <input type="hidden" name="auto_recruit" value="0">
                    <input type="checkbox" name="auto_recruit" value="1" {{ ($aiSettings['auto_recruit'] ?? false) ? 'checked' : '' }} class="w-5 h-5 text-gaf-green border-gray-300 rounded focus:ring-gaf-khaki">
                </label>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Enrollment Delay (days)</label>
                    <input type="number" name="enrollment_delay_days" value="{{ $aiSettings['enrollment_delay_days'] ?? 14 }}" min="1" max="365" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Training Battalion</label>
                    <input type="text" name="default_training_battalion" value="{{ $aiSettings['default_training_battalion'] ?? 'GAF Training Depot' }}" maxlength="100" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Global Scoring Weights</h3>
            <p class="text-xs text-gray-500 mb-4">Default weights for composite score calculation. Per-cycle overrides can be set in the cycle editor.</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Medical</label>
                    <input type="number" name="scoring_weights_medical" value="{{ $aiSettings['scoring_weights_medical'] ?? 0.40 }}" step="0.01" min="0" max="1" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Interview</label>
                    <input type="number" name="scoring_weights_interview" value="{{ $aiSettings['scoring_weights_interview'] ?? 0.30 }}" step="0.01" min="0" max="1" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fitness</label>
                    <input type="number" name="scoring_weights_fitness" value="{{ $aiSettings['scoring_weights_fitness'] ?? 0.20 }}" step="0.01" min="0" max="1" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Eligibility</label>
                    <input type="number" name="scoring_weights_eligibility" value="{{ $aiSettings['scoring_weights_eligibility'] ?? 0.10 }}" step="0.01" min="0" max="1" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Weights should sum to 1.0. Each candidate's scores are multiplied by these weights to compute a composite score.</p>
        </div>

        <div class="glass-strong rounded-xl shadow-sm p-6 gradient-border-left">
            <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Reserve List</h3>
            <p class="text-xs text-gray-500 mb-4">Configure how the reserve list works and auto-promotion.</p>
            <div class="space-y-4">
                <label class="flex items-center justify-between">
                    <div><p class="text-sm font-medium text-gray-800">Auto-Promote from Reserve</p><p class="text-xs text-gray-500">Automatically promote reserve candidates when vacancies open</p></div>
                    <input type="hidden" name="auto_promote_reserve" value="0">
                    <input type="checkbox" name="auto_promote_reserve" value="1" {{ ($aiSettings['auto_promote_reserve'] ?? false) ? 'checked' : '' }} class="w-5 h-5 text-gaf-green border-gray-300 rounded focus:ring-gaf-khaki">
                </label>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reserve Ratio</label>
                    <p class="text-xs text-gray-400 mb-1">Percentage of vacancies allocated to reserve list (e.g. 0.20 = 20%)</p>
                    <input type="number" name="reserve_ratio" value="{{ $aiSettings['reserve_ratio'] ?? 0.20 }}" step="0.01" min="0" max="1" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="px-8 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition shadow-sm">Save All Settings</button>
    </div>
    </form>
</div>
@endsection
