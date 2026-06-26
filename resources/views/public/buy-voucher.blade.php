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

    @if($errors->any())
    <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm mb-6">
        @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('voucher.purchase') }}" class="bg-white/90 glass-strong rounded-xl shadow-md border border-gray-200 p-8">
        @csrf

        {{-- Cycle Selection --}}
        <div class="mb-6" x-data="{}">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Recruitment Cycle</label>
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
                                <input type="radio" name="cycle_id" value="{{ $cycle->id }}" class="mt-1 text-gaf-khaki focus:ring-gaf-khaki" required>
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="purchaser_name" value="{{ old('purchaser_name') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki" placeholder="John Doe">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="purchaser_email" value="{{ old('purchaser_email') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki" placeholder="john@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" name="purchaser_phone" value="{{ old('purchaser_phone') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki" placeholder="0244000000">
                </div>
            </div>
        </div>

        {{-- Payment --}}
        <div class="border-t pt-6 mb-6">
            <h2 class="font-heading font-semibold text-base text-gray-800 mb-4">Payment Details</h2>
            <p class="text-xs text-gray-400 mb-4">Select your preferred payment method and enter the transaction reference.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select name="payment_method" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki">
                        <option value="">— Select —</option>
                        @foreach($paymentMethods as $val => $label)
                        <option value="{{ $val }}" {{ old('payment_method') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Reference</label>
                    <input type="text" name="payment_reference" value="{{ old('payment_reference') }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki focus:border-gaf-khaki" placeholder="Optional reference number">
                </div>
            </div>
        </div>

        <button type="submit" class="w-full bg-gaf-green text-white py-4 rounded-lg font-heading font-bold text-lg hover:bg-gaf-dark-green transition shadow-lg" {{ $activeCycles->isEmpty() ? 'disabled' : '' }}>
            Purchase Voucher
        </button>

        <p class="text-xs text-gray-400 text-center mt-3">By purchasing, you agree to the terms and conditions of the Ghana Armed Forces recruitment process.</p>
    </form>
</div>
@endsection
