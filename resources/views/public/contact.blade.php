@extends('layouts.app')

@section('title', 'Contact - Ghana Armed Forces')

@php $unsplashPhoto = $unsplashPhoto ?? unsplash_hero(); @endphp

@section('hero')
<div class="relative overflow-hidden" style="min-height:200px;">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $unsplashPhoto['regular_url'] ?? '' }}');"></div>
    <div class="absolute inset-0" style="background:linear-gradient(135deg, rgba(20,83,45,0.9) 0%, rgba(15,47,31,0.85) 70%, rgba(155,34,38,0.75) 100%);"></div>
    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <h1 class="font-heading font-bold text-3xl text-white mb-2">Contact Us</h1>
        <p class="text-gaf-khaki/80">Get in touch with the Ghana Armed Forces recruitment team.</p>
    </div>
    @if($unsplashPhoto && ($unsplashPhoto['attribution']['name'] ?? '') !== 'Unsplash')
    <div class="absolute bottom-2 right-4 z-20 text-xs text-white/40">
        Photo by <a href="{{ ($unsplashPhoto['attribution']['link'] ?? '#') }}?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">{{ $unsplashPhoto['attribution']['name'] ?? 'Unknown' }}</a> on <a href="https://unsplash.com/?utm_source=dmrms&utm_medium=referral" target="_blank" class="underline hover:text-white/80" rel="noopener noreferrer">Unsplash</a>
    </div>
    @endif
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="relative bg-gradient-section rounded-xl p-8 overflow-hidden">
            <div class="absolute inset-0 pointer-events-none" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22 viewBox=%220 0 200 200%22%3E%3Cg fill=%22%23D4AF37%22 fill-opacity=%220.04%22%3E%3Cpath d=%22M0 0 L200 0 L200 200 L0 200 Z M30 30 Q60 10 100 30 Q140 50 170 30%22/%3E%3Cpath d=%22M10 80 Q40 60 80 80 Q120 100 160 80%22/%3E%3Cpath d=%22M20 130 Q60 110 100 130 Q140 150 180 130%22/%3E%3C/g%3E%3C/svg%3E'); background-repeat: repeat; background-size: 200px;"></div>
            <div class="relative z-10">
                <div class="space-y-6">
                    <div class="gradient-border-left pl-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-gaf-khaki/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-gaf-khaki" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div><p class="text-sm font-medium text-white">Address</p><p class="text-sm text-white/70">{{ $contactAddress ?? 'Ghana Armed Forces Headquarters, Burma Camp, Accra' }}</p></div>
                        </div>
                    </div>
                    <div class="gradient-border-left pl-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-gaf-khaki/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-gaf-khaki" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div><p class="text-sm font-medium text-white">Phone</p><p class="text-sm text-white/70">{{ $contactPhone ?? '+233 (0) 302 123 456' }}</p></div>
                        </div>
                    </div>
                    <div class="gradient-border-left pl-6">
                        <div class="flex items-start space-x-4">
                            <div class="w-10 h-10 bg-gaf-khaki/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-gaf-khaki" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div><p class="text-sm font-medium text-white">Email</p><p class="text-sm text-white/70">{{ $contactEmail ?? 'recruitment@gaf.mil.gh' }}</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white/90 glass-strong rounded-xl p-8">
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6 gradient-border pb-4">Send a Message</h2>
                <form method="POST" action="{{ route('contact.send') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Your name" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }}">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="your@email.com" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }}">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Subject" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has('subject') ? 'border-red-500' : 'border-gray-300' }}">
                        @error('subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea name="message" rows="5" placeholder="Your message..." class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has('message') ? 'border-red-500' : 'border-gray-300' }}">{{ old('message') }}</textarea>
                        @error('message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="w-full bg-gaf-green text-white py-3 rounded-lg font-semibold hover:bg-gaf-dark-green transition">Send Message</button>
                </form>
            </div>

            <div class="bg-white/90 glass-strong rounded-xl p-8">
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-4 gold-accent pb-2">Office Hours</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-600">Monday - Friday</span><span class="font-medium text-gray-800">8:00 AM - 5:00 PM</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Saturday</span><span class="font-medium text-gray-800">9:00 AM - 1:00 PM</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Sunday & Public Holidays</span><span class="font-medium text-gray-800">Closed</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
