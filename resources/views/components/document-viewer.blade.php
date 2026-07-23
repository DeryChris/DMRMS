@props(['document', 'admin' => false, 'documents' => null])

@php
    $docs = $documents ? $documents->values() : collect([$document]);
    $initialIndex = $documents ? $docs->search(fn($d) => $d->id === $document->id) : 0;
    $fileUrl = asset('storage/' . $document->file_path);
@endphp

<div x-data="documentViewer(@js($document), {{ $admin ? 'true' : 'false' }}, @js($docs->toArray()), {{ $initialIndex }})" @keydown.window="handleKeydown($event)">
    <button @click="open()" class="text-gaf-green hover:text-gaf-dark-green text-sm font-medium" title="View document">
        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
    </button>

    <div x-show="show" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60" @click.self="close()">
        <div x-ref="viewerPanel" class="bg-white rounded-2xl w-[95vw] max-w-[1352px] h-[790px] flex flex-col overflow-hidden shadow-2xl dark:bg-gray-900 dark:border-gray-700" :class="isFullscreen ? '!rounded-none !w-screen !h-screen !max-w-none' : ''">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0 dark:border-gray-700" style="background:linear-gradient(135deg, #f8faf8 0%, #ffffff 100%);">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3">
                        <h3 class="font-heading font-semibold text-gray-900 truncate dark:text-gray-100" x-text="doc.file_name"></h3>
                        <template x-if="admin">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full" :class="doc.verification_status === 'verified' ? 'bg-green-100 text-green-700' : (doc.verification_status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700')" x-text="doc.verification_status || 'pending'"></span>
                        </template>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5 dark:text-gray-400" x-text="doc.document_type ? doc.document_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : ''"></p>
                </div>
                <div class="flex items-center space-x-2 flex-shrink-0 ml-4">
                    <template x-if="documents.length > 1">
                        <div class="flex items-center space-x-1 mr-2">
                            <button @click="prevDoc()" :disabled="currentIndex === 0" class="p-1.5 rounded-lg hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition dark:hover:bg-gray-700" title="Previous">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <span class="text-xs text-gray-500 whitespace-nowrap dark:text-gray-400" x-text="(currentIndex + 1) + ' of ' + documents.length"></span>
                            <button @click="nextDoc()" :disabled="currentIndex === documents.length - 1" class="p-1.5 rounded-lg hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition dark:hover:bg-gray-700" title="Next">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </template>



                    <a :href="doc.file_url || '/storage/' + doc.file_path" target="_blank" class="flex items-center space-x-1 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-lg transition dark:text-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600" title="Download">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Download</span>
                    </a>
                    <button @click="toggleFullscreen()" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600 dark:hover:bg-gray-700 dark:text-gray-500 dark:hover:text-gray-300" :title="isFullscreen ? 'Exit fullscreen' : 'Fullscreen'">
                        <svg x-show="!isFullscreen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8V4a1 1 0 011-1h4m10 0h4a1 1 0 011 1v4M3 16v4a1 1 0 001 1h4m10 0h4a1 1 0 001-1v-4"/></svg>
                        <svg x-show="isFullscreen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h4V4m12 4h-4V4M4 16h4v4m12-4h-4v4"/></svg>
                    </button>
                    <button @click="close()" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600 dark:hover:bg-gray-700 dark:text-gray-500 dark:hover:text-gray-300" title="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-auto p-4 bg-gray-100/80 flex items-center justify-center min-h-0 relative dark:bg-gray-800">
                {{-- Image --}}
                <template x-if="isImage">
                    <div class="relative flex items-center justify-center w-full h-full">
                        <div class="flex items-center justify-center w-full h-full" :class="zoomLevel > 1 ? 'overflow-auto cursor-move' : ''">
                            <img :src="doc.file_url || '/storage/' + doc.file_path"
                                 :style="zoomStyle"
                                 :class="zoomLevel > 1 ? 'max-w-none max-h-none' : 'max-w-full max-h-full object-contain'"
                                 class="rounded-lg shadow-sm" />
                        </div>
                        <div class="absolute bottom-4 right-4 flex items-center gap-1 bg-white/90 dark:bg-gray-800/90 rounded-lg shadow-md px-2 py-1.5 z-10 text-sm">
                            <button @click="zoomOut()" :disabled="zoomLevel <= 0.25" class="px-1.5 py-0.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed" title="Zoom out">−</button>
                            <span class="text-xs font-medium min-w-[3rem] text-center text-gray-700 dark:text-gray-300" x-text="Math.round(zoomLevel * 100) + '%'"></span>
                            <button @click="zoomIn()" :disabled="zoomLevel >= 3" class="px-1.5 py-0.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed" title="Zoom in">+</button>
                            <button @click="resetZoom()" class="px-1.5 py-0.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-xs ml-1" title="Reset zoom">⟲</button>
                        </div>
                    </div>
                </template>

                {{-- PDF (server-rendered as JPEG via Imagick) --}}
                <template x-if="isPdf">
                    <div class="relative flex flex-col items-center gap-3 w-full h-full">
                        <template x-if="!pdfReady">
                            <div class="flex items-center justify-center h-full text-center text-gray-400 dark:text-gray-500">
                                <svg class="w-10 h-10 mx-auto mb-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <p class="text-sm font-medium">Loading PDF preview…</p>
                            </div>
                        </template>
                        <div x-show="pdfReady" class="flex flex-col items-center gap-3 w-full h-full overflow-auto">
                            <img :src="pdfUrl"
                                 :style="zoomStyle"
                                 :class="zoomLevel > 1 ? 'max-w-none max-h-none' : 'max-w-full max-h-[65vh] object-contain'"
                                 class="rounded-lg shadow-sm" />
                            <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400 flex-shrink-0">
                                <button @click="prevPage()" :disabled="pageNum <= 1" class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 dark:text-gray-300 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-300 dark:hover:bg-gray-600 transition">← Prev</button>
                                <span class="min-w-[8rem] text-center font-medium" x-text="'Page ' + pageNum + ' of ' + totalPages"></span>
                                <button @click="nextPage()" :disabled="pageNum >= totalPages" class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700 dark:text-gray-300 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-gray-300 dark:hover:bg-gray-600 transition">Next →</button>
                            </div>
                        </div>
                        <div class="absolute bottom-4 right-4 flex items-center gap-1 bg-white/90 dark:bg-gray-800/90 rounded-lg shadow-md px-2 py-1.5 z-10 text-sm">
                            <button @click="zoomOut()" :disabled="zoomLevel <= 0.25" class="px-1.5 py-0.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed" title="Zoom out">−</button>
                            <span class="text-xs font-medium min-w-[3rem] text-center text-gray-700 dark:text-gray-300" x-text="Math.round(zoomLevel * 100) + '%'"></span>
                            <button @click="zoomIn()" :disabled="zoomLevel >= 3" class="px-1.5 py-0.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed" title="Zoom in">+</button>
                            <button @click="resetZoom()" class="px-1.5 py-0.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-xs ml-1" title="Reset zoom">⟲</button>
                        </div>
                    </div>
                </template>

                {{-- Unsupported --}}
                <template x-if="!isImage && !isPdf">
                    <div class="text-center py-16 text-gray-400 dark:text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <p class="text-sm font-medium">Preview not available</p>
                        <p class="text-xs mt-1">This file type cannot be previewed inline.</p>
                        <a :href="doc.file_url || '/storage/' + doc.file_path" target="_blank" class="inline-block mt-3 text-gaf-green font-semibold text-sm hover:underline">Open in new tab</a>
                    </div>
                </template>
            </div>

            {{-- Admin footer --}}
            <template x-if="admin">
                <div class="px-6 py-3 border-t border-gray-200 flex items-center justify-between flex-shrink-0 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-800/80">
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Verification:</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full" :class="doc.verification_status === 'verified' ? 'bg-green-100 text-green-700' : (doc.verification_status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700')" x-text="doc.verification_status || 'pending'"></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <template x-if="doc.verification_status !== 'verified'">
                            <form method="POST" :action="'/admin/documents/' + doc.id + '/verify'" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="verified">
                                <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Approve</button>
                            </form>
                        </template>
                        <template x-if="doc.verification_status !== 'rejected'">
                            <form method="POST" :action="'/admin/documents/' + doc.id + '/verify'" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="px-4 py-1.5 text-xs font-semibold bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Reject</button>
                            </form>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
