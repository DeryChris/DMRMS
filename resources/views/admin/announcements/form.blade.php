@extends('layouts.admin')

@section('title', $announcement ? 'Edit Announcement' : 'New Announcement')

@push('head')
<script src="https://cdn.tiny.cloud/1/7g8xk62ny7gxf7pcyh88bhevv8h0xf7lfcxfoh93e8rzbb9q/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
@endpush

@section('content')
<div x-data="{
    published: {{ $announcement?->is_published ? 'true' : 'false' }},
    imagePreview: '{{ $announcement?->featured_image ? asset("storage/" . $announcement->featured_image) : "" }}',
    unsplashImage: '',
    fetchingUnsplash: false,
    previewImage(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => { this.imagePreview = ev.target.result; };
            reader.readAsDataURL(file);
        }
    },
    fetchUnsplash() {
        const title = document.querySelector('[name=title]')?.value;
        if (!title) { alert('Please enter a title first.'); return; }
        this.fetchingUnsplash = true;
        fetch('{{ route("admin.announcements.unsplash-fetch") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ query: title })
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) { alert(data.error); return; }
            this.imagePreview = data.url;
            this.unsplashImage = data.url;
        })
        .catch(() => alert('Failed to fetch image.'))
        .finally(() => { this.fetchingUnsplash = false; });
    }
}" class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-heading font-bold text-2xl text-gray-800">{{ $announcement ? 'Edit Announcement' : 'New Announcement' }}</h1>
        <a href="{{ route('admin.announcements.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back</a>
    </div>

    <form method="POST" action="{{ $announcement ? route('admin.announcements.update', $announcement->id) : route('admin.announcements.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if($announcement) @method('PUT') @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php $f = 'title'; @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="{{ $f }}" value="{{ old($f, $announcement?->title) }}" required maxlength="255" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @php $f = 'category'; @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="{{ $f }}" required class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                        <option value="general" {{ old($f, $announcement?->category) === 'general' ? 'selected' : '' }}>General</option>
                        <option value="requirements" {{ old($f, $announcement?->category) === 'requirements' ? 'selected' : '' }}>Requirements</option>
                        <option value="deadlines" {{ old($f, $announcement?->category) === 'deadlines' ? 'selected' : '' }}>Deadlines</option>
                        <option value="results" {{ old($f, $announcement?->category) === 'results' ? 'selected' : '' }}>Results</option>
                        <option value="press" {{ old($f, $announcement?->category) === 'press' ? 'selected' : '' }}>Press Release</option>
                        <option value="events" {{ old($f, $announcement?->category) === 'events' ? 'selected' : '' }}>Events</option>
                    </select>
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            @php $f = 'excerpt'; @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                <textarea name="{{ $f }}" rows="2" maxlength="500" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}" placeholder="Brief summary shown in cards...">{{ old($f, $announcement?->excerpt) }}</textarea>
                @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            @php $f = 'content'; @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Content <span class="text-red-500">*</span></label>
                <textarea name="{{ $f }}" id="tiny-editor" rows="12" class="w-full border rounded-lg px-4 py-3 text-sm {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">{{ old($f, $announcement?->content) }}</textarea>
                @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h3 class="font-heading font-semibold text-gray-800">Media &amp; Metadata</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
                    <div class="flex items-center gap-2">
                        <input type="file" name="featured_image" accept="image/*" @change="previewImage($event)" class="w-full text-sm">
                        <button type="button" @click="fetchUnsplash" :disabled="fetchingUnsplash" class="shrink-0 px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-50" x-text="fetchingUnsplash ? 'Fetching...' : 'Unsplash'"></button>
                    </div>
                    <input type="hidden" name="unsplash_image" x-model="unsplashImage">
                    <template x-if="imagePreview">
                        <img :src="imagePreview" class="mt-2 rounded-lg max-h-40 object-cover">
                    </template>
                    <p class="text-xs text-gray-400 mt-1">Upload an image or click <strong>Unsplash</strong> to fetch from Unsplash (uses the announcement title as search query).</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gallery Images (multi)</label>
                    <input type="file" name="media_gallery[]" multiple accept="image/*" class="w-full text-sm">
                    <p class="text-xs text-gray-400 mt-1">Upload multiple images for the gallery.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php $f = 'author'; @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                    <input type="text" name="{{ $f }}" value="{{ old($f, $announcement?->author ?? auth()->user()?->name ?? auth('web')->user()?->name ?? '') }}" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @php $f = 'tags'; @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tags (comma-separated)</label>
                    <input type="text" name="{{ $f }}" value="{{ old($f, $tags ?? '') }}" placeholder="e.g. recruitment, 2026, gaf" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @php $f = 'published_at'; @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Publish Date</label>
                    <input type="date" name="{{ $f }}" value="{{ old($f, $announcement?->published_at?->format('Y-m-d')) }}" class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki {{ $errors->has($f) ? 'border-red-500' : 'border-gray-300' }}">
                    @error($f) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" x-model="published" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-gaf-khaki rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gaf-green"></div>
                    <span class="ms-3 text-sm font-medium text-gray-700" x-text="published ? 'Published' : 'Draft'"></span>
                </label>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.announcements.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition">{{ $announcement ? 'Update' : 'Create' }} Announcement</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#tiny-editor',
            height: 400,
            menubar: false,
            plugins: 'lists link image preview code table hr',
            toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | link image | code | removeformat',
            branding: false,
            promotion: false,
            content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; color: #1f2937; }',
        });
    }
});
</script>
@endpush
