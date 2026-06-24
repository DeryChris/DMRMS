@extends('layouts.app')

@section('title', 'Announcements - Ghana Armed Forces')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="font-heading font-bold text-3xl text-gaf-green mb-2">Announcements</h1>
    <p class="text-gray-600 mb-8">Stay updated with the latest recruitment news.</p>

    <div class="mb-8 rounded-xl overflow-hidden shadow-lg">
        <img src="{{ asset('assets/images/hero/img4.jpg') }}" alt="Announcements" class="w-full h-48 object-cover">
    </div>

    <div x-data="{ category: 'all' }">
        <div class="flex space-x-2 mb-8 overflow-x-auto pb-2">
            <button @click="category = 'all'" class="px-4 py-2 rounded-full text-sm font-medium transition" x-bind:class="category === 'all' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">All</button>
            <button @click="category = 'general'" class="px-4 py-2 rounded-full text-sm font-medium transition" x-bind:class="category === 'general' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">General</button>
            <button @click="category = 'requirements'" class="px-4 py-2 rounded-full text-sm font-medium transition" x-bind:class="category === 'requirements' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">Requirements</button>
            <button @click="category = 'deadlines'" class="px-4 py-2 rounded-full text-sm font-medium transition" x-bind:class="category === 'deadlines' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">Deadlines</button>
            <button @click="category = 'results'" class="px-4 py-2 rounded-full text-sm font-medium transition" x-bind:class="category === 'results' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">Results</button>
        </div>

        <div class="space-y-4">
            @php
                $announcements = [
                    ['id' => 1, 'cat' => 'general', 'date' => '2026-06-20', 'title' => '2026 Recruitment Exercise Launched', 'excerpt' => 'The Ghana Armed Forces is pleased to announce the commencement of the 2026 recruitment exercise. Applications are now open.'],
                    ['id' => 2, 'cat' => 'requirements', 'date' => '2026-06-18', 'title' => 'Updated Document Requirements', 'excerpt' => 'Please note the updated list of required documents for the application process. All applicants must provide...'],
                    ['id' => 3, 'cat' => 'deadlines', 'date' => '2026-06-15', 'title' => 'Application Deadline Extended', 'excerpt' => 'The application deadline has been extended to July 31, 2026 to allow more applicants to complete their submissions.'],
                    ['id' => 4, 'cat' => 'results', 'date' => '2026-06-10', 'title' => 'Shortlisted Candidates 2025', 'excerpt' => 'The list of shortlisted candidates for the 2025 recruitment cycle has been published. Check your status on the portal.'],
                    ['id' => 5, 'cat' => 'general', 'date' => '2026-06-05', 'title' => 'Important: No Middlemen Policy', 'excerpt' => 'The GAF wishes to remind the public that the recruitment process is free and there are no middlemen. Reporttt any...'],
                ];
            @endphp
            @foreach($announcements as $a)
            <div x-show="category === 'all' || category === '{{ $a['cat'] }}'" x-cloak class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-xs text-gray-400">{{ $a['date'] }}</span>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full"
                        :class="{
                            'bg-blue-100 text-blue-700': '{{ $a['cat'] }}' === 'general',
                            'bg-purple-100 text-purple-700': '{{ $a['cat'] }}' === 'requirements',
                            'bg-orange-100 text-orange-700': '{{ $a['cat'] }}' === 'deadlines',
                            'bg-green-100 text-green-700': '{{ $a['cat'] }}' === 'results'
                        }">
                        {{ ucfirst($a['cat']) }}
                    </span>
                </div>
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-2">{{ $a['title'] }}</h3>
                <p class="text-sm text-gray-600 mb-3">{{ $a['excerpt'] }}</p>
                <a href="#" class="text-gaf-green text-sm font-medium hover:underline">Read More &rarr;</a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
