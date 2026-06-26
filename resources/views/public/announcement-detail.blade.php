@extends('layouts.app')

@section('title', $announcement->title . ' - Ghana Armed Forces')

@php $unsplashPhoto = $unsplashPhoto ?? unsplash_hero(); @endphp

@section('hero')
<div class="relative overflow-hidden" style="min-height:200px;">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $unsplashPhoto['regular_url'] ?? '' }}');"></div>
    <div class="absolute inset-0" style="background:linear-gradient(135deg, rgba(20,83,45,0.9) 0%, rgba(15,47,31,0.85) 70%, rgba(155,34,38,0.75) 100%);"></div>
    <div class="relative z-10 max-w-4xl mx-auto px-4 py-14">
        <a href="{{ route('announcements') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-white/20 text-white text-sm font-medium hover:bg-white/30 transition mb-4">&larr; Back to Announcements</a>
        <div class="mb-2">
            <span class="text-xs font-semibold text-gaf-khaki uppercase tracking-wider">{{ $announcement->category }}</span>
        </div>
        <h1 class="text-4xl font-heading font-bold text-white mb-2">{{ $announcement->title }}</h1>
        <div class="flex items-center text-sm text-white/70 space-x-4">
            @if($announcement->author)
            <span>By <strong>{{ $announcement->author }}</strong></span>
            <span>·</span>
            @endif
            <span>{{ $announcement->published_at?->format('F j, Y') }}</span>
        </div>
    </div>
    @if($unsplashPhoto && ($unsplashPhoto['attribution']['name'] ?? '') !== 'Unsplash')
    <div class="absolute bottom-2 right-4 z-20 text-xs text-white/40">
        Photo by <a href="{{ ($unsplashPhoto['attribution']['link'] ?? '#') }}?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">{{ $unsplashPhoto['attribution']['name'] ?? 'Unknown' }}</a> on <a href="https://unsplash.com/?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">Unsplash</a>
    </div>
    @endif
</div>
@endsection

@section('content')
<article class="max-w-4xl mx-auto px-4 py-10">

    <div class="mb-2">
        <span class="text-xs font-semibold text-gaf-green uppercase tracking-wider">{{ $announcement->category }}</span>
    </div>
    <h1 class="text-4xl font-heading font-bold text-gray-900 mb-4">{{ $announcement->title }}</h1>

    <div class="flex items-center text-sm text-gray-500 space-x-4 mb-8">
        @if($announcement->author)
        <span>By <strong>{{ $announcement->author }}</strong></span>
        <span>·</span>
        @endif
        <span>{{ $announcement->published_at?->format('F j, Y') }}</span>
        @if($announcement->tags)
        <span>·</span>
        <span class="flex space-x-1">
            @foreach($announcement->tags as $tag)
            <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs">#{{ $tag }}</span>
            @endforeach
        </span>
        @endif
    </div>

    @if($announcement->featured_image)
    <div class="rounded-2xl overflow-hidden mb-8 shadow-sm relative">
        <img src="{{ asset('storage/' . $announcement->featured_image) }}" alt="{{ $announcement->title }}" class="w-full h-80 md:h-96 object-cover">
        <div class="absolute inset-0" style="background: linear-gradient(0deg, rgba(20,83,45,0.4) 0%, transparent 60%);"></div>
    </div>
    @endif

    @if($announcement->excerpt)
    <div class="text-lg text-gray-600 italic border-l-4 border-gaf-khaki pl-6 mb-8">{{ $announcement->excerpt }}</div>
    @endif

    <div class="bg-white glass-strong rounded-xl p-8 md:p-10 shadow-inner prose prose-lg max-w-none prose-headings:text-gray-900 prose-p:text-gray-700 prose-a:text-gaf-green">
        {!! $announcement->content !!}
    </div>

    @if($announcement->media_gallery && count($announcement->media_gallery) > 0)
    <div class="mt-10">
        <h3 class="font-heading font-semibold text-xl text-gray-800 mb-4">Gallery</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($announcement->media_gallery as $media)
            <a href="{{ asset('storage/' . $media['url']) }}" target="_blank" class="block rounded-lg overflow-hidden bg-gray-100 h-40 hover:opacity-90 transition">
                <img src="{{ asset('storage/' . $media['url']) }}" alt="{{ $media['caption'] ?? '' }}" class="w-full h-full object-cover">
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($related->count() > 0)
    <div class="mt-16 pt-8 border-t border-gray-200">
        <h3 class="font-heading font-semibold text-xl text-gray-800 mb-6">Related Articles</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($related as $r)
            <a href="{{ route('announcements.detail', $r->id) }}" class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition">
                @if($r->featured_image)
                <div class="h-36 bg-gray-100 overflow-hidden">
                    <img src="{{ asset('storage/' . $r->featured_image) }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition">
                </div>
                @endif
                <div class="p-4">
                    <span class="text-xs text-gaf-green font-semibold uppercase">{{ $r->category }}</span>
                    <h4 class="font-medium text-gray-900 mt-1 group-hover:text-gaf-green transition">{{ $r->title }}</h4>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</article>
@endsection
