<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ScreeningWebController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:screener,admin');
    }

    public function dashboard(): View
    {
        return view('screening.dashboard');
    }

    public function verify(): View
    {
        return view('screening.verify');
    }

    public function medical(): View
    {
        return view('screening.medical');
    }

    public function fitness(): View
    {
        return view('screening.fitness');
    }

    public function interview(): View
    {
        return view('screening.interview');
    }
}
