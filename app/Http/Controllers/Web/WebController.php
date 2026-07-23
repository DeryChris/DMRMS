<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Applicant;
use App\Models\Application;
use App\Mail\ContactNotificationMail;
use App\Models\ContactMessage;
use App\Models\Cycle;
use App\Models\Faq;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
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
                    'attribution' => null,
                ];
            }
        }

        $heroUnsplash = unsplash_photos(5, 'ghana armed forces,african military,west africa army', 'landscape');
        foreach ($heroUnsplash as $up) {
            array_unshift($images, [
                'src' => $up['regular_url'] ?? '',
                'cls' => 'anim-fade',
                'alt' => $up['alt'] ?? 'Ghana Armed Forces recruitment',
                'attribution' => $up['attribution'] ?? null,
            ]);
        }

        $portraitImages = [
            [
                'src' => asset('assets/images/hero/img1.png'),
                'alt' => 'Ghana Armed Forces',
                'attribution' => null,
            ],
        ];

        $militaryPortraits = unsplash_military_portraits(4);
        foreach ($militaryPortraits as $pp) {
            $portraitImages[] = [
                'src' => $pp['regular_url'] ?? '',
                'alt' => $pp['alt'] ?? 'Ghana Armed Forces',
                'attribution' => $pp['attribution'] ?? null,
            ];
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

        return view('public.landing', compact('images', 'portraitImages', 'activeCycle', 'totalApplicants', 'shortlistedCount', 'screeningCount', 'selectedCount', 'recentNews', 'unsplashPhoto'));
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
        $contactAddress = SystemSetting::getValue('contact_address', 'Ghana Armed Forces Headquarters, Burma Camp, Accra');
        $contactPhone = SystemSetting::getValue('contact_phone', '+233 (0) 302 123 456');
        $contactEmail = SystemSetting::getValue('contact_email', 'recruitment@gaf.mil.gh');

        return view('public.contact', compact('unsplashPhoto', 'contactAddress', 'contactPhone', 'contactEmail'));
    }

    public function sendContactMessage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:100',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|min:10|max:5000',
        ]);

        try {
            $contactMessage = ContactMessage::create($validated);

            // Send email notification to the configured contact address
            $contactEmail = SystemSetting::getValue('contact_email', 'amoaheugene23@gmail.com');
            Mail::to($contactEmail)->send(new ContactNotificationMail($contactMessage));

            return redirect()->route('contact')
                ->with('success', 'Your message has been sent successfully. We will get back to you shortly.');
        } catch (\Exception $e) {
            return redirect()->route('contact')
                ->with('error', 'Sorry, we could not send your message. Please try again later.');
        }
    }
}
