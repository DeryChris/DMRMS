@extends('layouts.app')

@section('title', 'Announcements - Ghana Armed Forces')

@php $unsplashPhoto = $unsplashPhoto ?? unsplash_hero(); @endphp

@section('hero')
<div class="relative overflow-hidden" style="min-height:200px;">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $unsplashPhoto['regular_url'] ?? '' }}');"></div>
    <div class="absolute inset-0" style="background:linear-gradient(135deg, rgba(20,83,45,0.9) 0%, rgba(15,47,31,0.85) 70%, rgba(155,34,38,0.75) 100%);"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 text-center py-14">
        <h1 class="text-4xl font-heading font-bold text-white mb-2">News &amp; Announcements</h1>
        <p class="text-gaf-khaki text-lg">Stay updated with the latest recruitment news from the Ghana Armed Forces.</p>
    </div>
    @if($unsplashPhoto && ($unsplashPhoto['attribution']['name'] ?? '') !== 'Unsplash')
    <div class="absolute bottom-2 right-4 z-20 text-xs text-white/40">
        Photo by <a href="{{ ($unsplashPhoto['attribution']['link'] ?? '#') }}?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">{{ $unsplashPhoto['attribution']['name'] ?? 'Unknown' }}</a> on <a href="https://unsplash.com/?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">Unsplash</a>
    </div>
    @endif
</div>
<x-section-divider type="wave" color="#14532d" />
@endsection

@section('content')

<section class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-wrap gap-2 mb-8">
        <a href="{{ route('announcements') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ !request('category') ? 'bg-gaf-green text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">All</a>
        @foreach($categories as $cat)
            <a href="{{ route('announcements', ['category' => $cat]) }}" class="px-4 py-2 rounded-full text-sm font-medium {{ request('category') === $cat ? 'bg-gaf-green text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">{{ ucfirst($cat) }}</a>
        @endforeach
    </div>

    @if($featured && !request('category'))
    <div class="mb-12">
        <a href="{{ route('announcements.detail', $featured->id) }}" class="group block bg-white/90 glass-strong rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition gradient-border">
            <div class="md:flex">
                @if($featured->featured_image)
                <div class="md:w-1/2 h-64 md:h-auto bg-gray-100">
                    <img src="{{ asset('storage/' . $featured->featured_image) }}" alt="{{ $featured->title }}" class="w-full h-full object-cover">
                </div>
                @endif
                <div class="p-8 md:w-1/2 flex flex-col justify-center">
                    <span class="text-xs font-semibold text-gaf-green uppercase tracking-wider">{{ $featured->category }}</span>
                    <h2 class="text-2xl font-heading font-bold text-gray-900 mt-2 group-hover:text-gaf-green transition">{{ $featured->title }}</h2>
                    <p class="text-gray-500 mt-3 text-sm leading-relaxed">{{ $featured->excerpt ?? Str::limit(strip_tags($featured->content), 200) }}</p>
                    <div class="flex items-center text-xs text-gray-400 mt-4">
                        <span>{{ $featured->author ? $featured->author . ' · ' : '' }}{{ $featured->published_at?->format('F j, Y') }}</span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($announcements as $a)
        <a href="{{ route('announcements.detail', $a->id) }}" class="card-hover gradient-border-left rounded-xl overflow-hidden">
            @if($a->featured_image)
            <div class="h-48 bg-gray-100 overflow-hidden">
                <img src="{{ asset('storage/' . $a->featured_image) }}" alt="{{ $a->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
            </div>
            @endif
            <div class="p-5">
                <div class="flex items-center space-x-2 mb-2">
                    <span class="text-xs font-semibold text-gaf-green uppercase">{{ $a->category }}</span>
                    <span class="text-xs text-gray-400">{{ $a->published_at?->format('M j, Y') }}</span>
                </div>
                <h3 class="font-heading font-semibold text-gray-900 group-hover:text-gaf-green transition">{{ $a->title }}</h3>
                <p class="text-sm text-gray-500 mt-2 line-clamp-3">{{ $a->excerpt ?? Str::limit(strip_tags($a->content), 150) }}</p>
                <div class="flex items-center justify-between mt-4">
                    @if($a->author)
                    <span class="text-xs text-gray-400">By {{ $a->author }}</span>
                    @endif
                    <span class="text-xs text-gaf-green font-medium group-hover:underline">Read More &rarr;</span>
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-16">
            <x-illustration name="empty" class="mx-auto w-48 opacity-50" />
            <p class="text-gray-400 text-lg font-medium mt-4">No announcements yet</p>
            <p class="text-gray-400 text-sm">Check back later for updates.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $announcements->links() }}
    </div>
</section>
@endsection
