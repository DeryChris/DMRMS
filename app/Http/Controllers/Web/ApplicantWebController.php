<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ApplicantWebController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function dashboard(): View
    {
        return view('applicant.dashboard');
    }

    public function applicationForm(): View
    {
        return view('applicant.application-form');
    }

    public function documents(): View
    {
        return view('applicant.documents');
    }

    public function status(): View
    {
        return view('applicant.status');
    }

    public function appointment(): View
    {
        return view('applicant.appointment');
    }

    public function notifications(): View
    {
        return view('applicant.notifications');
    }
}
