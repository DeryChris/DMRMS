@extends('layouts.app')

@section('title', '403 - Access Denied')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="text-center max-w-lg">
        <h1 class="text-9xl font-heading font-extrabold tracking-tight" style="color: #C8102E;">403</h1>
        <h2 class="text-3xl font-heading font-bold text-gray-800 mt-4">Access Denied</h2>
        <p class="text-gray-500 mt-4 text-lg leading-relaxed">
            You do not have permission to access this resource. If you believe this is an error, please contact the system administrator.
        </p>
        <div class="mt-8 flex items-center justify-center gap-4">
            <a href="/" class="inline-block bg-gaf-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-900 transition">Home</a>
            @guest
                <a href="{{ route('login') }}" class="inline-block bg-gaf-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-gaf-dark-green transition">Login</a>
            @endguest
        </div>
    </div>
</div>
@endsection
