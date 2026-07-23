@extends('layouts.screening')

@section('title', 'Fitness Test - Ghana Armed Forces')

@section('content')
<div id="screening-old-data" data-application-id="{{ old('application_id') }}" data-search="{{ old('q', session('last_search_q', '')) }}" class="hidden"></div>

<div x-data="{
    q: '{{ old('q', session('last_search_q', '')) }}',
    applicant: null,
    error: '',
    loading: false,
    search() {
        this.error = '';
        this.applicant = null;
        if (!this.q.trim()) return;
        this.loading = true;
        fetch('{{ route('screening.search-applicant') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ q: this.q })
        })
        .then(r => r.json())
        .then(data => {
            this.loading = false;
            if (data.error) { this.error = data.error; }
            else { this.applicant = data; }
        })
        .catch(() => { this.loading = false; this.error = 'Server error.'; });
    }
}" x-init="{{ old('application_id') ? '$nextTick(search)' : '' }}" class="max-w-3xl mx-auto">
    <h1 class="font-heading font-bold text-2xl text-gray-800 mb-6 gradient-border pb-4">Fitness Test</h1>

    <div class="glass-strong rounded-xl shadow-sm p-6 mb-6 gradient-border-left">
        <div class="flex space-x-3">
            <input type="text" x-model="q" @keydown.enter="search()" placeholder="Search by GAF ID or name..." class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
            <button @click="search()" :disabled="loading" class="px-6 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition" :class="loading ? 'opacity-50' : ''">
                <span x-show="!loading">Search</span>
                <span x-show="loading">Searching...</span>
            </button>
        </div>
        <p x-show="error" x-text="error" class="text-red-600 text-sm mt-2"></p>
    </div>

    <div x-show="applicant" x-cloak x-transition>
        <template x-if="applicant">
            <div class="glass-strong rounded-xl shadow-sm p-8 gradient-border-left">
                <div class="flex items-center space-x-4 mb-6 p-4 -mx-8 -mt-8 mb-6 section-gradient-light text-white rounded-t-xl" style="background:linear-gradient(135deg, var(--gaf-green), var(--gaf-dark-green));">
                    <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center text-white font-heading font-bold text-xl" x-text="(applicant.name || '??').split(' ').map(w => w[0]).join('').slice(0,2).toUpperCase()"></div>
                    <div>
                        <h2 class="font-heading font-semibold text-xl text-white" x-text="applicant.name"></h2>
                        <p class="text-sm text-white/70" x-text="applicant.gaf_id"></p>
                        <span x-show="applicant.fitness_score !== null" class="text-xs text-gray-500 mt-1 block">Previous Score: <span x-text="applicant.fitness_score"></span></span>
                    </div>
                </div>

                <form method="POST" action="{{ route('screening.fitness.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="application_id" value="{{ old('application_id') }}" x-model="applicant?.application_id ?? ''">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Run Time (seconds)</label>
                            <input type="number" name="run_time_seconds" value="{{ old('run_time_seconds') }}" min="0" placeholder="e.g. 750" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Push-ups (count)</label>
                            <input type="number" name="push_ups" value="{{ old('push_ups') }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sit-ups (count)</label>
                            <input type="number" name="sit_ups" value="{{ old('sit_ups') }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pull-ups (count)</label>
                            <input type="number" name="pull_ups" value="{{ old('pull_ups') }}" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Shuttle Run (seconds)</label>
                            <input type="number" name="shuttle_run" value="{{ old('shuttle_run') }}" step="0.1" min="0" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
                            <select name="fitness_grade" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                                <option value="">--</option>
                                <option value="a" {{ old('fitness_grade') === 'a' ? 'selected' : '' }}>A (Excellent)</option>
                                <option value="b" {{ old('fitness_grade') === 'b' ? 'selected' : '' }}>B (Good)</option>
                                <option value="c" {{ old('fitness_grade') === 'c' ? 'selected' : '' }}>C (Average)</option>
                                <option value="d" {{ old('fitness_grade') === 'd' ? 'selected' : '' }}>D (Below Average)</option>
                                <option value="f" {{ old('fitness_grade') === 'f' ? 'selected' : '' }}>F (Fail)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Overall Score (0-100)</label>
                        <input type="number" name="fitness_score" value="{{ old('fitness_score') }}" required min="0" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki" placeholder="Enter fitness observations...">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="px-8 py-3 text-white rounded-lg font-semibold transition" style="background:linear-gradient(135deg,#22c55e,#16a34a);">Submit Fitness Results</button>
                </form>
            </div>
        </template>
    </div>
</div>
@endsection
