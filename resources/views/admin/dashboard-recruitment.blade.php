@php use Carbon\Carbon; @endphp
@extends('layouts.admin')
@section('title', 'Recruitment Officer Dashboard - Ghana Armed Forces')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gradient-border pb-4">
        <div>
            <h1 class="text-2xl font-heading font-bold text-gaf-dark-green">Recruitment Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Document verification & application overview</p>
        </div>
        <span class="text-sm text-gray-500 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-2">
            <svg class="w-4 h-4 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>{{ now()->format('l, F j, Y') }}</span>
        </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-3">{{ $pendingDocsCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Pending Document Verifications</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-3">{{ $submittedAppsCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Submitted Applications</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-3">{{ $docsVerifiedToday }}</p>
            <p class="text-xs text-gray-500 mt-1">Verified Today</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-all group">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-lg bg-gaf-green/10 flex items-center justify-center group-hover:bg-gaf-green/20 transition-colors">
                    <svg class="w-5 h-5 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-3">{{ $totalApplicants }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Applicants</p>
        </div>
    </div>

    <div class="glass-strong rounded-xl shadow-sm overflow-hidden gradient-border-left">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-heading font-bold text-gaf-dark-green">Pending <span class="text-gray-400 font-normal">Document Verifications</span></h3>
            <a href="{{ route('admin.applications', ['status' => 'submitted']) }}" class="text-xs text-gaf-green hover:text-gaf-dark-green font-medium hover:underline transition-all">View All &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-5 py-3 font-medium">Applicant</th>
                        <th class="px-5 py-3 font-medium">Documents</th>
                        <th class="px-5 py-3 font-medium">Submitted</th>
                        <th class="px-5 py-3 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pendingApplications as $app)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-gaf-green/10 flex items-center justify-center text-xs font-bold text-gaf-green">{{ substr($app->applicant->name ?? '?', 0, 1) }}</div>
                                <span class="font-medium text-gray-900">{{ $app->applicant->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex flex-wrap gap-1">
                                @php $docTypes = ['birth_certificate' => 'Birth Cert', 'certificate' => 'Certificate', 'national_id' => 'National ID', 'photograph' => 'Photo']; @endphp
                                @foreach($docTypes as $key => $label)
                                    @php $doc = $app->documents->where('document_type', $key)->first(); @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $doc && $doc->verification_status === 'verified' ? 'bg-green-100 text-green-700' : ($doc ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-400') }}">
                                        {{ $label }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $app->created_at->diffForHumans() }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.applications.detail', $app->id) }}" class="relative group p-1.5 rounded-lg hover:bg-gaf-green/10 text-gaf-green transition-colors inline-flex items-center" title="Review">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Review</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm font-medium">All documents verified</p>
                            <p class="text-xs text-gray-400 mt-1">No pending document verifications.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('admin.applications') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-gaf-green/30 transition-all group flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-gaf-green/10 flex items-center justify-center group-hover:bg-gaf-green/20 transition-colors">
                <svg class="w-6 h-6 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 group-hover:text-gaf-dark-green transition-colors">View Applications</p>
                <p class="text-xs text-gray-500 mt-0.5">Browse and verify all applications</p>
            </div>
            <svg class="w-5 h-5 text-gray-300 group-hover:text-gaf-green ml-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('admin.cycles') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-gaf-dark-green/30 transition-all group flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-gaf-dark-green/10 flex items-center justify-center group-hover:bg-gaf-dark-green/20 transition-colors">
                <svg class="w-6 h-6 text-gaf-dark-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 group-hover:text-gaf-dark-green transition-colors">Manage Cycles</p>
                <p class="text-xs text-gray-500 mt-0.5">Configure recruitment cycles</p>
            </div>
            <svg class="w-5 h-5 text-gray-300 group-hover:text-gaf-dark-green ml-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
</div>
@endsection
