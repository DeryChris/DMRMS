@extends('layouts.applicant')

@section('title', 'Documents - Ghana Armed Forces')

@section('content')
<div x-data="{ filePreview: null }" class="max-w-4xl mx-auto">
    <h1 class="font-heading font-bold text-2xl text-gray-800 mb-2">My Documents</h1>
    <p class="text-gray-500 text-sm mb-6">Upload and manage your required documents.</p>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">{{ session('error') }}</div>
    @endif

    @if(!$applicant->application)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center mb-6">
        <p class="text-sm text-amber-700">Please submit your application before uploading documents.</p>
        <a href="{{ route('applicant.application') }}" class="inline-block mt-2 text-sm font-semibold text-gaf-green hover:underline">Go to Application</a>
    </div>
    @else
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
                        @php
                            $statusColors = ['verified' => 'bg-green-100 text-green-700', 'pending' => 'bg-yellow-100 text-yellow-700', 'rejected' => 'bg-red-100 text-red-700'];
                        @endphp
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $statusColors[$doc->verification_status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($doc->verification_status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-500 hidden md:table-cell text-sm">{{ $doc->upload_date?->format('Y-m-d') ?: '—' }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            @if($doc->file_path)
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-gaf-green hover:text-gaf-dark-green text-sm font-medium">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
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

    <div class="mt-8">
        <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Upload New Document</h3>
        <form method="POST" action="{{ route('applicant.documents.upload') }}" enctype="multipart/form-data" class="bg-white border border-gray-200 rounded-xl p-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document Type</label>
                    <select name="document_type" required class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                        <option value="">Select</option>
                        <option value="birth_certificate">Birth Certificate</option>
                        <option value="national_id">National ID (Ghana Card)</option>
                        <option value="certificate">WASSCE/SSCE Certificate</option>
                        <option value="photograph">Passport Photograph</option>
                        <option value="medical_report">Medical Report</option>
                        <option value="police_clearance">Police Clearance</option>
                        <option value="other">Other</option>
                    </select>
                    <x-input-error :messages="$errors->get('document_type')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File (PDF/JPEG, max 5MB)</label>
                    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki">
                    <x-input-error :messages="$errors->get('file')" style="font-size:12px;color:#dc2626;margin-top:4px;" />
                </div>
            </div>
            <button type="submit" class="px-6 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">Upload Document</button>
        </form>
    </div>
    @endif
</div>
@endsection
