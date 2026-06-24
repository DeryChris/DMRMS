@extends('layouts.applicant')

@section('title', 'Appointment - DMRMS')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="font-heading font-bold text-2xl text-gray-800 mb-2">My Appointment</h1>
    <p class="text-gray-500 text-sm mb-8">View your screening appointment details.</p>

    <div class="bg-white border border-gray-200 rounded-xl p-8 mb-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-heading font-semibold text-xl text-gray-800">Screening Appointment</h2>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-100 text-blue-700">Scheduled</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-gaf-green mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <div><p class="text-sm font-medium text-gray-800">Date</p><p class="text-sm text-gray-500">July 15, 2026</p></div>
            </div>
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-gaf-green mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div><p class="text-sm font-medium text-gray-800">Time</p><p class="text-sm text-gray-500">8:00 AM - 12:00 PM</p></div>
            </div>
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-gaf-green mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <div><p class="text-sm font-medium text-gray-800">Venue</p><p class="text-sm text-gray-500">GAF Recruitment Center, Burma Camp, Accra</p></div>
            </div>
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-gaf-green mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <div><p class="text-sm font-medium text-gray-800">Slot number</p><p class="text-sm text-gray-500">SLOT-0427</p></div>
            </div>
        </div>
        <div class="mt-6 flex space-x-3">
            <button class="px-6 py-2.5 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">Reschedule</button>
            <a href="#" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition inline-flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Add to Calendar (.ics)</span>
            </a>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-8">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Venue Location</h3>
        <div class="bg-gray-200 rounded-lg h-48 flex items-center justify-center">
            <p class="text-gray-500 text-sm">Map will be displayed here</p>
        </div>
    </div>

    <div class="mt-6 bg-white border border-gray-200 rounded-xl p-8">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Upcoming Dates</h3>
        <div class="grid grid-cols-7 gap-1 text-center text-xs">
            <div class="text-gray-400 py-2">Sun</div><div class="text-gray-400 py-2">Mon</div><div class="text-gray-400 py-2">Tue</div><div class="text-gray-400 py-2">Wed</div><div class="text-gray-400 py-2">Thu</div><div class="text-gray-400 py-2">Fri</div><div class="text-gray-400 py-2">Sat</div>
            @for($i = 1; $i <= 30; $i++)
                <div class="py-2 rounded {{ $i === 15 ? 'bg-gaf-red text-white font-semibold' : 'hover:bg-gray-100' }}">{{ $i }}</div>
            @endfor
        </div>
    </div>
</div>
@endsection
