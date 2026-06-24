@extends('layouts.applicant')

@section('title', 'Documents - Ghana Armed Forces')

@section('content')
<div x-data="{ filePreview: null }" class="max-w-4xl mx-auto">
    <h1 class="font-heading font-bold text-2xl text-gray-800 mb-2">My Documents</h1>
    <p class="text-gray-500 text-sm mb-6">Upload and manage your required documents.</p>

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
                @php
                    $documents = [
                        ['type' => 'Birth Certificate', 'file' => 'birth_certificate.pdf', 'size' => '1.2 MB', 'status' => 'verified', 'date' => '2026-06-20'],
                        ['type' => 'National ID (Ghana Card)', 'file' => 'ghana_card.jpg', 'size' => '856 KB', 'status' => 'verified', 'date' => '2026-06-20'],
                        ['type' => 'WASSCE/SSCE Certificate', 'file' => 'wassee_cert.pdf', 'size' => '1.5 MB', 'status' => 'pending', 'date' => '2026-06-21'],
                        ['type' => 'Passport Photograph', 'file' => '', 'size' => '', 'status' => 'rejected', 'date' => '2026-06-19'],
                        ['type' => 'Medical Report', 'file' => '', 'size' => '', 'status' => 'pending', 'date' => ''],
                        ['type' => 'Police Clearance', 'file' => '', 'size' => '', 'status' => 'pending', 'date' => ''],
                    ];
                @endphp
                @foreach($documents as $doc)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-800">{{ $doc['type'] }}</td>
                    <td class="px-6 py-4 text-gray-500 hidden sm:table-cell">{{ $doc['file'] ?: '—' }}</td>
                    <td class="px-6 py-4 text-gray-500 hidden md:table-cell">{{ $doc['size'] ?: '—' }}</td>
                    <td class="px-6 py-4">
                        @php
                            $statusColors = ['verified' => 'bg-green-100 text-green-700', 'pending' => 'bg-yellow-100 text-yellow-700', 'rejected' => 'bg-red-100 text-red-700'];
                        @endphp
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $statusColors[$doc['status']] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($doc['status']) }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-500 hidden md:table-cell text-sm">{{ $doc['date'] ?: '—' }}</td>
                    <td class="px-6 py-4 text-right">
                        <label class="inline-flex items-center space-x-1 text-gaf-khaki hover:text-blue-700 cursor-pointer text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <span>Upload</span>
                            <input type="file" class="hidden" @change="filePreview = $event.target.files[0].name">
                        </label>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 border-2 border-dashed border-gray-300 rounded-xl p-8 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
        <p class="text-gray-500 text-sm">Drag & drop files here or <span class="text-gaf-khaki font-medium cursor-pointer">browse</span></p>
        <p class="text-xs text-gray-400 mt-1">Supported: PDF, JPEG (Max 2MB each)</p>
    </div>
</div>
@endsection
