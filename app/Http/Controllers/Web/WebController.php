<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Cycle;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class WebController extends Controller
{
    public function landing(): View
    {
        $heroPath = public_path('assets/images/hero');
        $images = [];
        $animations = ['anim-fade', 'anim-zoom', 'anim-slide-right', 'anim-slide-up'];

        $unsplashPhoto = unsplash_hero();

        if (File::isDirectory($heroPath)) {
            $files = collect(File::files($heroPath))
                ->filter(fn($f) => in_array($f->getExtension(), ['jpg', 'jpeg', 'png', 'webp', 'ico']))
                ->values();

            foreach ($files as $i => $file) {
                $images[] = [
                    'src' => asset('assets/images/hero/' . $file->getFilename()),
                    'cls' => $animations[$i % count($animations)],
                    'alt' => '',
                ];
            }
        }

        if ($unsplashPhoto && ($unsplashUrl = $unsplashPhoto['regular_url'] ?? null)) {
            array_unshift($images, [
                'src' => $unsplashUrl,
                'cls' => 'anim-fade',
                'alt' => $unsplashPhoto['alt'] ?? 'Ghana Armed Forces recruitment',
            ]);
        }

        $activeCycle = Cycle::where('status', 'active')->orderBy('start_date', 'desc')->first();

        $totalApplicants = Applicant::count();
        $shortlistedCount = Application::whereIn('status', ['shortlisted', 'appointment_scheduled', 'screening_completed', 'final_decision_pending', 'selected', 'recruited', 'reserve'])->count();
        $screeningCount = Application::whereIn('status', ['screening_completed', 'final_decision_pending', 'selected', 'recruited', 'reserve'])->count();
        $selectedCount = Application::whereIn('status', ['selected', 'recruited'])->count();

        $recentNews = Announcement::published()
            ->orderBy('published_at', 'desc')
            ->take(4)
            ->get();

        return view('public.landing', compact('images', 'activeCycle', 'totalApplicants', 'shortlistedCount', 'screeningCount', 'selectedCount', 'recentNews', 'unsplashPhoto'));
    }

    public function eligibilityChecker(): View
    {
        $unsplashPhoto = unsplash_hero();

        return view('public.eligibility-checker', compact('unsplashPhoto'));
    }

    public function recruitmentPortal(): View
    {
        $activeCycles = Cycle::where('status', 'active')
            ->withCount('applications')
            ->orderBy('start_date', 'desc')
            ->get();

        $unsplashPhoto = unsplash_hero();

        return view('public.recruitment-portal', compact('activeCycles', 'unsplashPhoto'));
    }

    public function announcements(): View
    {
        $announcements = Announcement::published()
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        $featured = Announcement::published()
            ->whereNotNull('featured_image')
            ->orderBy('published_at', 'desc')
            ->first();

        $categories = Announcement::select('category')->distinct()->pluck('category');

        $unsplashPhoto = unsplash_hero();

        return view('public.announcements', compact('announcements', 'featured', 'categories', 'unsplashPhoto'));
    }

    public function announcementDetail(Request $request, $id): View|RedirectResponse
    {
        $announcement = Announcement::findOrFail($id);

        if (!$announcement->is_published) {
            abort(404);
        }

        $announcement->increment('views_count');

        $related = Announcement::published()
            ->where('id', '!=', $announcement->id)
            ->where('category', $announcement->category)
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        $unsplashPhoto = unsplash_hero();

        return view('public.announcement-detail', compact('announcement', 'related', 'unsplashPhoto'));
    }

    public function guide(): View
    {
        $unsplashPhoto = unsplash_hero();

        return view('public.guide', compact('unsplashPhoto'));
    }

    public function faq(): View
    {
        $faqs = Faq::where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        $unsplashPhoto = unsplash_hero();

        return view('public.faq', compact('faqs', 'unsplashPhoto'));
    }

    public function contact(): View
    {
        $unsplashPhoto = unsplash_hero();

        return view('public.contact', compact('unsplashPhoto'));
    }
}
