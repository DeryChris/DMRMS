@extends('layouts.applicant')

@section('title', 'Documents - Ghana Armed Forces')

@section('content')
<div x-data="documentUpload()" 
     x-init="initDraftListener()"
     @unload.window="if (hasDraftChanges()) $event.preventDefault()"
     class="max-w-5xl mx-auto px-4">

    {{-- Header with autosave status --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-heading font-bold text-2xl text-gray-800">My Documents</h1>
            <p class="text-gray-500 text-sm mt-1">Upload and manage your required documents.</p>
        </div>
        <div class="flex items-center gap-3">
            <template x-if="autoSaveStatus">
                <span class="text-xs flex items-center gap-1.5" :class="autoSaveStatusClass">
                    {{-- Spinner --}}
                    <span x-show="autoSaveStatus === 'saving'">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </span>
                    {{-- Check icon --}}
                    <span x-show="autoSaveStatus === 'saved'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    {{-- Error icon --}}
                    <span x-show="autoSaveStatus === 'error'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                    </span>
                    <span x-text="autoSaveStatusText"></span>
                </span>
            </template>
        </div>
    </div>

    {{-- Back to Application Form --}}
    <div class="flex items-center gap-2 mb-6">
        <a href="{{ route('applicant.application', ['step' => 5]) }}" class="inline-flex items-center px-3 py-1.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-gaf-green transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Application Form
        </a>
        {{-- Discard All Drafts --}}
        <button type="button" @click="confirmDiscardAll()" x-show="hasAnyDrafts" class="inline-flex items-center px-3 py-1.5 text-sm text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Discard All Drafts
        </button>
    </div>

    @if(!$applicant->application)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center mb-6">
        <p class="text-sm text-amber-700">Please submit your application before uploading documents.</p>
        <a href="{{ route('applicant.application') }}" class="inline-block mt-2 text-sm font-semibold text-gaf-green hover:underline">Go to Application</a>
    </div>
    @else
    @php
        $totalRequired = count($requiredDocTypes);
        $missingDocs = array_diff(array_keys($requiredDocTypes), $uploadedDocTypes);
        $uploadedCount = $totalRequired - count($missingDocs);
        $allUploaded = empty($missingDocs);
        $progressPercent = $totalRequired > 0 ? round(($uploadedCount / $totalRequired) * 100) : 0;
    @endphp

    {{-- Rejected Documents Alert --}}
    @if(!empty($rejectedDocTypes))
    <div class="bg-red-50 border-l-4 border-red-500 rounded-r-xl p-5 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <div>
                <p class="text-sm font-semibold text-red-800">Some documents were rejected</p>
                <p class="text-xs text-red-600 mt-1">Please re-upload the following document(s):</p>
                <ul class="mt-2 space-y-1">
                    @foreach($rejectedDocTypes as $docType)
                    <li class="flex items-center space-x-2 text-sm text-red-700">
                        <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="font-medium">{{ $docType }}</span>
                        <span class="text-xs text-red-500">— rejected, upload a corrected version below</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Required Documents Card with Progress Bar --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-2">Required Documents</h3>
        <p class="text-sm text-gray-500 mb-3">{{ $uploadedCount }} of {{ $totalRequired }} required documents uploaded</p>

        {{-- Progress Bar --}}
        <div class="w-full bg-gray-100 rounded-full h-3 mb-4 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500 ease-out {{ $allUploaded ? 'bg-green-500' : 'bg-amber-500' }}" style="width: {{ $progressPercent }}%"></div>
        </div>

        <div class="space-y-2">
            @foreach($requiredDocTypes as $type => $label)
                @php
                    $isUploaded = in_array($type, $uploadedDocTypes);
                    $verifStatus = $verificationStatuses[$type] ?? null;
                    $isRejected = $verifStatus === 'rejected';
                    $isVerified = $verifStatus === 'verified';
                    $isPending = $verifStatus === 'pending' || $verifStatus === 'needs_review';
                @endphp
                <div class="flex items-center justify-between py-2 px-3 rounded-lg
                    {{ $isRejected ? 'bg-red-50' : ($isVerified ? 'bg-green-50' : ($isUploaded ? 'bg-blue-50' : 'bg-red-50')) }}">
                    <span class="text-sm font-medium
                        {{ $isRejected ? 'text-red-800' : ($isVerified ? 'text-green-800' : ($isUploaded ? 'text-blue-800' : 'text-red-800')) }}">{{ $label }}</span>
                    <span class="inline-flex items-center text-xs font-semibold
                        {{ $isRejected ? 'text-red-700' : ($isVerified ? 'text-green-700' : ($isUploaded ? 'text-blue-700' : 'text-red-700')) }}">
                        @if($isRejected)
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Rejected
                        @elseif($isVerified)
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Verified
                        @elseif($isUploaded)
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                            Pending Review
                        @else
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            Missing
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Finalize Banner --}}
    @if($allUploaded)
    @php
        $hasRejected = !empty($rejectedDocTypes);
        $bannerBg = $hasRejected ? 'bg-amber-50 border-amber-200' : 'bg-green-50 border-green-200';
        $bannerTextColor = $hasRejected ? 'text-amber-800' : 'text-green-800';
        $bannerSubColor = $hasRejected ? 'text-amber-600' : 'text-green-600';
    @endphp
    <div class="flex items-start justify-between {{ $bannerBg }} border rounded-xl p-5 mb-6">
        <div class="flex items-start gap-3">
            @if($hasRejected)
            <svg class="w-6 h-6 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            @else
            <svg class="w-6 h-6 text-green-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @endif
            <div>
                <p class="text-sm font-semibold {{ $bannerTextColor }}">
                    @if($hasRejected)
                    Corrected documents uploaded — please finalize to re-submit
                    @else
                    All required documents uploaded
                    @endif
                </p>
                <p class="text-xs {{ $bannerSubColor }} mt-1">
                    @if($hasRejected)
                    You have re-uploaded the rejected documents. Press <strong>Proceed to Application</strong> to lock them in for re-verification.
                    @else
                    When you're ready, press <strong>Proceed to Application</strong>. Your documents will be locked and sent for verification. You won't be able to modify them afterwards.
                    @endif
                </p>
                {{-- Guidance tooltip --}}
                <div class="mt-2 relative" x-data="{ showGuidance: false }">
                    <button type="button" @click="showGuidance = !showGuidance" @click.away="showGuidance = false" class="text-xs text-gaf-green font-medium hover:underline inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        What happens next?
                    </button>
                    <div x-show="showGuidance" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="absolute left-0 top-full mt-2 w-72 bg-white border border-gray-200 rounded-xl shadow-lg p-4 text-xs text-gray-600 space-y-2 z-10">
                        <p>1. Your documents are locked and sent for AI verification.</p>
                        <p>2. If AI confidence is high, your documents are auto-verified.</p>
                        <p>3. If AI is uncertain, an admin manually reviews them.</p>
                        <p>4. You will receive a notification once verification is complete.</p>
                        <p class="text-xs text-gray-400 mt-2">This process typically takes 1-2 business days.</p>
                    </div>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('applicant.documents.finalize') }}" class="shrink-0 ml-4">
            @csrf
            <button type="submit" class="px-5 py-2.5 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition shadow-sm">
                Proceed to Application
            </button>
        </form>
    </div>
    @else
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-6">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <p class="text-sm font-semibold text-amber-800">
                    Upload all required documents to proceed
                    <span class="font-normal text-amber-600 ml-1">({{ $progressPercent }}% complete)</span>
                </p>
                <p class="text-xs text-amber-600 mt-1">Your progress is saved automatically as you upload each document.</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Documents Table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-6 py-4 font-medium text-gray-700">Document Type</th>
                    <th class="text-left px-6 py-4 font-medium text-gray-700 hidden sm:table-cell">File Name</th>
                    <th class="text-left px-6 py-4 font-medium text-gray-700 hidden md:table-cell">Size</th>
                    <th class="text-left px-6 py-4 font-medium text-gray-700">Status</th>
                    <th class="text-left px-6 py-4 font-medium text-gray-700">Draft</th>
                    <th class="text-left px-6 py-4 font-medium text-gray-700 hidden md:table-cell">Uploaded</th>
                    <th class="text-right px-6 py-4 font-medium text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($documents as $doc)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-800">{{ str_replace('_', ' ', ucfirst($doc->document_type)) }}</td>
                    <td class="px-6 py-4 text-gray-500 hidden sm:table-cell truncate max-w-[150px]">{{ $doc->file_name ?: '—' }}</td>
                    <td class="px-6 py-4 text-gray-500 hidden md:table-cell">{{ $doc->file_size ? round($doc->file_size / 1024, 1) . ' KB' : '—' }}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ status_color($doc->verification_status, 'document') }}">{{ status_label($doc->verification_status, 'document') }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($doc->is_draft)
                            <span class="inline-flex items-center text-xs font-medium px-2 py-1 rounded-full bg-amber-100 text-amber-700">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                Draft
                            </span>
                        @else
                            <span class="inline-flex items-center text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-700">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Finalized
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-500 hidden md:table-cell text-sm">{{ $doc->upload_date?->format('Y-m-d') ?: '—' }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            @if($doc->file_path)
                            <x-document-viewer :document="$doc" :documents="$documents" />
                            @endif
                            {{-- Discard draft button --}}
                            <button type="button" @click="discardDocument({{ $doc->id }}, '{{ addslashes(str_replace('_', ' ', ucfirst($doc->document_type))) }}', this)" class="text-red-400 hover:text-red-600 transition p-1" title="Discard draft">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">No documents uploaded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Upload New Document --}}
    <div class="mt-8 bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-heading font-semibold text-lg text-gray-800">Upload New Document</h3>
            {{-- Compression toggle --}}
            <label class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer">
                <input type="checkbox" x-model="compressEnabled" class="rounded border-gray-300">
                Compress large files
            </label>
        </div>

        {{-- Passport photo requirements --}}
        <div x-show="isPhotograph" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mb-5 p-4 rounded-xl border-2 border-amber-200 bg-amber-50">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-sm text-amber-800 space-y-1">
                    <p class="font-semibold">Passport Photo Requirements</p>
                    <ul class="list-disc list-inside space-y-0.5 text-amber-700">
                        <li>Size must be exactly <strong>450 pixels wide × 540 pixels tall</strong></li>
                        <li>Background must be <strong>plain white</strong> — no patterns, gradients, or shadows</li>
                        <li>Full front view, face centered, eyes open, natural expression</li>
                        <li>No sunglasses, hats, or headwear (except for religious reasons)</li>
                        <li>Only <strong>JPEG or PNG</strong> files accepted</li>
                    </ul>
                    <p class="text-xs text-amber-600 mt-1">The system will check your photo dimensions and background before upload.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('applicant.documents.upload') }}" enctype="multipart/form-data" class="space-y-5" @submit="handleUpload($event)">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Document Type</label>
                <select name="document_type" x-model="docType" required @change="autoSave()" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-green focus:border-gaf-green outline-none transition">
                    <option value="">Select document type</option>
                    <optgroup label="Required Documents">
                        <option value="birth_certificate">Birth Certificate</option>
                        <option value="certificate">Educational Certificate</option>
                        <option value="national_id">National ID (Ghana Card)</option>
                        <option value="photograph">Passport Photograph</option>
                    </optgroup>
                    <optgroup label="Optional Documents">
                        <option value="medical_report">Medical Report</option>
                        <option value="police_clearance">Police Clearance</option>
                        <option value="other">Other</option>
                    </optgroup>
                </select>
                <x-input-error :messages="$errors->get('document_type')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
            </div>

            <div
                @dragover.prevent="dragging = true"
                @dragleave.prevent="dragging = false"
                @drop.prevent="handleDrop"
                @click="document.getElementById('fileInput').click()"
                :class="{
                    'border-gaf-green bg-green-50/50': dragging,
                    'border-gray-300 bg-gray-50/50': !dragging && !hasFile,
                    'border-green-400 bg-green-50': hasFile && !compressing,
                    'border-blue-400 bg-blue-50': compressing,
                    'border-red-400 bg-red-50': (dimensionError || bgError) && hasFile
                }"
                class="relative border-2 border-dashed rounded-2xl p-10 text-center cursor-pointer transition-all duration-200 hover:border-gaf-green hover:bg-green-50/30"
            >
                <input type="file" name="file" id="fileInput" :accept="acceptAttr" class="hidden" @change="handleInput">

                {{-- No file selected --}}
                <template x-if="!hasFile && !compressing">
                    <div>
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gaf-green/10 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <p class="text-base font-semibold text-gray-700">
                            <span class="text-gaf-green">Drag & Drop</span> your file here
                        </p>
                        <p class="text-sm text-gray-400 mt-1">or <span class="text-gaf-green font-medium hover:underline">browse</span> to choose a file</p>
                        <p class="text-xs text-gray-400 mt-4" x-text="supportedText"></p>
                    </div>
                </template>

                {{-- File selected --}}
                <template x-if="hasFile && !compressing">
                    <div class="flex items-center gap-4 text-left">
                        <div class="w-12 h-12 rounded-xl bg-gaf-green/10 flex items-center justify-center shrink-0">
                            <template x-if="fileType === 'application/pdf'">
                                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </template>
                            <template x-if="fileType.startsWith('image/')">
                                <svg class="w-6 h-6 text-gaf-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate" x-text="fileName"></p>
                            <p class="text-xs text-gray-400" x-text="fileSize"></p>
                            <p x-show="compressedNotice" class="text-xs text-blue-500 mt-0.5" x-text="compressedNotice"></p>
                        </div>
                        <button type="button" @click.stop="clearFile" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>

                {{-- Compressing --}}
                <template x-if="compressing">
                    <div class="text-center py-4">
                        <svg class="w-8 h-8 mx-auto mb-3 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <p class="text-sm font-medium text-blue-700">Compressing image... <span x-text="compressProgress"></span></p>
                        <p class="text-xs text-blue-500 mt-1">Optimizing file size for faster upload</p>
                    </div>
                </template>

                {{-- Validation errors --}}
                <template x-if="dimensionError">
                    <p class="mt-2 text-xs text-red-600 text-left" x-text="dimensionError"></p>
                </template>
                <template x-if="bgError">
                    <p class="mt-2 text-xs text-red-600 text-left" x-text="bgError"></p>
                </template>
            </div>

            <x-input-error :messages="$errors->get('file')" style="font-size:12px;color:#dc2626;margin-top:4px;" />

            <div class="flex items-center justify-between pt-2">
                <p class="text-xs text-gray-400">
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Secure upload
                    </span>
                </p>
                <button type="submit" :disabled="!canSubmit || compressing" class="px-8 py-3 rounded-xl text-sm font-semibold transition-all duration-200" :class="(canSubmit && !compressing) ? 'bg-gaf-green text-white hover:bg-gaf-dark-green shadow-sm hover:shadow-md' : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                    <span x-show="!uploading">Upload Document</span>
                    <span x-show="uploading" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Uploading...
                    </span>
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Discard All Drafts Modal --}}
    <template x-teleport="body">
        <div x-show="showDiscardAllModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5);">
            <div x-show="showDiscardAllModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-heading font-semibold text-lg text-gray-800">Discard All Drafts?</h3>
                        <p class="text-sm text-gray-500">This will permanently delete all draft documents. This action cannot be undone.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showDiscardAllModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Cancel</button>
                    <button type="button" @click="executeDiscardAll()" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">Yes, Discard All</button>
                </div>
            </div>
        </div>
    </template>

    {{-- Toast notification --}}
    <template x-teleport="body">
        <div x-show="toastVisible" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" class="fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2" :class="toastType === 'success' ? 'bg-green-700 text-white' : (toastType === 'warning' ? 'bg-amber-600 text-white' : 'bg-red-700 text-white')">
            <template x-if="toastType === 'success'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </template>
            <template x-if="toastType === 'warning'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
            </template>
            <template x-if="toastType === 'error'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </template>
            <span x-text="toastMessage"></span>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function documentUpload() {
    return {
        // Upload form state
        docType: '',
        dragging: false,
        hasFile: false,
        fileName: '',
        fileSize: '',
        fileType: '',
        dimensionError: '',
        bgError: '',
        uploading: false,
        compressing: false,
        compressProgress: '',
        compressedNotice: '',
        compressEnabled: false,
        originalFileSize: 0,

        // Autosave state
        autoSaveStatus: '',
        autoSaveTimer: null,
        hasAnyDrafts: {{ $documents->where('is_draft', true)->count() > 0 ? 'true' : 'false' }},

        // Toast
        toastVisible: false,
        toastMessage: '',
        toastType: 'success',
        toastTimer: null,

        // Discard modal
        showDiscardAllModal: false,

        // ── Computed ──
        get isPhotograph() {
            return this.docType === 'photograph';
        },

        get canSubmit() {
            return this.docType && this.hasFile && !this.dimensionError && !this.bgError && !this.uploading;
        },

        get acceptAttr() {
            if (this.docType === 'photograph') return '.jpg,.jpeg,.png';
            return '.pdf,.jpg,.jpeg,.png';
        },

        get supportedText() {
            if (this.docType === 'photograph') return 'Supported: JPG, JPEG, PNG (max 5MB)';
            return 'Supported: PDF, JPG, JPEG, PNG (max 5MB)';
        },

        get autoSaveStatusText() {
            return {
                'saving': 'Saving...',
                'saved': 'Progress saved',
                'error': 'Save failed'
            }[this.autoSaveStatus] || '';
        },

        get autoSaveStatusClass() {
            return {
                'saving': 'text-blue-600',
                'saved': 'text-green-600',
                'error': 'text-red-600'
            }[this.autoSaveStatus] || 'text-gray-400';
        },

        // ── Init ──
        initDraftListener() {
            // Restore from localStorage if available
            try {
                const saved = localStorage.getItem('document_upload_draft');
                if (saved) {
                    const data = JSON.parse(saved);
                    if (data.docType) this.docType = data.docType;
                }
            } catch (e) {}
        },

        hasDraftChanges() {
            return this.docType || this.hasFile;
        },

        // ── File handling ──
        handleInput(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.processFile(file);
        },

        handleDrop(event) {
            this.dragging = false;
            const file = event.dataTransfer.files[0];
            if (!file) return;
            document.getElementById('fileInput').files = event.dataTransfer.files;
            this.processFile(file);
        },

        async processFile(file) {
            this.hasFile = true;
            this.fileName = file.name;
            this.fileSize = this.formatSize(file.size);
            this.fileType = file.type;
            this.dimensionError = '';
            this.bgError = '';
            this.compressedNotice = '';
            this.originalFileSize = file.size;
            this.autoSave();

            if (this.isPhotograph && file.type.startsWith('image/')) {
                this.validatePhoto(file);
            }

            // Optional compression for large images
            if (this.compressEnabled && file.type.startsWith('image/') && file.size > 2 * 1024 * 1024) {
                const compressed = await this.compressImage(file);
                if (compressed) {
                    // Replace the file input with compressed blob
                    const dt = new DataTransfer();
                    dt.items.add(compressed);
                    document.getElementById('fileInput').files = dt.files;
                    this.fileName = compressed.name;
                    this.fileSize = this.formatSize(compressed.size);
                    this.compressedNotice = `Compressed from ${this.formatSize(this.originalFileSize)}`;
                }
            }
        },

        formatSize(bytes) {
            if (bytes > 1024 * 1024) {
                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            }
            return (bytes / 1024).toFixed(1) + ' KB';
        },

        compressImage(file) {
            return new Promise((resolve) => {
                this.compressing = true;
                this.compressProgress = '0%';

                const img = new Image();
                const reader = new FileReader();

                reader.onload = (e) => {
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let { width, height } = img;

                        // Max dimensions
                        const MAX = 1920;
                        if (width > MAX || height > MAX) {
                            const ratio = Math.min(MAX / width, MAX / height);
                            width *= ratio;
                            height *= ratio;
                        }

                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        this.compressProgress = '50%';

                        canvas.toBlob((blob) => {
                            this.compressing = false;
                            this.compressProgress = '100%';
                            if (blob && blob.size < file.size) {
                                const compressedFile = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });
                                resolve(compressedFile);
                            } else {
                                resolve(null);
                            }
                        }, 'image/jpeg', 0.8);
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        },

        validatePhoto(file) {
            const img = new Image();
            const reader = new FileReader();
            reader.onload = (e) => {
                img.onload = () => {
                    if (img.width !== 450 || img.height !== 540) {
                        this.dimensionError = `Photo must be exactly 450×540 pixels. Yours is ${img.width}×${img.height}.`;
                    } else {
                        this.dimensionError = '';
                    }
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        clearFile() {
            this.hasFile = false;
            this.fileName = '';
            this.fileSize = '';
            this.fileType = '';
            this.dimensionError = '';
            this.bgError = '';
            this.compressedNotice = '';
            document.getElementById('fileInput').value = '';
        },

        // ── Upload ──
        handleUpload(event) {
            if (!this.canSubmit) {
                event.preventDefault();
                return;
            }
            this.uploading = true;
        },

        // ── Autosave ──
        autoSave() {
            this.autoSaveStatus = 'saving';
            if (this.autoSaveTimer) clearTimeout(this.autoSaveTimer);

            this.autoSaveTimer = setTimeout(() => {
                try {
                    const data = {
                        docType: this.docType,
                        hasFile: this.hasFile,
                        fileName: this.fileName,
                        savedAt: new Date().toISOString()
                    };
                    localStorage.setItem('document_upload_draft', JSON.stringify(data));
                    this.autoSaveStatus = 'saved';
                    this.showToast('Progress saved', 'success');

                    this.autoSaveTimer = setTimeout(() => {
                        if (this.autoSaveStatus === 'saved') {
                            this.autoSaveStatus = '';
                        }
                    }, 2500);
                } catch (e) {
                    this.autoSaveStatus = 'error';
                    this.showToast('Failed to save progress', 'error');
                }
            }, 500);
        },

        // ── Discard ──
        discardDocument(id, label, btnElement) {
            if (!confirm(`Discard "${label}" draft? This will permanently delete this document.`)) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/applicant/documents/${id}`;
            form.style.display = 'none';

            const csrf = document.createElement('input');
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            const method = document.createElement('input');
            method.name = '_method';
            method.value = 'DELETE';
            form.appendChild(method);

            document.body.appendChild(form);
            form.submit();
        },

        confirmDiscardAll() {
            if (this.hasAnyDrafts) {
                this.showDiscardAllModal = true;
            }
        },

        executeDiscardAll() {
            this.showDiscardAllModal = false;

            // Redirect to the delete-all route (we'll create it on the server side)
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("applicant.documents.discard-all") }}';
            form.style.display = 'none';

            const csrf = document.createElement('input');
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            document.body.appendChild(form);
            form.submit();
        },

        // ── Toast ──
        showToast(message, type = 'success') {
            this.toastMessage = message;
            this.toastType = type;
            this.toastVisible = true;
            if (this.toastTimer) clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => {
                this.toastVisible = false;
            }, 3000);
        }
    };
}
</script>
@endpush
