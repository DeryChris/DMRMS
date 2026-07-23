@extends('layouts.app')

@section('title', 'Recruitment Portal - Ghana Armed Forces')

@php $unsplashPhoto = $unsplashPhoto ?? unsplash_hero(); @endphp

@section('hero')
<div class="relative overflow-hidden" style="min-height:200px;">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $unsplashPhoto['regular_url'] ?? '' }}');"></div>
    <div class="absolute inset-0" style="background:linear-gradient(135deg, rgba(20,83,45,0.9) 0%, rgba(15,47,31,0.85) 70%, rgba(155,34,38,0.75) 100%);"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center">
        <div class="portal-badge mx-auto mb-4 w-fit">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            Active Recruitment Exercises
        </div>
        <h1 class="font-heading font-bold text-3xl md:text-4xl text-white mb-2">Current Recruitment Cycles</h1>
        <p class="text-gaf-khaki/80 text-lg">Review open positions and start your application journey</p>
    </div>
    @if($unsplashPhoto && ($unsplashPhoto['attribution']['name'] ?? '') !== 'Unsplash')
    <div class="absolute bottom-2 right-4 z-20 text-xs text-white/40">
        Photo by <a href="{{ ($unsplashPhoto['attribution']['link'] ?? '#') }}?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">{{ $unsplashPhoto['attribution']['name'] ?? 'Unknown' }}</a> on <a href="https://unsplash.com/?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">Unsplash</a>
    </div>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    @forelse($activeCycles as $cycle)
    @php $req = $cycle->requirements ?? []; @endphp
    <div class="bg-white/90 glass-strong rounded-xl shadow-md border border-gray-200 overflow-hidden mb-6 hover:shadow-lg transition hover:-translate-y-0.5 duration-200">
        <div class="card-gradient-header flex items-center justify-between">
            <div>
                <h2 class="font-heading font-bold text-xl text-white">{{ $cycle->name }}</h2>
                <p class="text-gaf-khaki/80 text-sm">Code: {{ $cycle->cycle_code }} &middot; {{ number_format($cycle->total_vacancies) }} vacancies</p>
            </div>
            <span class="bg-gaf-khaki text-gaf-dark-green text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">Active</span>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="flex items-center space-x-3 text-sm">
                    <svg class="w-5 h-5 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <div>
                        <p class="font-medium text-gray-900">Application Period</p>
                        <p class="text-gray-500">{{ $cycle->start_date?->format('M d, Y') }} — {{ $cycle->end_date?->format('M d, Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3 text-sm">
                    <svg class="w-5 h-5 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="font-medium text-gray-900">Deadline</p>
                        <p class="text-gray-500">{{ $cycle->application_deadline?->format('M d, Y \a\t H:i') }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3 text-sm">
                    <svg class="w-5 h-5 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <div>
                        <p class="font-medium text-gray-900">Applications</p>
                        <p class="text-gray-500">{{ number_format($cycle->applications_count) }} submitted</p>
                    </div>
                </div>
            </div>

            {{-- Eligibility Summary --}}
            @if($req)
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-heading font-semibold text-sm text-gray-700 mb-3">Eligibility Requirements</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wide">Age</p>
                        <p class="font-medium">{{ $req['min_age'] ?? 'N/A' }} — {{ $req['max_age'] ?? 'N/A' }} years</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wide">Height (Male)</p>
                        <p class="font-medium">{{ $req['min_height_male'] ?? 'N/A' }}m minimum</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wide">Height (Female)</p>
                        <p class="font-medium">{{ $req['min_height_female'] ?? 'N/A' }}m minimum</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs uppercase tracking-wide">Nationality</p>
                        <p class="font-medium">{{ $req['nationality'] ?? 'N/A' }}</p>
                    </div>
                </div>
                @if(!empty($req['education_levels']))
                <div class="mt-2">
                    <p class="text-gray-400 text-xs uppercase tracking-wide">Education</p>
                    <div class="flex flex-wrap gap-1.5 mt-1">
                        @foreach($req['education_levels'] as $edu)
                        <span class="bg-gaf-green/10 text-gaf-green text-xs font-medium px-2 py-0.5 rounded-full">{{ $edu }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                @if(!empty($req['marital_status']))
                <div class="mt-2">
                    <p class="text-gray-400 text-xs uppercase tracking-wide">Marital Status</p>
                    <p class="font-medium">{{ is_array($req['marital_status']) ? implode(', ', $req['marital_status']) : $req['marital_status'] }}</p>
                </div>
                @endif
            </div>
            @endif

            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('voucher.buy', ['cycle_id' => $cycle->id]) }}" class="text-gaf-green hover:text-gaf-dark-green text-sm font-medium hover:underline">Buy Voucher &rarr;</a>
                    <a href="{{ route('eligibility.checker') }}" class="text-gaf-green hover:text-gaf-dark-green text-sm font-medium hover:underline">Check eligibility &rarr;</a>
                    <a href="{{ route('voucher.buy', ['cycle_id' => $cycle->id]) }}" class="inline-block bg-gaf-green text-white px-6 py-3 rounded-lg font-heading font-bold text-sm hover:bg-gaf-dark-green transition shadow">Apply Now</a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-16 bg-white/90 glass-strong rounded-xl shadow-sm border border-gray-200">
        <x-illustration name="empty" class="mx-auto w-48 opacity-50" />
        <h2 class="font-heading font-bold text-xl text-gray-500 mb-2 mt-4">No Active Campaigns</h2>
        <p class="text-gray-400 text-sm">There are currently no open recruitment exercises. Check back later or follow our announcements.</p>
        <a href="{{ route('announcements') }}" class="inline-block mt-4 text-gaf-green hover:underline text-sm font-medium">View Announcements &rarr;</a>
    </div>
    @endforelse
</div>
@endsection