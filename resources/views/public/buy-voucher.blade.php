@extends('layouts.app')

@section('title', 'Buy Voucher - Ghana Armed Forces')

@php $unsplashPhoto = $unsplashPhoto ?? unsplash_hero(); @endphp

@section('hero')
<div class="relative overflow-hidden" style="min-height:200px;">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $unsplashPhoto['regular_url'] ?? '' }}');"></div>
    <div class="absolute inset-0" style="background:linear-gradient(135deg, rgba(20,83,45,0.9) 0%, rgba(15,47,31,0.85) 70%, rgba(155,34,38,0.75) 100%);"></div>
    <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center">
        <h1 class="font-heading font-bold text-3xl text-white mb-2">Purchase Recruitment Voucher</h1>
        <p class="text-gaf-khaki/80">Get your unique serial number and PIN to begin your application</p>
    </div>
    @if($unsplashPhoto && ($unsplashPhoto['attribution']['name'] ?? '') !== 'Unsplash')
    <div class="absolute bottom-2 right-4 z-20 text-xs text-white/40">
        Photo by <a href="{{ ($unsplashPhoto['attribution']['link'] ?? '#') }}?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">{{ $unsplashPhoto['attribution']['name'] ?? 'Unknown' }}</a> on <a href="https://unsplash.com/?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">Unsplash</a>
    </div>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    @php $hasActiveCycles = $activeCycles->isNotEmpty(); @endphp

    @if(!$hasActiveCycles)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6 text-center">
        <svg class="w-10 h-10 text-amber-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <h2 class="font-heading font-bold text-lg text-amber-800 mb-1">No Active Recruitment Cycles</h2>
        <p class="text-amber-700 text-sm">There is no active recruitment cycle at the moment. Voucher purchases are unavailable until a new cycle opens. Please check back later or follow our announcements.</p>
        <a href="{{ route('landing') }}" class="inline-block mt-3 text-sm text-amber-700 hover:text-amber-900 underline font-medium">Return to Homepage</a>
    </div>
    @endif

    <form method="POST" action="{{ route('voucher.purchase') }}" class="bg-white/90 glass-strong rounded-xl shadow-md border border-gray-200 p-8">
        @csrf

        {{-- Cycle Selection --}}
        <div class="mb-6" x-data="{}">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Recruitment Cycle <span class="text-red-500">*</span></label>
            @error('cycle_id') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror
            @if($activeCycles->isEmpty())
                <div class="bg-gray-50 rounded-lg p-4 text-center text-gray-400 text-sm">No active recruitment cycles available for purchase.</div>
            @else
                <div class="space-y-3">
                    @foreach($activeCycles as $cycle)
                    @php $req = $cycle->requirements ?? []; @endphp
                    @php
                        $tierColors = ['bg-gaf-green', 'bg-gaf-khaki', 'bg-gaf-red'];
                        $tierIdx = $loop->index % 3;
                        $tierClass = $tierColors[$tierIdx];
                    @endphp
                    <label class="block border border-gray-200 rounded-lg overflow-hidden cursor-pointer hover:border-gaf-khaki transition has-[:checked]:border-gaf-khaki has-[:checked]:ring-2 has-[:checked]:ring-gaf-khaki">
                        <div class="card-gradient-header {{ $tierClass }}">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-white">{{ $cycle->name }}</span>
                                <span class="text-white font-bold text-lg">GHS {{ number_format($cycle->voucher_price ?? config('recruitment.voucher_costs.regular', 50), 2) }}</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="flex items-start">
                                <input type="radio" name="cycle_id" value="{{ $cycle->id }}" {{ (string) $selectedCycleId === (string) $cycle->id ? 'checked' : '' }} class="mt-1 text-gaf-khaki focus:ring-gaf-khaki" required>
                                <div class="ml-3 flex-1">
                                    <p class="text-xs text-gray-400 mt-0.5">Code: {{ $cycle->cycle_code }} &middot; Deadline: {{ $cycle->application_deadline?->format('M d, Y H:i') }}</p>
                                @if(!empty($req))
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @if(!empty($req['min_age']))<span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded">Age {{ $req['min_age'] }}-{{ $req['max_age'] ?? 'N/A' }}</span>@endif
                                    @if(!empty($req['education_levels']))<span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded">{{ implode('/', $req['education_levels']) }}</span>@endif
                                    <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded">{{ number_format($cycle->total_vacancies) }} vacancies</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    </label>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Personal Info --}}
        <div class="border-t pt-6 mb-6">
            <h2 class="font-heading font-semibold text-base text-gray-800 mb-4">Your Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php $f = 'purchaser_name'; @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="{{ $f }}" value="{{ old($f) }}" required class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}" placeholder="John Doe">
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @php $f = 'purchaser_email'; @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="{{ $f }}" value="{{ old($f) }}" required class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}" placeholder="john@example.com">
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @php $f = 'purchaser_phone'; @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                    <input type="tel" name="{{ $f }}" value="{{ old($f) }}" required class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}" placeholder="0244000000">
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Payment --}}
        <div class="border-t pt-6 mb-6">
            <h2 class="font-heading font-semibold text-base text-gray-800 mb-4">Payment Details</h2>
            <p class="text-xs text-gray-400 mb-4">Select your preferred payment method and enter the transaction reference.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php $f = 'payment_method'; @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                    <select name="{{ $f }}" required class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                        <option value="">— Select —</option>
                        @foreach($paymentMethods as $val => $label)
                        <option value="{{ $val }}" {{ old($f) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <button type="submit" class="w-full bg-gaf-green text-white py-4 rounded-lg font-heading font-bold text-lg hover:bg-gaf-dark-green transition shadow-lg" {{ $activeCycles->isEmpty() ? 'disabled' : '' }}>
            Purchase Voucher
        </button>

        <p class="text-xs text-gray-400 text-center mt-3">By purchasing, you agree to the terms and conditions of the Ghana Armed Forces recruitment process.</p>
    </form>

    {{-- Voucher Lookup Section --}}
    <div class="mt-8" x-data="{ showLookup: false, showLookupModal: {{ isset($lookupResults) ? 'true' : 'false' }}, lookupEmail: '{{ old('lookup_email', '') }}' }">
        <button @click="showLookup = !showLookup" class="w-full text-center text-sm text-gaf-green hover:text-gaf-dark-green font-semibold py-2 transition">
            <span x-text="showLookup ? '▼ Hide' : '▶ Already purchased? Check your voucher'"></span>
        </button>

        <div x-show="showLookup" x-cloak x-transition class="mt-4 bg-gray-50 rounded-xl border border-gray-200 p-6">
            <h3 class="font-heading font-semibold text-sm text-gray-700 mb-3">Look up your purchased voucher</h3>
            <form method="POST" action="{{ route('voucher.lookup') }}" class="flex gap-3 flex-wrap">
                @csrf
                @php $f = 'lookup_email'; @endphp
                <div class="flex-1 min-w-[200px]">
                    <input type="email" name="{{ $f }}" x-model="lookupEmail" placeholder="Enter your email address" required class="w-full border rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="bg-gaf-green text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition whitespace-nowrap">Check</button>
            </form>
        </div>

        {{-- Results Modal Overlay --}}
        <div x-show="showLookupModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5);">
            <div @click.away="showLookupModal = false" class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-heading font-semibold text-lg text-gray-800">Your Voucher{{ isset($lookupResults) && $lookupResults->count() !== 1 ? 's' : '' }}</h3>
                    <button @click="showLookupModal = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>

                @isset($lookupResults)
                    @if($lookupResults->isEmpty())
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-gray-500 text-sm">No vouchers found for <strong>{{ old('lookup_email') }}</strong>.</p>
                            <p class="text-gray-400 text-xs mt-1">Make sure you enter the email used during purchase.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($lookupResults as $v)
                            <div class="border border-gray-200 rounded-lg p-4 {{ $v->status === 'available' ? 'border-green-200 bg-green-50/50' : ($v->status === 'used' ? 'border-blue-200 bg-blue-50/50' : 'border-gray-200') }}">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-heading font-semibold text-sm text-gray-700">{{ $v->cycle->name ?? 'N/A' }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                        {{ $v->status === 'available' ? 'bg-green-100 text-green-700' : ($v->status === 'used' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                                        {{ ucfirst($v->status) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                                    <div><span class="text-gray-400">Serial:</span> <span class="font-mono font-semibold text-gray-700">{{ $v->serial_number }}</span></div>
                                    <div><span class="text-gray-400">PIN:</span> <span class="font-mono font-semibold text-gray-700">{{ $v->pin_code }}</span></div>
                                    <div><span class="text-gray-400">Purchased:</span> <span class="text-gray-600">{{ $v->purchased_at?->format('d M Y') }}</span></div>
                                    <div><span class="text-gray-400">Expires:</span> <span class="text-gray-600">{{ $v->expires_at?->format('d M Y') }}</span></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                @endisset

                <div class="mt-5 text-center">
                    <button @click="showLookupModal = false" class="text-sm text-gray-500 hover:text-gray-700 underline">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
