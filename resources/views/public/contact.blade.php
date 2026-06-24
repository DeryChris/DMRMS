@extends('layouts.app')

@section('title', 'Contact - Ghana Armed Forces')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="font-heading font-bold text-3xl text-gaf-green mb-2">Contact Us</h1>
    <p class="text-gray-600 mb-10">Get in touch with the Ghana Armed Forces recruitment team.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white border border-gray-200 rounded-xl p-8">
            <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Send a Message</h2>
            <form class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" placeholder="Your name" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" placeholder="your@email.com" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" placeholder="Subject" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea rows="5" placeholder="Your message..." class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></textarea>
                </div>
                <button type="submit" class="w-full bg-gaf-green text-white py-3 rounded-lg font-semibold hover:bg-gaf-dark-green transition">Send Message</button>
            </form>
        </div>

        <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-xl p-8">
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-6">Contact Details</h2>
                <div class="space-y-5">
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 bg-gaf-khaki bg-opacity-10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gaf-khaki" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div><p class="text-sm font-medium text-gray-800">Address</p><p class="text-sm text-gray-500">Ghana Armed Forces Headquarters, Burma Camp, Accra</p></div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 bg-gaf-green bg-opacity-10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div><p class="text-sm font-medium text-gray-800">Phone</p><p class="text-sm text-gray-500">+233 (0) 302 123 456</p></div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 bg-gaf-khaki bg-opacity-10 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div><p class="text-sm font-medium text-gray-800">Email</p><p class="text-sm text-gray-500">recruitment@gaf.mil.gh</p></div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-8">
                <h2 class="font-heading font-semibold text-xl text-gray-800 mb-4">Office Hours</h2>
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
