@extends('layouts.app')

@section('title', '419 - Session Expired')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="text-center max-w-lg">
        <h1 class="text-9xl font-heading font-extrabold tracking-tight text-gaf-red">419</h1>
        <h2 class="text-3xl font-heading font-bold text-gray-800 mt-4">Session Expired</h2>
        <p class="text-gray-500 mt-4 text-lg leading-relaxed">
            Your session has expired. Please refresh the page and try again.
        </p>
        <div class="mt-8">
            <button onclick="window.location.reload()" class="inline-block bg-gaf-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-900 transition cursor-pointer">Refresh Page</button>
        </div>
    </div>
</div>
@endsection
