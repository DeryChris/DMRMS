<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class WebController extends Controller
{
    public function landing(): View
    {
        $heroPath = public_path('assets/images/hero');
        $images = [];
        $animations = ['anim-fade', 'anim-zoom', 'anim-slide-right', 'anim-slide-up'];

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

        return view('public.landing', compact('images'));
    }

    public function eligibilityChecker(): View
    {
        return view('public.eligibility-checker');
    }

    public function announcements(): View
    {
        return view('public.announcements');
    }

    public function guide(): View
    {
        return view('public.guide');
    }

    public function faq(): View
    {
        return view('public.faq');
    }

    public function contact(): View
    {
        return view('public.contact');
    }
}
