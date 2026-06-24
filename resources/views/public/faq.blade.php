@extends('layouts.app')

@section('title', 'FAQ - DMRMS')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="font-heading font-bold text-3xl text-gaf-green mb-2">Frequently Asked Questions</h1>
    <p class="text-gray-600 mb-8">Find answers to common questions about the recruitment process.</p>

    <div x-data="{ search: '', category: 'all' }">
        <div class="flex flex-col sm:flex-row gap-4 mb-8">
            <div class="relative flex-1">
                <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" placeholder="Search questions..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gaf-khaki">
            </div>
        </div>
        <div class="flex space-x-2 mb-6 overflow-x-auto pb-2">
            <button @click="category = 'all'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" :class="category === 'all' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">All</button>
            <button @click="category = 'general'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" :class="category === 'general' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">General</button>
            <button @click="category = 'eligibility'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" :class="category === 'eligibility' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">Eligibility</button>
            <button @click="category = 'application'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" :class="category === 'application' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">Application</button>
            <button @click="category = 'documents'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" :class="category === 'documents' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">Documents</button>
            <button @click="category = 'screening'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" :class="category === 'screening' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">Screening</button>
            <button @click="category = 'results'" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap" :class="category === 'results' ? 'bg-gaf-green text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">Results</button>
        </div>

        @php
            $faqs = [
                ['cat' => 'general', 'q' => 'What is DMRMS?', 'a' => 'DMRMS (Defence Manpower Recruitment Management System) is the official online portal for Ghana Armed Forces recruitment.'],
                ['cat' => 'general', 'q' => 'Is the recruitment process free?', 'a' => 'Yes, the application is completely free. Report anyone asking for payment to GAF authorities.'],
                ['cat' => 'eligibility', 'q' => 'What is the age limit?', 'a' => 'Applicants must be between 18 and 35 years old at the time of application.'],
                ['cat' => 'eligibility', 'q' => 'Can non-Ghanaians apply?', 'a' => 'No, only Ghanaian citizens by birth are eligible to apply.'],
                ['cat' => 'eligibility', 'q' => 'What are the height requirements?', 'a' => 'Minimum height is 1.68m for males and 1.60m for females.'],
                ['cat' => 'eligibility', 'q' => 'What educational qualifications are needed?', 'a' => 'At minimum, applicants must have SSCE/WASSCE with passes in core subjects.'],
                ['cat' => 'application', 'q' => 'How do I apply?', 'a' => 'Create an account on the portal, complete the application form, upload documents, and submit.'],
                ['cat' => 'application', 'q' => 'Can I edit my application after submission?', 'a' => 'No, once submitted, the application cannot be edited. Review carefully before submitting.'],
                ['cat' => 'application', 'q' => 'How long does the application take?', 'a' => 'The application form takes approximately 20-30 minutes to complete.'],
                ['cat' => 'documents', 'q' => 'What documents are required?', 'a' => 'Birth certificate, National ID, WASSCE/SSCE certificate, passport photograph, medical report, and police clearance.'],
                ['cat' => 'documents', 'q' => 'What file formats are accepted?', 'a' => 'PDF and JPEG formats are accepted. Maximum file size is 2MB per document.'],
                ['cat' => 'screening', 'q' => 'Where does screening take place?', 'a' => 'Screening is conducted at designated GAF recruitment centers nationwide.'],
                ['cat' => 'screening', 'q' => 'What does screening involve?', 'a' => 'Screening includes medical examination, fitness test, and oral interview.'],
                ['cat' => 'results', 'q' => 'How will I know if I am selected?', 'a' => 'Results are published on the portal and notifications are sent via email and SMS.'],
                ['cat' => 'results', 'q' => 'Can I appeal a rejection?', 'a' => 'Yes, you may submit an appeal through the portal within 14 days of the decision.'],
            ];
        @endphp

        <div class="space-y-3">
            @foreach($faqs as $faq)
            <div x-data="{ open: false }"
                 x-show="(category === 'all' || category === '{{ $faq['cat'] }}') && (search === '' || '{{ $faq['q'] }}'.toLowerCase().includes(search.toLowerCase()) || '{{ $faq['a'] }}'.toLowerCase().includes(search.toLowerCase()))"
                 x-cloak
                 class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                    <span class="text-sm font-medium text-gray-800">{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse x-cloak class="px-6 pb-4">
                    <p class="text-sm text-gray-600">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
