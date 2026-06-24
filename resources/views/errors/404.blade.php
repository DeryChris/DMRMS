@extends('layouts.app')

@section('title', '404 - Page Not Found')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="text-center max-w-lg">
        <h1 class="text-9xl font-heading font-extrabold tracking-tight text-gaf-red">404</h1>
        <h2 class="text-3xl font-heading font-bold text-gray-800 mt-4">Page Not Found</h2>
        <p class="text-gray-500 mt-4 text-lg leading-relaxed">
            The page you are looking for does not exist or has been moved.
        </p>
        <p class="text-gray-400 mt-2 text-sm">
            Try searching our <a href="{{ route('faq') }}" class="text-gaf-red hover:underline">FAQ</a> or check the <a href="{{ route('landing') }}" class="text-gaf-red hover:underline">homepage</a>.
        </p>
        <div class="mt-8">
            <a href="/" class="inline-block bg-gaf-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-900 transition">Home</a>
        </div>
    </div>
</div>
@endsection
