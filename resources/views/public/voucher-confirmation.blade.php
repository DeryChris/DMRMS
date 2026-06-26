@extends('layouts.app')

@section('title', 'Voucher Purchased - Ghana Armed Forces')

@php $unsplashPhoto = $unsplashPhoto ?? unsplash_hero(); @endphp

@section('hero')
<div class="relative overflow-hidden" style="min-height:200px;">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $unsplashPhoto['regular_url'] ?? '' }}');"></div>
    <div class="absolute inset-0" style="background:linear-gradient(135deg, rgba(20,83,45,0.9) 0%, rgba(15,47,31,0.85) 70%, rgba(155,34,38,0.75) 100%);"></div>
    <div class="relative z-10 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center">
        <x-illustration name="success" class="mx-auto w-16 text-white mb-4" />
        <h1 class="font-heading font-bold text-3xl text-white mb-2">Voucher Purchased Successfully!</h1>
        <p class="text-gaf-khaki/80">Use the details below to complete your registration.</p>
    </div>
    @if($unsplashPhoto && ($unsplashPhoto['attribution']['name'] ?? '') !== 'Unsplash')
    <div class="absolute bottom-2 right-4 z-20 text-xs text-white/40">
        Photo by <a href="{{ ($unsplashPhoto['attribution']['link'] ?? '#') }}?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">{{ $unsplashPhoto['attribution']['name'] ?? 'Unknown' }}</a> on <a href="https://unsplash.com/?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">Unsplash</a>
    </div>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    @if (session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg text-sm mb-6">{{ session('success') }}</div>
    @endif

    <div class="bg-white/90 glass-strong rounded-xl shadow-md border border-gray-200 p-8 mb-6">
        <div class="bg-gaf-green rounded-lg p-6 text-center mb-6">
            <p class="text-gaf-khaki/70 text-xs uppercase tracking-wide font-medium mb-2">Your Voucher Credentials</p>
            <div class="space-y-3">
                <div class="flex items-center justify-between bg-white/10 rounded-lg px-4 py-3">
                    <div class="text-left">
                        <p class="text-gaf-khaki/70 text-xs uppercase tracking-wide font-medium">Serial Number</p>
                        <p id="serial-number" class="text-white font-heading font-bold text-xl tracking-widest mt-0.5">{{ $voucher->serial_number }}</p>
                    </div>
                    <button onclick="copyToClipboard('serial-number', this)" class="text-gaf-khaki hover:text-white transition p-2 rounded-lg hover:bg-white/10" title="Copy Serial Number">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                </div>
                <div class="flex items-center justify-between bg-white/10 rounded-lg px-4 py-3">
                    <div class="text-left">
                        <p class="text-gaf-khaki/70 text-xs uppercase tracking-wide font-medium">PIN Code</p>
                        <p id="pin-code" class="text-gaf-khaki font-heading font-bold text-xl tracking-widest mt-0.5">{{ $voucher->pin_code }}</p>
                    </div>
                    <button onclick="copyToClipboard('pin-code', this)" class="text-gaf-khaki hover:text-white transition p-2 rounded-lg hover:bg-white/10" title="Copy PIN Code">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm mb-6">
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-wide">Cycle</p>
                <p class="font-medium text-gray-900">{{ $voucher->cycle->name }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-wide">Cost</p>
                <p class="font-medium text-gray-900">GHS {{ number_format($voucher->cost, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-wide">Purchased By</p>
                <p class="font-medium text-gray-900">{{ $voucher->purchaser_name }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs uppercase tracking-wide">Valid Until</p>
                <p class="font-medium text-gray-900">{{ $voucher->expires_at?->format('M d, Y H:i') }}</p>
            </div>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-700">
            <p class="font-medium mb-1">Important:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                <li>Keep your serial number and PIN confidential.</li>
                <li>You will need them to create your applicant account.</li>
                <li>Each voucher can only be used once.</li>
                <li>Voucher expires on {{ $voucher->expires_at?->format('M d, Y \a\t H:i') }}.</li>
            </ul>
        </div>

        <p class="text-xs text-gray-400 text-center mt-4">A copy of these details has been sent to <strong>{{ $voucher->purchaser_email }}</strong>.</p>
    </div>

    <div class="text-center space-y-3">
        <a href="{{ route('applicant.register') }}?serial={{ $voucher->serial_number }}&pin={{ $voucher->pin_code }}" class="inline-block bg-gaf-green text-white px-8 py-4 rounded-lg font-heading font-bold text-lg hover:bg-gaf-dark-green transition shadow-lg">Proceed to Register</a>
        <p class="text-sm text-gray-400">
            <a href="{{ route('landing') }}" class="text-gaf-green hover:underline">Return to Home</a>
        </p>
    </div>
</div>

<script>
function copyToClipboard(elementId, btn) {
    const text = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(text).then(() => {
        const original = btn.innerHTML;
        btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
        setTimeout(() => btn.innerHTML = original, 2000);
    });
}
</script>
@endsection
