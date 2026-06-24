@extends('layouts.app')

@section('title', '500 - Server Error')

@php
    $errorId = now()->format('Ymd-His') . '-' . substr(md5(uniqid()), 0, 8);
@endphp

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="text-center max-w-lg">
        <h1 class="text-9xl font-heading font-extrabold tracking-tight text-gaf-red">500</h1>
        <h2 class="text-3xl font-heading font-bold text-gray-800 mt-4">Server Error</h2>
        <p class="text-gray-500 mt-4 text-lg leading-relaxed">
            An unexpected error occurred. Our technical team has been notified. Please try again later.
        </p>
        <p class="text-gray-400 mt-4 text-xs font-mono bg-gray-100 inline-block px-3 py-1 rounded">
            Error ID: {{ $errorId }}
        </p>
        <div class="mt-8 flex items-center justify-center gap-4">
            <a href="/" class="inline-block bg-gaf-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-900 transition">Home</a>
            <a href="{{ route('contact') }}" class="inline-block border border-gaf-green text-gaf-green px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">Contact Support</a>
        </div>
    </div>
</div>
@endsection
