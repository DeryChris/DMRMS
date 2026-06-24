<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class ApplicantGuestLayout extends Component
{
    public string $title = 'Authentication';
    public string $subtitle = '';

    public function __construct(string $title = 'Authentication', string $subtitle = '')
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
    }

    public function render(): View
    {
        return view('layouts.applicant-guest');
    }
}
