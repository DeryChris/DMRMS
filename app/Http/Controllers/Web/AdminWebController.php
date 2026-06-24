<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminWebController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin');
    }

    public function dashboard(): View
    {
        return view('admin.dashboard');
    }

    public function applications(): View
    {
        return view('admin.applications');
    }

    public function applicationDetail($id): View
    {
        return view('admin.application-detail', compact('id'));
    }

    public function cycles(): View
    {
        return view('admin.cycles');
    }

    public function scheduling(): View
    {
        return view('admin.scheduling');
    }

    public function screeningResults(): View
    {
        return view('admin.screening-results');
    }

    public function selection(): View
    {
        return view('admin.selection');
    }

    public function reports(): View
    {
        return view('admin.reports');
    }

    public function users(): View
    {
        return view('admin.users');
    }

    public function aiConfig(): View
    {
        return view('admin.ai-config');
    }

    public function auditLogs(): View
    {
        return view('admin.audit-logs');
    }
}
