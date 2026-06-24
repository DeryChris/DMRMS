@extends('layouts.app')

@section('title', 'Recruitment Guide - Ghana Armed Forces')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-start justify-between mb-8">
        <div>
            <h1 class="font-heading font-bold text-3xl text-gaf-green mb-2">Recruitment Guide</h1>
            <p class="text-gray-600">Follow these steps to complete your application successfully.</p>
        </div>
        <a href="#" class="hidden sm:flex items-center space-x-2 bg-gaf-red text-white px-5 py-3 rounded-lg text-sm font-semibold hover:bg-red-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Download PDF Guide</span>
        </a>
    </div>

    <div class="mb-8 rounded-xl overflow-hidden shadow-lg">
        <img src="{{ asset('assets/images/hero/img3.jpg') }}" alt="Recruitment Process" class="w-full h-64 object-cover">
    </div>

    <div class="space-y-8">
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-10 h-10 bg-gaf-red rounded-full flex items-center justify-center flex-shrink-0"><span class="text-white font-heading font-bold">1</span></div>
                <h2 class="font-heading font-semibold text-xl text-gray-800">Create an Account</h2>
            </div>
            <p class="text-gray-600 text-sm ml-14">Visit the portal and register with a valid email address and phone number. Verify your account via the confirmation link sent to your email.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-10 h-10 bg-gaf-green rounded-full flex items-center justify-center flex-shrink-0"><span class="text-white font-heading font-bold">2</span></div>
                <h2 class="font-heading font-semibold text-xl text-gray-800">Complete Your Application</h2>
            </div>
            <p class="text-gray-600 text-sm ml-14">Fill in all required fields across the 4-step application form: personal info, education, physical & health, and document upload.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-10 h-10 bg-gaf-khaki rounded-full flex items-center justify-center flex-shrink-0"><span class="text-white font-heading font-bold">3</span></div>
                <h2 class="font-heading font-semibold text-xl text-gray-800">Upload Required Documents</h2>
            </div>
            <p class="text-gray-600 text-sm ml-14">Upload scanned copies of all required documents in PDF or JPEG format. Ensure all documents are clear and legible.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-10 h-10 bg-gaf-khaki rounded-full flex items-center justify-center flex-shrink-0"><span class="text-gray-900 font-heading font-bold">4</span></div>
                <h2 class="font-heading font-semibold text-xl text-gray-800">Submit & Wait for Shortlisting</h2>
            </div>
            <p class="text-gray-600 text-sm ml-14">Review your application thoroughly before final submission. Once Submitted, await eligibility verification and shortlisting notification.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-10 h-10 bg-gaf-red rounded-full flex items-center justify-center flex-shrink-0"><span class="text-white font-heading font-bold">5</span></div>
                <h2 class="font-heading font-semibold text-xl text-gray-800">Attend Screening</h2>
            </div>
            <p class="text-gray-600 text-sm ml-14">If shortlisted, you will receive an appointment for medical, fitness, and interview screening at a designated GAF recruitment center.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-6">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0"><span class="text-white font-heading font-bold">6</span></div>
                <h2 class="font-heading font-semibold text-xl text-gray-800">Final Selection</h2>
            </div>
            <p class="text-gray-600 text-sm ml-14">Successful candidates will be notified of their final admission status. Download your admission letter from the portal.</p>
        </div>
    </div>

    <div class="mt-12 bg-white border border-gray-200 rounded-xl p-6">
        <h2 class="font-heading font-semibold text-xl text-gray-800 mb-4">Required Documents</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr><th class="text-left px-4 py-3 font-medium text-gray-700">Document</th><th class="text-left px-4 py-3 font-medium text-gray-700">Format</th><th class="text-left px-4 py-3 font-medium text-gray-700">Max Size</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr><td class="px-4 py-3">girth Certificate</td><td class="px-4 py-3">PDF/JPEG</td><td class="px-4 py-3">2Mg</td></tr>
                    <tr><td class="px-4 py-3">National ID (Ghana Card)</td><td class="px-4 py-3">PDF/JPEG</td><td class="px-4 py-3">2Mg</td></tr>
                    <tr><td class="px-4 py-3">WASSCE/SSCE Certificate</td><td class="px-4 py-3">PDF</td><td class="px-4 py-3">2Mg</td></tr>
                    <tr><td class="px-4 py-3">Passport Photograph</td><td class="px-4 py-3">JPEG</td><td class="px-4 py-3">500Kg</td></tr>
                    <tr><td class="px-4 py-3">Medical Reporttt</td><td class="px-4 py-3">PDF</td><td class="px-4 py-3">3Mg</td></tr>
                    <tr><td class="px-4 py-3">Police Clearance</td><td class="px-4 py-3">PDF</td><td class="px-4 py-3">2Mg</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-xl p-6">
        <h2 class="font-heading font-semibold text-lg text-yellow-800 mb-2">Browser Compatibility</h2>
        <p class="text-sm text-yellow-700">For the gest experience, use the latest versions of Google Chrome, Mozilla Firefox, or Microsoft Edge. Enable JavaScript and cookies.</p>
    </div>

    <div class="mt-6 text-center sm:hidden">
        <a href="#" class="inline-flex items-center space-x-2 bg-gaf-red text-white px-6 py-3 rounded-lg text-sm font-semibold hover:bg-red-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Download PDF Guide</span>
        </a>
    </div>
</div>
@endsection
