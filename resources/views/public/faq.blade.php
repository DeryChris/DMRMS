@extends('layouts.app')

@section('title', 'FAQ - Ghana Armed Forces')

@php $unsplashPhoto = $unsplashPhoto ?? unsplash_hero(); @endphp

@section('hero')
<div class="relative overflow-hidden" style="min-height:200px;">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $unsplashPhoto['regular_url'] ?? '' }}');"></div>
    <div class="absolute inset-0" style="background:linear-gradient(135deg, rgba(20,83,45,0.9) 0%, rgba(15,47,31,0.85) 70%, rgba(155,34,38,0.75) 100%);"></div>
    <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center">
        <h1 class="font-heading font-bold text-3xl text-white mb-2">Frequently Asked Questions</h1>
        <p class="text-gaf-khaki/80">Find answers to common questions about the recruitment process.</p>
    </div>
    @if($unsplashPhoto && ($unsplashPhoto['attribution']['name'] ?? '') !== 'Unsplash')
    <div class="absolute bottom-2 right-4 z-20 text-xs text-white/40">
        Photo by <a href="{{ ($unsplashPhoto['attribution']['link'] ?? '#') }}?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">{{ $unsplashPhoto['attribution']['name'] ?? 'Unknown' }}</a> on <a href="https://unsplash.com/?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">Unsplash</a>
    </div>
    @endif
</div>
<x-section-divider type="peak" color="#14532d" />
@endsection

@section('content')

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div x-data="{ search: '', category: 'all' }">
        <div class="flex flex-col sm:flex-row gap-4 mb-8">
            <div class="relative flex-1 bg-white/90 glass-strong rounded-lg">
                <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" placeholder="Search questions..." class="w-full pl-10 pr-4 py-3 bg-transparent border-0 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
            </div>
        </div>
        <div class="flex space-x-2 mb-6 overflow-x-auto pb-2">
            <button @click="category = 'all'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" x-bind:class="category === 'all' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">All</button>
            <button @click="category = 'general'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" x-bind:class="category === 'general' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">General</button>
            <button @click="category = 'eligibility'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" x-bind:class="category === 'eligibility' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">Eligibility</button>
            <button @click="category = 'application'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" x-bind:class="category === 'application' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">Application</button>
            <button @click="category = 'documents'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" x-bind:class="category === 'documents' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">Documents</button>
            <button @click="category = 'screening'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" x-bind:class="category === 'screening' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">Screening</button>
            <button @click="category = 'results'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" x-bind:class="category === 'results' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">Results</button>
        </div>

        <div class="space-y-3">
            @forelse($faqs as $faq)
            <div x-data="{ open: false }"
                 x-show="(category === 'all' || category === '{{ $faq->category }}') && (search === '' || '{{ $faq->question }}'.toLowerCase().includes(search.toLowerCase()) || '{{ $faq->answer }}'.toLowerCase().includes(search.toLowerCase()))"
                 x-cloak
                 class="bg-white border border-gray-200 rounded-lg overflow-hidden gradient-border-left">
                <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gradient-to-r hover:from-green-50 hover:to-transparent transition">
                    <span class="text-sm font-medium text-gray-800">{{ $faq->question }}</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse x-cloak class="px-6 pb-4">
                    <p class="text-sm text-gray-600">{{ $faq->answer }}</p>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-400">
                <p class="text-sm font-medium">No FAQs available yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
