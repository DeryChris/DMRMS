<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCycleRequest;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Cycle;
use App\Models\Document;
use App\Models\AuditLog;
use App\Models\Barrack;
use App\Models\Appointment;
use App\Models\ScreeningResult;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\AiUsage;
use App\Models\FinalDecision;
use App\Models\ReserveList;
use App\Models\VerificationCode;
use App\Services\Eligibility\EligibilityService;
use App\Services\Notification\NotificationService;
use App\Services\Scheduling\AppointmentSchedulingService;
use App\Services\ShortlistingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminWebController extends Controller
{
    protected EligibilityService $eligibilityService;
    protected ShortlistingService $shortlistingService;
    protected NotificationService $notificationService;

    protected AppointmentSchedulingService $schedulingService;

    public function __construct(
        EligibilityService $eligibilityService,
        ShortlistingService $shortlistingService,
        NotificationService $notificationService,
        AppointmentSchedulingService $schedulingService,
    ) {
        $this->eligibilityService = $eligibilityService;
        $this->shortlistingService = $shortlistingService;
        $this->notificationService = $notificationService;
        $this->schedulingService = $schedulingService;
    }

    public function dashboard(): View
    {
        if (!auth()->user()->first_name) {
            return redirect()->route('admin.profile.complete');
        }

        $user = auth()->user();

        if ($user->hasRole('recruitment_officer')) {
            return $this->recruitmentDashboard();
        }
        if ($user->hasRole('scheduling_officer')) {
            return $this->schedulingDashboard();
        }
        if ($user->hasRole('screening_officer')) {
            return $this->screeningOfficerDashboard();
        }

        return $this->fullDashboard();
    }

    private function fullDashboard(): View
    {
        $totalApplicants = Applicant::count();
        $approvedCount = Application::where('status', 'selected')->count();
        $pendingCount = Application::whereIn('status', ['submitted', 'documents_verified', 'eligibility_passed', 'final_decision_pending'])->count();
        $screenedCount = Application::where('status', 'screening_completed')->count();
        $shortlistedCount = Application::where('status', 'shortlisted')->count();
        $disqualifiedCount = Application::where('status', 'disqualified')->count();
        $rejectedCount = Application::whereIn('status', ['rejected', 'eligibility_failed', 'disqualified'])->count();
        $eligibleCount = Application::where('status', 'eligibility_passed')->count();
        $submittedTotal = Application::whereIn('status', ['submitted', 'documents_verified', 'eligibility_passed', 'shortlisted', 'appointment_scheduled', 'screening_completed', 'final_decision_pending', 'selected', 'recruited', 'reserve'])->count();
        $successRate = $submittedTotal > 0 ? round(($approvedCount / $submittedTotal) * 100, 1) : 0;

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
        $funnelDecision = Application::whereIn('status', ['final_decision_pending', 'selected', 'rejected', 'reserve'])->count();
        $funnelShortlisted = Application::whereIn('status', ['shortlisted', 'appointment_scheduled', 'screening_completed', 'final_decision_pending', 'selected', 'reserve'])->count();
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
            'shortlistedCount', 'disqualifiedCount', 'rejectedCount', 'eligibleCount', 'successRate', 'recentApplicants',
            'regionLabels', 'regionData', 'maleCount', 'femaleCount',
            'funnelApplied', 'funnelScreened', 'funnelDecision', 'funnelShortlisted', 'funnelApproved',
            'dailyLabels', 'dailyData'
        ));
    }

    private function recruitmentDashboard(): View
    {
        $pendingDocsCount = Document::where('verification_status', 'pending')->count();
        $submittedAppsCount = Application::where('status', 'submitted')->count();
        $docsVerifiedToday = Document::where('verification_status', 'verified')
            ->whereDate('verified_at', today())
            ->count();
        $totalApplicants = Applicant::count();

        $pendingApplications = Application::with('applicant', 'documents')
            ->where('status', 'submitted')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard-recruitment', compact(
            'pendingDocsCount', 'submittedAppsCount', 'docsVerifiedToday',
            'totalApplicants', 'pendingApplications'
        ));
    }

    private function schedulingDashboard(): View
    {
        $totalSlots = Appointment::count();
        $shortlistedCount = Application::where('status', 'shortlisted')->count();
        $upcomingAppointments = Appointment::whereDate('scheduled_date', '>=', today())->count();
        $totalApplicants = Applicant::count();

        $upcomingAppointmentsList = Appointment::with('application.applicant')
            ->whereDate('scheduled_date', '>=', today())
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->take(10)
            ->get();

        return view('admin.dashboard-scheduling', compact(
            'totalSlots', 'shortlistedCount', 'upcomingAppointments',
            'totalApplicants', 'upcomingAppointmentsList'
        ));
    }

    private function screeningOfficerDashboard(): View
    {
        $todayAppointments = Appointment::with('application.applicant')
            ->whereDate('scheduled_date', Carbon::today())
            ->orderBy('scheduled_time')
            ->get();

        $todayCount = $todayAppointments->count();
        $checkedInCount = $todayAppointments->whereNotNull('checked_in_at')->count();
        $pendingCount = $todayAppointments->whereNull('checked_in_at')
            ->where('status', '!=', 'missed')->count();
        $resultsToday = ScreeningResult::whereDate('created_at', today())->count();
        $totalApplicants = Applicant::count();

        return view('admin.dashboard-screening', compact(
            'todayAppointments', 'todayCount', 'checkedInCount',
            'pendingCount', 'resultsToday', 'totalApplicants'
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
        $statuses = ['draft', 'submitted', 'documents_verified', 'eligibility_passed', 'eligibility_failed', 'shortlisted', 'appointment_scheduled', 'screening_completed', 'final_decision_pending', 'selected', 'rejected', 'disqualified', 'reserve'];

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
            'appointment',
            'finalDecision',
            'verificationCode',
        ])->findOrFail($id);

        $auditLogs = AuditLog::where('user_type', 'applicant')
            ->where('user_id', $application->applicant_id)
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

    public function cycleStore(CreateCycleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['requirements'] = $this->buildRequirements($request);
        $data['created_by'] = Auth::id();
        $data['status'] = 'draft';

        Cycle::create($data);

        return redirect()->route('admin.cycles')->with('success', 'Recruitment cycle created successfully.');
    }

    public function cycleUpdate(CreateCycleRequest $request, Cycle $cycle): RedirectResponse
    {
        if ($cycle->status === 'archived') {
            return back()->with('error', 'Cannot edit an archived cycle.');
        }

        $data = $request->validated();
        $data['requirements'] = $this->buildRequirements($request);

        $cycle->update($data);

        return redirect()->route('admin.cycles')->with('success', 'Recruitment cycle updated successfully.');
    }

    public function cyclePublish(Cycle $cycle): RedirectResponse
    {
        if ($cycle->status !== 'draft') {
            return back()->with('error', 'Only draft cycles can be published.');
        }

        $cycle->update(['status' => 'active']);

        return redirect()->route('admin.cycles')->with('success', 'Cycle published and is now active.');
    }

    public function cycleClose(Cycle $cycle): RedirectResponse
    {
        if ($cycle->status !== 'active') {
            return back()->with('error', 'Only active cycles can be closed.');
        }

        $cycle->update(['status' => 'closed']);

        return redirect()->route('admin.cycles')->with('success', 'Cycle closed successfully.');
    }

    public function cycleArchive(Cycle $cycle): RedirectResponse
    {
        if ($cycle->status !== 'closed') {
            return back()->with('error', 'Only closed cycles can be archived.');
        }

        $cycle->update(['status' => 'archived']);

        return redirect()->route('admin.cycles')->with('success', 'Cycle archived successfully.');
    }

    public function shortlist(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'application_ids' => 'required|array',
            'application_ids.*' => 'exists:applications,id',
        ]);

        $result = $this->shortlistingService->bulkShortlist($validated['application_ids'], Auth::id());

        $count = count($result);

        return redirect()->route('admin.selection')->with(
            $count > 0 ? 'success' : 'error',
            $count > 0 ? "{$count} applicants shortlisted successfully." : 'No eligible applicants found to shortlist.'
        );
    }

    public function finalizeDecision(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'decision' => 'required|in:selected,rejected,deferred,reserve',
            'remarks' => 'nullable|string|max:2000',
            'evaluation' => 'nullable|json',
        ]);

        $application = Application::findOrFail($validated['application_id']);

        $decisionMap = [
            'selected' => 'admitted',
            'rejected' => 'rejected',
            'deferred' => 'deferred',
            'reserve' => 'reserve',
        ];

        $statusMap = [
            'selected' => 'selected',
            'rejected' => 'rejected',
            'deferred' => 'final_decision_pending',
            'reserve' => 'reserve',
        ];

        $data = [
            'decision' => $decisionMap[$validated['decision']],
            'decision_reason' => $validated['remarks'],
            'evaluation' => isset($validated['evaluation']) && $validated['evaluation'] ? json_decode($validated['evaluation'], true) : null,
            'committee_members' => [Auth::id()],
            'committee_approved_at' => now(),
            'committee_approved_by' => Auth::id(),
            'decision_date' => now(),
        ];

        if ($validated['decision'] === 'selected') {
            $data['reporting_code'] = 'GAF-' . strtoupper(substr(uniqid(), -8));

            $barrack = null;
            $applicant = $application->applicant;
            if ($applicant?->region) {
                $barrack = Barrack::where('region', $applicant->region)->where('is_active', true)->first();
                if ($barrack) {
                    $data['barrack_id'] = $barrack->id;
                }
            }

            Appointment::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'scheduled_date' => now()->addDays(14),
                    'scheduled_time' => '08:00',
                    'venue' => $barrack?->name . ($barrack?->location ? ', ' . $barrack->location : ''),
                    'status' => 'scheduled',
                ]
            );
        }

        FinalDecision::updateOrCreate(
            ['application_id' => $application->id],
            $data
        );

        $application->update(['status' => $statusMap[$validated['decision']]]);

        if ($validated['decision'] === 'reserve') {
            $lastPosition = ReserveList::max('position') ?? 0;
            ReserveList::create([
                'application_id' => $application->id,
                'priority_score' => $application->ai_ranking_score ?? 0,
                'position' => $lastPosition + 1,
                'notes' => $validated['remarks'],
            ]);
        }

        if (in_array($validated['decision'], ['selected', 'rejected', 'reserve'])) {
            $this->notificationService->finalDecision($application);
        }

        return redirect()->route('admin.selection')->with('success', 'Decision recorded successfully.');
    }

    public function verifyDocument(Request $request, int $id): RedirectResponse
    {
        $document = Document::with('application')->findOrFail($id);
        $application = $document->application;

        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
        ]);

        $document->update(['verification_status' => $validated['status']]);

        if ($validated['status'] === 'verified') {
            $requiredDocs = ['birth_certificate', 'certificate', 'national_id', 'photograph'];
            $verifiedDocs = $application->documents()
                ->whereIn('document_type', $requiredDocs)
                ->where('verification_status', 'verified')
                ->pluck('document_type')
                ->toArray();

            $allVerified = empty(array_diff($requiredDocs, $verifiedDocs));

            if ($allVerified && $application->status === 'submitted') {
                $application->update(['status' => 'documents_verified']);
            }
        }

        return redirect()->route('admin.applications.detail', $application->id)
            ->with('success', 'Document marked as ' . $validated['status'] . '.');
    }

    public function viewDocument(int $id): \Illuminate\Http\Response
    {
        $document = Document::findOrFail($id);
        $path = Storage::disk('public')->path($document->file_path);

        abort_unless(file_exists($path), 404);

        $content = file_get_contents($path);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => strlen($content),
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function buildRequirements(Request $request): array
    {
        return [
            'min_age' => (int) $request->input('requirements.min_age', 18),
            'max_age' => (int) $request->input('requirements.max_age', 26),
            'min_height_male' => (float) $request->input('requirements.min_height_male', 1.68),
            'min_height_female' => (float) $request->input('requirements.min_height_female', 1.60),
            'education_levels' => $request->input('requirements.education_levels', []),
            'nationality' => $request->input('requirements.nationality', 'Ghanaian'),
            'marital_status' => $request->input('requirements.marital_status', []),
            'national_id_required' => (bool) $request->input('requirements.national_id_required', true),
        ];
    }

    public function scheduling(): View
    {
        $appointments = Appointment::with('application.applicant')
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('scheduled_time')
            ->get();

        $scheduledDates = $appointments->pluck('scheduled_date')
            ->filter()
            ->map(fn($d) => $d instanceof \Carbon\Carbon ? $d->format('Y-m-d') : $d)
            ->unique()
            ->values()
            ->toArray();

        $shortlistedCount = Application::where('status', 'shortlisted')->count();

        return view('admin.scheduling', compact('appointments', 'scheduledDates', 'shortlistedCount'));
    }

    public function createSlots(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required',
            'venue'          => 'required|string|max:200',
            'capacity'       => 'required|integer|min:1|max:500',
        ]);

        $slots = $this->schedulingService->createSlots(
            $validated['scheduled_date'],
            $validated['scheduled_time'],
            $validated['venue'],
            $validated['capacity']
        );

        $this->notificationService->notifyAdminsByRole(
            'screening_officer',
            'slots_created',
            'Appointment Slots Created',
            count($slots) . " appointment slots created for {$validated['scheduled_date']} at {$validated['scheduled_time']} ({$validated['venue']})."
        );

        return redirect()->route('admin.scheduling')
            ->with('success', count($slots) . ' appointment slots created and assigned.');
    }

    public function assignSlot(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required',
            'venue'          => 'required|string|max:200',
            'slot_number'    => 'required|integer|min:1',
        ]);

        $app = Application::findOrFail($validated['application_id']);

        $this->schedulingService->assignSingle(
            $app,
            $validated['scheduled_date'],
            $validated['scheduled_time'],
            $validated['venue'],
            $validated['slot_number']
        );

        $this->notificationService->notifyAdminsByRole(
            'screening_officer',
            'appointment_assigned',
            'New Appointment Assigned',
            ($app->applicant->name ?? 'An applicant') . " has been assigned to a screening appointment on {$validated['scheduled_date']} at {$validated['scheduled_time']}."
        );

        return redirect()->route('admin.scheduling')
            ->with('success', 'Appointment assigned to ' . ($app->applicant->name ?? 'applicant') . '.');
    }

    public function screeningResults(): View
    {
        $results = ScreeningResult::with('application.applicant')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.screening-results', compact('results'));
    }

    public function screeningVerifyCode(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);

        $code = VerificationCode::with('application.applicant')
            ->where('code_value', $request->code)
            ->where('type', 'entry')
            ->where('used_status', false)
            ->where('expiry_date', '>=', now())
            ->first();

        if (!$code || !$code->application) {
            return response()->json(['error' => 'Invalid or expired verification code.'], 404);
        }

        $app = $code->application;
        $applicant = $app->applicant;

        return response()->json([
            'application_id' => $app->id,
            'name' => $applicant->name,
            'gaf_id' => $app->gaf_id,
            'contact_number' => $applicant->contact_number,
            'status' => $app->status,
        ]);
    }

    public function screeningSaveMedical(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'medical_status' => 'required|in:fit,unfit,pending',
            'blood_pressure' => 'nullable|string|max:50',
            'heart_rate'     => 'nullable|integer|min:30|max:250',
            'vision_left'    => 'nullable|string|max:20',
            'vision_right'   => 'nullable|string|max:20',
            'hearing_test'   => 'nullable|in:pass,fail',
            'height_cm'      => 'nullable|numeric|min:0.5|max:2.5',
            'weight_kg'      => 'nullable|numeric|min:30|max:200',
            'bmi'            => 'nullable|numeric',
            'notes'          => 'nullable|string|max:2000',
        ]);

        ScreeningResult::updateOrCreate(
            ['application_id' => $validated['application_id']],
            [
                'medical_status' => $validated['medical_status'],
                'medical_result' => $validated['medical_status'],
                'medical_notes'  => $validated['notes'] ?? null,
                'medical_data'   => [
                    'blood_pressure' => $validated['blood_pressure'] ?? null,
                    'heart_rate'     => $validated['heart_rate'] ?? null,
                    'vision_left'    => $validated['vision_left'] ?? null,
                    'vision_right'   => $validated['vision_right'] ?? null,
                    'hearing_test'   => $validated['hearing_test'] ?? null,
                    'height_cm'      => $validated['height_cm'] ?? null,
                    'weight_kg'      => $validated['weight_kg'] ?? null,
                    'bmi'            => $validated['bmi'] ?? null,
                ],
                'conducted_at' => now(),
            ]
        );

        $this->updateOverallStatus($validated['application_id']);

        return response()->json(['success' => true, 'message' => 'Medical results recorded.']);
    }

    public function screeningSaveFitness(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id'    => 'required|exists:applications,id',
            'fitness_score'     => 'required|numeric|min:0|max:100',
            'run_time_seconds'  => 'nullable|integer|min:0',
            'push_ups'          => 'nullable|integer|min:0',
            'sit_ups'           => 'nullable|integer|min:0',
            'pull_ups'          => 'nullable|integer|min:0',
            'shuttle_run'       => 'nullable|numeric|min:0',
            'fitness_grade'     => 'nullable|in:a,b,c,d,f',
            'notes'             => 'nullable|string|max:2000',
        ]);

        ScreeningResult::updateOrCreate(
            ['application_id' => $validated['application_id']],
            [
                'fitness_result' => $validated['fitness_score'] >= 50 ? 'pass' : 'fail',
                'fitness_score'   => $validated['fitness_score'],
                'fitness_details' => [
                    'run_time_seconds' => $validated['run_time_seconds'] ?? null,
                    'push_ups'         => $validated['push_ups'] ?? null,
                    'sit_ups'          => $validated['sit_ups'] ?? null,
                    'pull_ups'         => $validated['pull_ups'] ?? null,
                    'shuttle_run'      => $validated['shuttle_run'] ?? null,
                    'fitness_grade'    => $validated['fitness_grade'] ?? null,
                ],
                'fitness_notes' => $validated['notes'] ?? null,
                'conducted_at'  => now(),
            ]
        );

        $this->updateOverallStatus($validated['application_id']);

        return response()->json(['success' => true, 'message' => 'Fitness results recorded.']);
    }

    public function screeningSaveInterview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id'     => 'required|exists:applications,id',
            'interview_score'    => 'required|numeric|min:0|max:100',
            'interview_decision' => 'required|in:pass,fail',
            'communication'      => 'nullable|integer|min:1|max:10',
            'confidence'         => 'nullable|integer|min:1|max:10',
            'appearance'         => 'nullable|integer|min:1|max:10',
            'knowledge'          => 'nullable|integer|min:1|max:10',
            'attitude'           => 'nullable|integer|min:1|max:10',
            'notes'              => 'nullable|string|max:2000',
        ]);

        ScreeningResult::updateOrCreate(
            ['application_id' => $validated['application_id']],
            [
                'interview_score'    => $validated['interview_score'],
                'interview_decision' => $validated['interview_decision'],
                'interview_result'   => $validated['interview_decision'] === 'pass' ? 'recommended' : 'not_recommended',
                'interview_notes'    => $validated['notes'] ?? null,
                'interview_data'     => [
                    'communication' => $validated['communication'] ?? null,
                    'confidence'    => $validated['confidence'] ?? null,
                    'appearance'    => $validated['appearance'] ?? null,
                    'knowledge'     => $validated['knowledge'] ?? null,
                    'attitude'      => $validated['attitude'] ?? null,
                ],
                'conducted_at' => now(),
            ]
        );

        $this->updateOverallStatus($validated['application_id']);

        $application = Application::with('applicant')->find($validated['application_id']);
        if ($application) {
            $result = ScreeningResult::where('application_id', $application->id)->value('overall_status');

            if ($result === 'pass') {
                $newStatus = 'screening_completed';
            } elseif ($result === 'fail') {
                $newStatus = 'disqualified';
            } else {
                $newStatus = $application->status;
            }

            if ($newStatus !== $application->status) {
                $application->update(['status' => $newStatus]);
            }

            if ($newStatus === 'screening_completed') {
                $this->notificationService->notifyAdminsByRole(
                    ['admin', 'super_admin'],
                    'screening_completed',
                    'Screening Completed',
                    ($application->applicant->name ?? 'An applicant') . ' (GAF ID: ' . ($application->gaf_id ?? 'N/A') . ') has completed all screening stages.'
                );

                Appointment::where('application_id', $application->id)
                    ->whereNull('checked_in_at')
                    ->update(['checked_in_at' => now(), 'status' => 'completed']);

                Appointment::where('application_id', $application->id)
                    ->whereNotNull('checked_in_at')
                    ->update(['status' => 'completed']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Interview results recorded.',
            'status'  => $newStatus ?? 'pending',
        ]);
    }

    private function updateOverallStatus(int $applicationId): void
    {
        $result = ScreeningResult::where('application_id', $applicationId)->first();
        $result?->updateOverallStatus();
    }

    public function selection(Request $request): View
    {
        $cycles = Cycle::where('status', 'active')->orderBy('start_date', 'desc')->get();

        $eligibleQuery = Application::with('applicant', 'eligibilityResult', 'cycle')
            ->where('status', 'eligibility_passed');

        $screenedQuery = Application::with('applicant', 'screeningResult', 'eligibilityResult', 'cycle')
            ->whereIn('status', ['screening_completed', 'final_decision_pending']);

        if ($cycleId = $request->get('cycle_id')) {
            $eligibleQuery->where('cycle_id', $cycleId);
            $screenedQuery->where('cycle_id', $cycleId);
        }

        $eligibleApplicants = $eligibleQuery->orderBy('created_at', 'desc')->get();
        $screenedApplicants = $screenedQuery->orderBy('created_at', 'desc')->get();

        $vacancyStats = $cycles->map(fn($c) => [
            'cycle' => $c,
            'shortlisted_count' => Application::where('cycle_id', $c->id)
                ->whereIn('status', ['shortlisted', 'appointment_scheduled', 'screening_completed', 'final_decision_pending', 'selected', 'recruited'])
                ->count(),
            'total_vacancies' => $c->total_vacancies ?? 0,
            'remaining' => max(0, ($c->total_vacancies ?? 0) - Application::where('cycle_id', $c->id)
                ->whereIn('status', ['shortlisted', 'appointment_scheduled', 'screening_completed', 'final_decision_pending', 'selected', 'recruited'])
                ->count()),
            'pct' => ($c->total_vacancies ?? 0) > 0 ? round((Application::where('cycle_id', $c->id)
                ->whereIn('status', ['shortlisted', 'appointment_scheduled', 'screening_completed', 'final_decision_pending', 'selected', 'recruited'])
                ->count() / $c->total_vacancies) * 100) : 0,
        ]);

        return view('admin.selection', compact('eligibleApplicants', 'screenedApplicants', 'cycles', 'vacancyStats'));
    }

    public function selectionStats(Request $request): JsonResponse
    {
        $cycles = Cycle::where('status', 'active')->orderBy('start_date', 'desc')->get();

        $eligibleCount = Application::where('status', 'eligibility_passed')->count();
        $screenedCount = Application::whereIn('status', ['screening_completed', 'final_decision_pending'])->count();

        $vacancyStats = $cycles->map(fn($c) => [
            'cycle_id' => $c->id,
            'cycle_name' => $c->name,
            'shortlisted_count' => Application::where('cycle_id', $c->id)
                ->whereIn('status', ['shortlisted', 'appointment_scheduled', 'screening_completed', 'final_decision_pending', 'selected', 'recruited'])
                ->count(),
            'total_vacancies' => $c->total_vacancies ?? 0,
            'remaining' => max(0, ($c->total_vacancies ?? 0) - Application::where('cycle_id', $c->id)
                ->whereIn('status', ['shortlisted', 'appointment_scheduled', 'screening_completed', 'final_decision_pending', 'selected', 'recruited'])
                ->count()),
            'pct' => ($c->total_vacancies ?? 0) > 0 ? round((Application::where('cycle_id', $c->id)
                ->whereIn('status', ['shortlisted', 'appointment_scheduled', 'screening_completed', 'final_decision_pending', 'selected', 'recruited'])
                ->count() / $c->total_vacancies) * 100) : 0,
        ]);

        return response()->json([
            'eligibleCount' => $eligibleCount,
            'screenedCount' => $screenedCount,
            'vacancyStats' => $vacancyStats,
        ]);
    }

    public function dashboardStats(): JsonResponse
    {
        return response()->json([
            'totalApplicants' => Applicant::count(),
            'approvedCount' => Application::where('status', 'selected')->count(),
            'pendingCount' => Application::whereIn('status', ['submitted', 'documents_verified', 'eligibility_passed', 'final_decision_pending'])->count(),
            'screenedCount' => Application::where('status', 'screening_completed')->count(),
            'shortlistedCount' => Application::where('status', 'shortlisted')->count(),
            'eligibleCount' => Application::where('status', 'eligibility_passed')->count(),
            'rejectedCount' => Application::whereIn('status', ['rejected', 'eligibility_failed', 'disqualified'])->count(),
            'successRate' => round((Application::where('status', 'selected')->count() / max(1, Application::whereIn('status', ['submitted', 'documents_verified', 'eligibility_passed', 'shortlisted', 'appointment_scheduled', 'screening_completed', 'final_decision_pending', 'selected', 'recruited', 'reserve'])->count())) * 100, 1),
        ]);
    }

    public function reports(): View
    {
        $cycles = Cycle::orderBy('start_date', 'desc')->get();

        $regionStats = Applicant::select('region', DB::raw('count(*) as total'))
            ->groupBy('region')
            ->orderBy('total', 'desc')
            ->get();

        $maleCount = Applicant::where('gender', 'Male')->count();
        $femaleCount = Applicant::where('gender', 'Female')->count();

        $stageStats = Application::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->pluck('total', 'status');

        return view('admin.reports', compact('cycles', 'regionStats', 'maleCount', 'femaleCount', 'stageStats'));
    }

    public function users(): View
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('users'));
    }

    public function aiConfig(): View
    {
        $aiStats = AiUsage::select(
            DB::raw('COALESCE(SUM(total_tokens), 0) as tokens_total'),
            DB::raw('COALESCE(SUM(CASE WHEN created_at >= now() - interval \'1 day\' THEN total_tokens ELSE 0 END), 0) as tokens_today'),
            DB::raw('COALESCE(SUM(CASE WHEN created_at >= now() - interval \'1 month\' THEN total_tokens ELSE 0 END), 0) as tokens_month'),
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

    public function auditLogs(Request $request): View
    {
        $query = AuditLog::with('applicant', 'administrator');

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }
        if ($from = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'ilike', "%{$search}%")
                    ->orWhere('details', 'ilike', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $actionTypes = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit-logs', compact('logs', 'actionTypes'));
    }

    public function settings(): View
    {
        $settings = [
            'system_name' => SystemSetting::getValue('system_name', env('APP_NAME', 'DMRMS')),
            'support_email' => SystemSetting::getValue('support_email', env('SUPPORT_EMAIL', 'support@dmrms.gov.gh')),
            'max_applications' => SystemSetting::getValue('max_applications', env('MAX_APPLICATIONS', 10000)),
            'min_age' => SystemSetting::getValue('min_age', env('MIN_AGE', 18)),
            'max_age' => SystemSetting::getValue('max_age', env('MAX_AGE', 26)),
            'min_height_male' => SystemSetting::getValue('min_height_male', env('MIN_HEIGHT_MALE', 1.68)),
            'min_height_female' => SystemSetting::getValue('min_height_female', env('MIN_HEIGHT_FEMALE', 1.60)),
            'session_lifetime' => SystemSetting::getValue('session_lifetime', 120),
            'password_min_length' => SystemSetting::getValue('password_min_length', 8),
            'max_login_attempts' => SystemSetting::getValue('max_login_attempts', 5),
            'registration_enabled' => SystemSetting::getValue('registration_enabled', true, 'boolean'),
            'mfa_required' => SystemSetting::getValue('mfa_required', false, 'boolean'),
            'maintenance_mode' => SystemSetting::getValue('maintenance_mode', false, 'boolean'),
            'php_version' => PHP_VERSION,
            'db_connection' => config('database.default'),
            'laravel_version' => app()->version(),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function settingsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'system_name' => 'required|string|max:100',
            'support_email' => 'required|email|max:100',
            'max_applications' => 'required|integer|min:1|max:100000',
            'min_age' => 'required|integer|min:16|max:60',
            'max_age' => 'required|integer|min:16|max:60|gte:min_age',
            'min_height_male' => 'required|numeric|min:1.0|max:2.5',
            'min_height_female' => 'required|numeric|min:1.0|max:2.5',
            'session_lifetime' => 'integer|min:5|max:1440',
            'password_min_length' => 'integer|min:4|max:100',
            'max_login_attempts' => 'integer|min:1|max:100',
            'registration_enabled' => 'boolean',
            'mfa_required' => 'boolean',
            'maintenance_mode' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            $type = match (true) {
                is_bool($value) => 'boolean',
                is_int($value) => 'integer',
                default => 'string',
            };
            SystemSetting::setValue($key, $value, $type, 'general');
        }

        return redirect()->route('admin.settings')->with('success', 'Settings saved successfully.');
    }

    public function exportReport(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'cycle_id' => 'nullable|exists:cycles,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,csv',
        ]);

        $query = Application::with('applicant', 'cycle');

        if ($validated['cycle_id'] ?? null) {
            $query->where('cycle_id', $validated['cycle_id']);
        }
        if ($validated['start_date'] ?? null) {
            $query->whereDate('created_at', '>=', $validated['start_date']);
        }
        if ($validated['end_date'] ?? null) {
            $query->whereDate('created_at', '<=', $validated['end_date']);
        }

        $applications = $query->orderBy('created_at', 'desc')->get();

        if ($validated['format'] === 'pdf') {
            $stageStats = Application::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')->orderBy('status')->get()->pluck('total', 'status');

            $cycleName = $validated['cycle_id'] ? Cycle::find($validated['cycle_id'])?->name : 'All Cycles';
            $generatedAt = now()->format('Y-m-d H:i');

            $pdf = Pdf::loadView('admin.reports-pdf', compact('applications', 'stageStats', 'cycleName', 'generatedAt'));
            return response()->streamDownload(fn() => print($pdf->output()), "dmrms-report-{$generatedAt}.pdf");
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'GAF ID');
        $sheet->setCellValue('B1', 'Applicant Name');
        $sheet->setCellValue('C1', 'Region');
        $sheet->setCellValue('D1', 'Status');
        $sheet->setCellValue('E1', 'Cycle');
        $sheet->setCellValue('F1', 'Submitted');

        $row = 2;
        foreach ($applications as $app) {
            $sheet->setCellValue("A{$row}", $app->applicant?->gaf_id ?? 'N/A');
            $sheet->setCellValue("B{$row}", $app->applicant?->name ?? 'N/A');
            $sheet->setCellValue("C{$row}", $app->applicant?->region ?? 'N/A');
            $sheet->setCellValue("D{$row}", $app->status);
            $sheet->setCellValue("E{$row}", $app->cycle?->name ?? 'N/A');
            $sheet->setCellValue("F{$row}", $app->created_at->format('Y-m-d'));
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = "dmrms-report-" . now()->format('Y-m-d-His') . ".xlsx";
        $tempFile = tempnam(sys_get_temp_dir(), 'report');
        $writer->save($tempFile);

        return response()->streamDownload(function () use ($tempFile) {
            readfile($tempFile);
            unlink($tempFile);
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function exportAuditLogs(Request $request): StreamedResponse
    {
        $query = AuditLog::with('applicant', 'administrator');

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }
        if ($from = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'ilike', "%{$search}%")
                    ->orWhere('details', 'ilike', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'User');
        $sheet->setCellValue('B1', 'Action');
        $sheet->setCellValue('C1', 'Details');
        $sheet->setCellValue('D1', 'IP Address');
        $sheet->setCellValue('E1', 'Timestamp');

        $row = 2;
        foreach ($logs as $log) {
            $userName = $log->applicant?->name ?? $log->administrator?->name ?? 'System';
            $details = is_array($log->details) ? json_encode($log->details) : $log->details;
            $sheet->setCellValue("A{$row}", $userName);
            $sheet->setCellValue("B{$row}", $log->action);
            $sheet->setCellValue("C{$row}", $details);
            $sheet->setCellValue("D{$row}", $log->ip_address ?? 'N/A');
            $sheet->setCellValue("E{$row}", $log->created_at->format('Y-m-d H:i:s'));
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = "dmrms-audit-logs-" . now()->format('Y-m-d-His') . ".xlsx";
        $tempFile = tempnam(sys_get_temp_dir(), 'audit');
        $writer->save($tempFile);

        return response()->streamDownload(function () use ($tempFile) {
            readfile($tempFile);
            unlink($tempFile);
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function aiConfigSave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ai_enabled' => 'boolean',
            'provider' => 'string|in:openai,anthropic,google',
            'model' => 'string|max:50',
            'monthly_budget_cap' => 'numeric|min:0',
            'daily_budget_cap' => 'numeric|min:0',
            'max_requests_per_minute' => 'integer|min:1|max:10000',
            'max_tokens_per_request' => 'integer|min:1|max:100000',
        ]);

        foreach ($validated as $key => $value) {
            $type = match (true) {
                is_bool($value) => 'boolean',
                is_int($value) => 'integer',
                is_float($value) => 'float',
                default => 'string',
            };
            SystemSetting::setValue($key, $value, $type, 'ai');
        }

        return redirect()->route('admin.ai-config')->with('success', 'AI settings saved.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:administrators,email',
            'password' => 'required|string|min:8|max:100',
            'role' => 'required|in:super_admin,admin,recruitment_officer,screening_officer,scheduling_officer',
            'status' => 'sometimes|in:active,suspended',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'status' => $validated['status'] ?? 'active',
            'username' => strtolower($validated['first_name'] . '.' . $validated['last_name']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:administrators,email,' . $user->id,
            'role' => 'required|in:super_admin,admin,recruitment_officer,screening_officer,scheduling_officer',
            'password' => 'nullable|string|min:8|max:100',
        ]);

        $data = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }

        $user->update($data);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function toggleUserStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot suspend your own account.');
        }

        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        $action = $newStatus === 'suspended' ? 'suspended' : 'activated';

        return redirect()->route('admin.users')->with('success', "User {$action} successfully.");
    }

    public function kpi(Request $request): View
    {
        $cycleId = $request->get('cycle_id');

        $query = Application::query();
        $cycleQuery = Application::query();

        if ($cycleId) {
            $query->where('cycle_id', $cycleId);
            $cycleQuery->where('cycle_id', $cycleId);
        }

        $totalApplicants = (clone $query)->count();
        $eligibleCount = (clone $query)->where('status', 'eligibility_passed')->count();
        $rejectedCount = (clone $query)->whereIn('status', ['rejected', 'eligibility_failed', 'disqualified'])->count();
        $selectedCount = (clone $query)->where('status', 'selected')->count();
        $recruitedCount = (clone $query)->where('status', 'recruited')->count();
        $submittedCount = (clone $query)->whereIn('status', ['submitted', 'documents_verified', 'eligibility_passed', 'shortlisted', 'appointment_scheduled', 'screening_completed', 'final_decision_pending', 'selected', 'recruited', 'reserve'])->count();

        $successRate = $submittedCount > 0 ? round((($selectedCount + $recruitedCount) / $submittedCount) * 100, 1) : 0;

        $rejectionBreakdown = (clone $query)
            ->select('status', DB::raw('count(*) as total'))
            ->whereIn('status', ['rejected', 'eligibility_failed', 'disqualified'])
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status');

        $stageFunnel = collect(config('recruitment.application_stages'))
            ->mapWithKeys(fn($s) => [$s => (clone $query)->where('status', $s)->count()])
            ->filter(fn($c) => $c > 0);

        $maleCount = Applicant::whereHas('application', fn($q) => $cycleId ? $q->where('cycle_id', $cycleId) : $q)->where('gender', 'Male')->count();
        $femaleCount = Applicant::whereHas('application', fn($q) => $cycleId ? $q->where('cycle_id', $cycleId) : $q)->where('gender', 'Female')->count();

        $regionStats = Applicant::select('region', DB::raw('count(*) as total'))
            ->whereHas('application', fn($q) => $cycleId ? $q->where('cycle_id', $cycleId) : $q)
            ->groupBy('region')
            ->orderBy('total', 'desc')
            ->get();

        $cycles = Cycle::orderBy('start_date', 'desc')->get();

        $cycleComparison = $cycles->map(fn($c) => [
            'name' => $c->name,
            'total' => Application::where('cycle_id', $c->id)->count(),
            'eligible' => Application::where('cycle_id', $c->id)->where('status', 'eligibility_passed')->count(),
            'selected' => Application::where('cycle_id', $c->id)->whereIn('status', ['selected', 'recruited'])->count(),
            'rejected' => Application::where('cycle_id', $c->id)->whereIn('status', ['rejected', 'eligibility_failed', 'disqualified'])->count(),
            'submitted' => Application::where('cycle_id', $c->id)->whereIn('status', ['submitted', 'documents_verified', 'eligibility_passed', 'shortlisted', 'appointment_scheduled', 'screening_completed', 'final_decision_pending', 'selected', 'recruited', 'reserve'])->count(),
        ])->map(fn($c) => array_merge($c, ['success_rate' => $c['submitted'] > 0 ? round(($c['selected'] / $c['submitted']) * 100, 1) : 0]));

        return view('admin.kpi', compact(
            'totalApplicants', 'eligibleCount', 'rejectedCount', 'selectedCount', 'recruitedCount',
            'submittedCount', 'successRate', 'rejectionBreakdown', 'stageFunnel',
            'maleCount', 'femaleCount', 'regionStats', 'cycles', 'cycleId', 'cycleComparison'
        ));
    }

    public function markRecruited(Request $request, int $id): RedirectResponse
    {
        $application = Application::findOrFail($id);

        if ($application->status !== 'selected') {
            return back()->with('error', 'Only selected applicants can be marked as recruited.');
        }

        $validated = $request->validate([
            'enrollment_date' => 'nullable|date',
            'training_battalion' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($application, $validated) {
            $application->update(['status' => 'recruited']);

            AuditLog::create([
                'user_id' => Auth::id(),
                'user_type' => 'administrator',
                'action' => 'recruit_completed',
                'details' => array_merge(
                    ['application_id' => $application->id],
                    array_filter($validated)
                ),
                'ip_address' => request()->ip(),
            ]);
        });

        return redirect()->route('admin.applications.detail', $application->id)
            ->with('success', 'Applicant marked as recruited successfully.');
    }

    public function offerLetter(int $id)
    {
        $application = Application::with('applicant', 'cycle', 'finalDecision')
            ->findOrFail($id);

        if (!in_array($application->status, ['selected', 'recruited'])) {
            abort(404, 'Offer letter not available for this applicant.');
        }

        $pdf = Pdf::loadView('admin.offer-letter-pdf', [
            'applicant' => $application->applicant,
            'application' => $application,
            'cycle' => $application->cycle,
            'generatedAt' => now()->format('F j, Y'),
        ]);

        $filename = 'offer-letter-' . ($application->applicant->gaf_id ?? 'applicant') . '.pdf';

        return response()->streamDownload(fn() => print($pdf->output()), $filename);
    }

    public function recruited(): View
    {
        $applications = Application::with('applicant', 'finalDecision', 'cycle', 'screeningResult')
            ->whereIn('status', ['selected', 'recruited'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('admin.recruited', compact('applications'));
    }

    public function sendBack(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'target_status' => 'required|string',
            'reason' => 'required|string|max:2000',
        ]);

        $application = Application::with('applicant', 'finalDecision', 'screeningResult', 'appointment', 'verificationCode')
            ->findOrFail($id);

        $currentStatus = $application->status;
        $targetStatus = $validated['target_status'];

        $validTargets = $this->getSendBackTargets($currentStatus);

        if (!in_array($targetStatus, $validTargets)) {
            return back()->with('error', 'Invalid target status selected.');
        }

        DB::transaction(function () use ($application, $targetStatus, $currentStatus, $validated) {
            $application->update([
                'status' => $targetStatus,
                'returned_count' => ($application->returned_count ?? 0) + 1,
                'last_returned_from' => $currentStatus,
                'last_returned_to' => $targetStatus,
                'last_return_reason' => $validated['reason'],
                'last_returned_at' => now(),
            ]);

            $stageOrder = config('recruitment.application_stages');
            $targetIndex = array_search($targetStatus, $stageOrder);

            $finalDecisionIndex = array_search('final_decision_pending', $stageOrder);
            $screeningIndex = array_search('screening_completed', $stageOrder);
            $appointmentIndex = array_search('appointment_scheduled', $stageOrder);
            $shortlistIndex = array_search('shortlisted', $stageOrder);

            if ($targetIndex < $finalDecisionIndex && $application->finalDecision) {
                $application->finalDecision->delete();
            }

            if ($targetIndex < $screeningIndex && $application->screeningResult) {
                $application->screeningResult->update(['is_stale' => true]);
            }

            if ($targetIndex < $appointmentIndex && $application->appointment) {
                $application->appointment->update(['status' => 'cancelled']);
            }

            if ($targetIndex < $shortlistIndex && $application->verificationCode) {
                $application->verificationCode->update(['used_status' => true]);
            }

            AuditLog::create([
                'user_id' => Auth::id(),
                'user_type' => 'administrator',
                'action' => 'send_back',
                'details' => [
                    'application_id' => $application->id,
                    'from_status' => $currentStatus,
                    'to_status' => $targetStatus,
                    'reason' => $validated['reason'],
                    'returned_count' => $application->returned_count + 1,
                ],
                'ip_address' => request()->ip(),
            ]);
        });

        $application->refresh();
        $this->notificationService->sendBack($application, $currentStatus, $validated['reason']);

        return redirect()->route('admin.recruited')->with('success', "{$application->applicant->name} has been sent back to " . str_replace('_', ' ', $targetStatus) . " for re-review.");
    }

    public static function getSendBackTargetsStatic(string $status): array
    {
        $stageOrder = config('recruitment.application_stages');
        $index = array_search($status, $stageOrder);

        if ($index === false || $index <= 1) {
            return [];
        }

        return array_slice($stageOrder, 0, $index);
    }

    private function getSendBackTargets(string $status): array
    {
        return self::getSendBackTargetsStatic($status);
    }

    public function applicants(Request $request): View
    {
        $query = Applicant::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('gaf_id', 'ilike', "%{$search}%")
                  ->orWhere('contact_number', 'ilike', "%{$search}%");
            });
        }

        if ($region = $request->get('region')) {
            $query->where('region', $region);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $applicants = $query->with('application')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $regions = Applicant::select('region')->distinct()->orderBy('region')->pluck('region');
        $statuses = ['active', 'inactive', 'suspended', 'pending'];

        return view('admin.applicants', compact('applicants', 'regions', 'statuses'));
    }

    public function updateApplicant(Request $request, Applicant $applicant): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:applicants,email,' . $applicant->id,
            'contact_number' => 'nullable|string|max:20',
            'region' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,suspended,pending',
        ]);

        $applicant->update($validated);

        return redirect()->route('admin.applicants')
            ->with('success', "Applicant {$applicant->name} updated successfully.");
    }

    public function toggleApplicantStatus(Applicant $applicant): RedirectResponse
    {
        $newStatus = $applicant->status === 'active' ? 'suspended' : 'active';
        $applicant->update(['status' => $newStatus]);

        $action = $newStatus === 'suspended' ? 'suspended' : 'activated';

        return redirect()->route('admin.applicants')
            ->with('success', "Applicant {$applicant->name} {$action} successfully.");
    }

    public function deleteApplicant(Applicant $applicant): RedirectResponse
    {
        $name = $applicant->name;

        DB::transaction(function () use ($applicant) {
            $applicant->load('application.documents');

            if ($application = $applicant->application) {
                foreach ($application->documents as $document) {
                    if ($document->file_path && Storage::exists($document->file_path)) {
                        Storage::delete($document->file_path);
                    }
                }
            }

            $applicant->delete();
        });

        return redirect()->route('admin.applicants')
            ->with('success', "Applicant {$name} and all associated records deleted successfully.");
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
