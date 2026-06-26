@extends('layouts.applicant')

@section('title', 'Documents - Ghana Armed Forces')

@section('content')
<div x-data="{ filePreview: null }" class="max-w-5xl mx-auto px-4">
    <h1 class="font-heading font-bold text-2xl text-gray-800 mb-2">My Documents</h1>
    <p class="text-gray-500 text-sm mb-6">Upload and manage your required documents.</p>



    @if(!$applicant->application)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center mb-6">
        <p class="text-sm text-amber-700">Please submit your application before uploading documents.</p>
        <a href="{{ route('applicant.application') }}" class="inline-block mt-2 text-sm font-semibold text-gaf-green hover:underline">Go to Application</a>
    </div>
    @else
    @php
        $missingDocs = array_diff(array_keys($requiredDocTypes), $uploadedDocTypes);
        $uploadedCount = count($requiredDocTypes) - count($missingDocs);
    @endphp
    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-2">Required Documents</h3>
        <p class="text-sm text-gray-500 mb-4">{{ $uploadedCount }} of {{ count($requiredDocTypes) }} required documents uploaded</p>
        <div class="space-y-3">
            @foreach($requiredDocTypes as $type => $label)
            <div class="flex items-center justify-between py-2 px-3 rounded-lg {{ in_array($type, $uploadedDocTypes) ? 'bg-green-50' : 'bg-red-50' }}">
                <span class="text-sm font-medium {{ in_array($type, $uploadedDocTypes) ? 'text-green-800' : 'text-red-800' }}">{{ $label }}</span>
                @if(in_array($type, $uploadedDocTypes))
                    <span class="inline-flex items-center text-xs font-semibold text-green-700">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Uploaded
                    </span>
                @else
                    <span class="inline-flex items-center text-xs font-semibold text-red-700">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        Missing
                    </span>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    @if(empty($missingDocs))
    <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl p-5 mb-6">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-sm font-semibold text-green-800">All required documents uploaded</p>
                <p class="text-xs text-green-600">You can now proceed with your application.</p>
            </div>
        </div>
        <a href="{{ route('applicant.application', ['step' => 5]) }}" class="px-5 py-2.5 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition shrink-0">Proceed to Application</a>
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-6 py-4 font-medium text-gray-700">Document Type</th>
                    <th class="text-left px-6 py-4 font-medium text-gray-700 hidden sm:table-cell">File Name</th>
                    <th class="text-left px-6 py-4 font-medium text-gray-700 hidden md:table-cell">Size</th>
                    <th class="text-left px-6 py-4 font-medium text-gray-700">Status</th>
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
                    <td class="px-6 py-4 text-gray-500 hidden md:table-cell text-sm">{{ $doc->upload_date?->format('Y-m-d') ?: '—' }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            @if($doc->file_path)
                            <x-document-viewer :document="$doc" :documents="$documents" />
                            @endif
                            <form method="POST" action="{{ route('applicant.documents.delete', $doc->id) }}" onsubmit="return confirm('Delete this document?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">No documents uploaded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8" x-data="docUpload()">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Upload New Document</h3>
        <form method="POST" action="{{ route('applicant.documents.upload') }}" enctype="multipart/form-data" class="bg-white border border-gray-200 rounded-2xl p-6">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Document Type</label>
                <select name="document_type" x-model="docType" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-green focus:border-gaf-green outline-none transition">
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
                    'border-green-400 bg-green-50': hasFile
                }"
                class="relative border-2 border-dashed rounded-2xl p-10 text-center cursor-pointer transition-all duration-200 hover:border-gaf-green hover:bg-green-50/30"
            >
                <input type="file" name="file" id="fileInput" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="handleInput">

                <template x-if="!hasFile">
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
                        <p class="text-xs text-gray-400 mt-4">Supported: PDF, JPEG, PNG &mdash; Max 5MB</p>
                    </div>
                </template>

                <template x-if="hasFile">
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
                        </div>
                        <button type="button" @click.stop="clearFile" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>

            <x-input-error :messages="$errors->get('file')" style="font-size:12px;color:#dc2626;margin-top:4px;" />

            <div class="mt-5 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Secure upload
                    </span>
                </p>
                <button type="submit" :disabled="!canSubmit" class="px-8 py-3 rounded-xl text-sm font-semibold transition-all duration-200" :class="canSubmit ? 'bg-gaf-green text-white hover:bg-gaf-dark-green shadow-sm hover:shadow-md' : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                    Upload Document
                </button>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
