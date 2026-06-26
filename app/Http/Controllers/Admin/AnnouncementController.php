<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\Media\UnsplashService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $query = Announcement::orderBy('created_at', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('published')) {
            $query->where('is_published', $request->published === 'yes');
        }

        $announcements = $query->paginate(20);
        $categories = Announcement::select('category')->distinct()->pluck('category');

        return view('admin.announcements.index', compact('announcements', 'categories'));
    }

    public function create(): View
    {
        return view('admin.announcements.form', ['announcement' => null]);
    }

    public function unsplashFetch(Request $request): JsonResponse
    {
        $query = $request->input('query', 'ghana military recruitment');
        $unsplash = app(UnsplashService::class);
        $photo = $unsplash->randomPhoto($query);

        if (!$photo || !($photo['url'] ?? null)) {
            return response()->json(['error' => 'Could not fetch image from Unsplash.'], 422);
        }

        return response()->json($photo);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'author' => 'nullable|string|max:100',
            'tags' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'media_gallery.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'unsplash_image' => 'nullable|string',
        ]);

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')
                ->store('announcements', 'public');
        } elseif ($request->filled('unsplash_image')) {
            $validated['featured_image'] = $request->input('unsplash_image');
        }

        if ($request->hasFile('media_gallery')) {
            $gallery = [];
            foreach ($request->file('media_gallery') as $file) {
                $gallery[] = [
                    'url' => $file->store('announcements/gallery', 'public'),
                    'caption' => null,
                ];
            }
            $validated['media_gallery'] = $gallery;
        }

        $validated['tags'] = $validated['tags']
            ? array_map('trim', explode(',', $validated['tags']))
            : null;

        $validated['is_published'] = $request->boolean('is_published');

        if ($validated['is_published'] && !$validated['published_at']) {
            $validated['published_at'] = now();
        }

        Announcement::create($validated);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.form', [
            'announcement' => $announcement,
            'tags' => $announcement->tags ? implode(', ', $announcement->tags) : '',
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'author' => 'nullable|string|max:100',
            'tags' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'media_gallery.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'unsplash_image' => 'nullable|string',
        ]);

        if ($request->hasFile('featured_image')) {
            if ($announcement->featured_image) {
                Storage::disk('public')->delete($announcement->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')
                ->store('announcements', 'public');
        } elseif ($request->filled('unsplash_image')) {
            if ($announcement->featured_image && !str_contains($announcement->featured_image, 'unsplash_')) {
                Storage::disk('public')->delete($announcement->featured_image);
            }
            $validated['featured_image'] = $request->input('unsplash_image');
        }

        if ($request->hasFile('media_gallery')) {
            $gallery = $announcement->media_gallery ?? [];
            foreach ($request->file('media_gallery') as $file) {
                $gallery[] = [
                    'url' => $file->store('announcements/gallery', 'public'),
                    'caption' => null,
                ];
            }
            $validated['media_gallery'] = $gallery;
        }

        $validated['tags'] = $validated['tags']
            ? array_map('trim', explode(',', $validated['tags']))
            : null;

        $validated['is_published'] = $request->boolean('is_published');

        if ($validated['is_published'] && !$validated['published_at']) {
            $validated['published_at'] = $announcement->published_at ?? now();
        }

        $announcement->update($validated);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    public function togglePublish(Announcement $announcement): RedirectResponse
    {
        $announcement->is_published = !$announcement->is_published;

        if ($announcement->is_published && !$announcement->published_at) {
            $announcement->published_at = now();
        }

        $announcement->save();

        $status = $announcement->is_published ? 'published' : 'unpublished';

        return redirect()->route('admin.announcements.index')
            ->with('success', "Announcement {$status} successfully.");
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        if ($announcement->featured_image) {
            Storage::disk('public')->delete($announcement->featured_image);
        }

        if ($announcement->media_gallery) {
            foreach ($announcement->media_gallery as $media) {
                Storage::disk('public')->delete($media['url']);
            }
        }

        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement deleted.');
    }
}
