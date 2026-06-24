<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Cycle;
use App\Models\Document;
use App\Models\AuditLog;
use App\Models\Appointment;
use App\Models\ScreeningResult;
use App\Models\User;
use App\Models\AiUsage;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminWebController extends Controller
{
    public function dashboard(): View
    {
        if (!auth()->user()->first_name) {
            return redirect()->route('admin.profile.complete');
        }

        $totalApplicants = Applicant::count();
        $approvedCount = Application::where('status', 'selected')->count();
        $pendingCount = Application::whereIn('status', ['submitted', 'eligibility_passed'])->count();
        $screenedCount = Application::where('status', 'screening_completed')->count();
        $shortlistedCount = Application::where('status', 'shortlisted')->count();
        $rejectedCount = Application::whereIn('status', ['rejected', 'eligibility_failed'])->count();

        $recentApplicants = Applicant::with('application')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        $regionCounts = Applicant::select('region', DB::raw('count(*) as total'))
            ->groupBy('region')
            ->orderBy('total', 'desc')
            ->get();

        $regionLabels = $regionCounts->pluck('region')->toArray();
        $regionData = $regionCounts->pluck('total')->toArray();

        $maleCount = Applicant::where('gender', 'male')->count();
        $femaleCount = Applicant::where('gender', 'female')->count();

        $funnelApplied = Application::count();
        $funnelScreened = Application::where('status', 'screening_completed')->count();
        $funnelShortlisted = Application::whereIn('status', ['shortlisted', 'appointment_scheduled', 'screening_completed', 'selected'])->count();
        $funnelApproved = Application::where('status', 'selected')->count();

        $dailyLabels = [];
        $dailyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyLabels[] = $date->format('D');
            $dailyData[] = Application::whereDate('created_at', $date)->count();
        }

        return view('admin.dashboard', compact(
            'totalApplicants', 'approvedCount', 'pendingCount', 'screenedCount',
            'shortlistedCount', 'rejectedCount', 'recentApplicants',
            'regionLabels', 'regionData', 'maleCount', 'femaleCount',
            'funnelApplied', 'funnelScreened', 'funnelShortlisted', 'funnelApproved',
            'dailyLabels', 'dailyData'
        ));
    }

    public function applications(Request $request): View
    {
        $query = Application::with('applicant', 'cycle');

        if ($s = $request->get('status')) {
            $query->where('status', $s);
        }
        if ($c = $request->get('cycle_id')) {
            $query->where('cycle_id', $c);
        }
        if ($r = $request->get('region')) {
            $query->whereHas('applicant', fn($q) => $q->where('region', $r));
        }
        if ($search = $request->get('search')) {
            $query->whereHas('applicant', fn($q) => $q->where(DB::raw("first_name || ' ' || last_name"), 'ilike', "%{$search}%")
                ->orWhere('gaf_id', 'ilike', "%{$search}%"));
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(15);

        $cycles = Cycle::orderBy('start_date', 'desc')->get();
        $regions = Applicant::select('region')->distinct()->orderBy('region')->pluck('region');
        $statuses = ['draft', 'submitted', 'eligibility_passed', 'eligibility_failed', 'shortlisted', 'appointment_scheduled', 'screening_completed', 'selected', 'rejected'];

        return view('admin.applications', compact('applications', 'cycles', 'regions', 'statuses'));
    }

    public function applicationDetail($id): View
    {
        $application = Application::with([
            'applicant',
            'applicant.voucher.cycle',
            'documents',
            'eligibilityResult',
            'screeningResult',
            'appointment.slot',
            'finalDecision',
            'verificationCode',
        ])->findOrFail($id);

        $auditLogs = AuditLog::where('applicant_id', $application->applicant_id)
            ->orWhere('application_id', $application->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.application-detail', compact('application', 'auditLogs'));
    }

    public function cycles(): View
    {
        $cycles = Cycle::withCount('applications')->orderBy('start_date', 'desc')->get();
        return view('admin.cycles', compact('cycles'));
    }

    public function scheduling(): View
    {
        $appointments = Appointment::with('application.applicant')
            ->orderBy('appointment_date', 'desc')
            ->get();

        $scheduledDates = $appointments->pluck('appointment_date')
            ->filter()
            ->map(fn($d) => $d->format('Y-m-d'))
            ->unique()
            ->values()
            ->toArray();

        return view('admin.scheduling', compact('appointments', 'scheduledDates'));
    }

    public function screeningResults(): View
    {
        $results = ScreeningResult::with('application.applicant')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.screening-results', compact('results'));
    }

    public function selection(): View
    {
        $applications = Application::with('applicant', 'eligibilityResult', 'screeningResult')
            ->whereIn('status', ['eligibility_passed', 'shortlisted', 'appointment_scheduled', 'screening_completed', 'selected', 'rejected'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.selection', compact('applications'));
    }

    public function reports(): View
    {
        $cycles = Cycle::orderBy('start_date', 'desc')->get();
        return view('admin.reports', compact('cycles'));
    }

    public function users(): View
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('users'));
    }

    public function aiConfig(): View
    {
        $aiStats = AiUsage::select(
            DB::raw('COALESCE(SUM(tokens_used), 0) as tokens_total'),
            DB::raw('COALESCE(SUM(CASE WHEN created_at >= now() - interval \'1 day\' THEN tokens_used ELSE 0 END), 0) as tokens_today'),
            DB::raw('COALESCE(SUM(CASE WHEN created_at >= now() - interval \'1 month\' THEN tokens_used ELSE 0 END), 0) as tokens_month'),
            DB::raw('COALESCE(COUNT(*), 0) as total_requests'),
            DB::raw('COALESCE(SUM(CASE WHEN created_at >= now() - interval \'1 day\' THEN 1 ELSE 0 END), 0) as requests_today'),
            DB::raw('COALESCE(SUM(CASE WHEN created_at >= now() - interval \'1 month\' THEN 1 ELSE 0 END), 0) as requests_month'),
        )->first();

        $aiSettings = [
            'ai_enabled' => env('AI_ENABLED', true),
            'provider' => env('AI_PROVIDER', 'openai'),
            'model' => env('AI_MODEL', 'gpt-4'),
            'monthly_budget_cap' => env('AI_MONTHLY_BUDGET', 200),
            'daily_budget_cap' => env('AI_DAILY_BUDGET', 10),
            'max_requests_per_minute' => env('AI_MAX_RPM', 60),
            'max_tokens_per_request' => env('AI_MAX_TOKENS', 4096),
        ];

        return view('admin.ai-config', compact('aiStats', 'aiSettings'));
    }

    public function auditLogs(): View
    {
        $logs = AuditLog::with('applicant', 'administrator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.audit-logs', compact('logs'));
    }

    public function settings(): View
    {
        $settings = [
            'system_name' => env('APP_NAME', 'DMRMS'),
            'support_email' => env('SUPPORT_EMAIL', 'support@dmrms.gov.gh'),
            'max_applications' => env('MAX_APPLICATIONS', 10000),
            'min_age' => env('MIN_AGE', 18),
            'max_age' => env('MAX_AGE', 26),
            'min_height_male' => env('MIN_HEIGHT_MALE', 1.68),
            'min_height_female' => env('MIN_HEIGHT_FEMALE', 1.60),
            'php_version' => PHP_VERSION,
            'db_connection' => config('database.default'),
            'laravel_version' => app()->version(),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function profileComplete(): View
    {
        if (auth()->user()->first_name && auth()->user()->last_name) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.profile-complete');
    }

    public function profileCompleteStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
        ]);

        Auth::user()->update($validated);

        return redirect()->route('admin.dashboard');
    }
}
