<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Appointment;
use App\Models\Cycle;
use App\Models\Document;
use App\Models\FinalDecision;
use App\Models\ReserveList;
use App\Models\ScreeningResult;
use App\Models\VerificationCode;
use App\Models\AiUsage;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function dashboardStats(): JsonResponse
    {
        $totalApplicants = Applicant::count();
        $submittedApplications = Application::where('status', 'submitted')->count();
        $verifiedDocuments = Document::where('verification_status', 'verified')->count();
        $scheduledAppointments = Appointment::count();
        $activeCycles = Cycle::where('status', 'active')->count();
        $shortlisted = Application::where('status', 'shortlisted')->count();
        $selected = FinalDecision::where('decision', 'admitted')->count();
        $verifiedCodes = Applicant::whereNotNull('email_verified_at')->count();

        $recentApplications = Application::with('applicant')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn($app) => [
                'id'           => $app->id,
                'applicant'    => $app->applicant?->full_name ?? 'N/A',
                'status'       => $app->status,
                'cycle'        => $app->cycle?->name,
                'created_at'   => $app->created_at,
            ]);

        $statusBreakdown = Application::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'data' => [
                'kpi_cards' => [
                    ['label' => 'Total Applicants', 'value' => $totalApplicants],
                    ['label' => 'Submitted Applications', 'value' => $submittedApplications],
                    ['label' => 'Verified Documents', 'value' => $verifiedDocuments],
                    ['label' => 'Scheduled Appointments', 'value' => $scheduledAppointments],
                    ['label' => 'Active Cycles', 'value' => $activeCycles],
                    ['label' => 'Shortlisted', 'value' => $shortlisted],
                    ['label' => 'Selected', 'value' => $selected],
                    ['label' => 'Email Verified', 'value' => $verifiedCodes],
                ],
                'chart_data' => [
                    'status_breakdown'    => $statusBreakdown,
                    'recent_applications' => $recentApplications,
                ],
            ],
        ]);
    }

    public function applications(Request $request, $id = null): JsonResponse
    {
        if ($id) {
            $application = Application::with(['applicant', 'cycle', 'documents', 'eligibilityResult', 'appointment', 'finalDecision'])
                ->findOrFail($id);

            return response()->json(['data' => $application]);
        }

        $query = Application::with(['applicant', 'cycle']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('region')) {
            $query->whereHas('applicant', fn($q) => $q->where('region', $request->region));
        }
        if ($request->filled('cycle_id')) {
            $query->where('cycle_id', $request->cycle_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('applicant', fn($q) => $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 20);

        return response()->json(['data' => $applications]);
    }

    public function updateApplicationStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:draft,submitted,documents_verified,eligibility_passed,eligibility_failed,shortlisted,appointment_scheduled,screening_completed,final_decision_pending,selected,rejected',
            'remarks' => 'nullable|string|max:500',
        ]);

        $application = Application::findOrFail($id);
        $application->update([
            'status'         => $validated['status'],
            'status_remarks' => $validated['remarks'] ?? null,
        ]);

        return response()->json([
            'message'     => 'Application status updated.',
            'application' => $application,
        ]);
    }

    public function shortlist(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'applicant_ids' => 'required|array',
            'applicant_ids.*' => 'exists:applicants,id',
        ]);

        $applications = Application::whereIn('applicant_id', $validated['applicant_ids'])
            ->where('status', 'eligibility_passed')
            ->get();

        $processed = [];

        foreach ($applications as $app) {
            $app->update(['status' => 'shortlisted']);

            $codeValue = strtoupper(\Illuminate\Support\Str::random(12));

            VerificationCode::create([
                'application_id' => $app->id,
                'applicant_id' => $app->applicant_id,
                'code_value' => $codeValue,
                'type' => 'entry',
                'issue_date' => now(),
                'expiry_date' => now()->addMonths(6),
                'used_status' => false,
            ]);

            $this->notificationService->shortlisted($app, $codeValue);

            $processed[] = [
                'application_id' => $app->id,
                'gaf_id' => $app->gaf_id,
                'verification_code' => $codeValue,
            ];
        }

        return response()->json([
            'message'    => count($processed) . ' applicants shortlisted successfully.',
            'processed'  => $processed,
        ]);
    }

    public function verifyDocument(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'verification_status' => 'required|in:verified,rejected',
            'remarks'             => 'nullable|string|max:500',
        ]);

        $document = Document::findOrFail($id);
        $document->update([
            'verification_status' => $validated['verification_status'],
            'verified_by'         => $request->user()->id,
            'verified_at'         => now(),
            'remarks'             => $validated['remarks'] ?? null,
        ]);

        return response()->json([
            'message'  => 'Document verification updated.',
            'document' => $document,
        ]);
    }

    public function slots(Request $request): JsonResponse
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'cycle_id'    => 'required|exists:cycles,id',
                'date'        => 'required|date',
                'start_time'  => 'required|date_format:H:i',
                'end_time'    => 'required|date_format:H:i|after:start_time',
                'capacity'    => 'required|integer|min:1',
                'location'    => 'required|string|max:255',
            ]);

            $appointments = [];
            for ($i = 0; $i < $validated['capacity']; $i++) {
                $appointments[] = Appointment::create([
                    'scheduled_date' => $validated['date'],
                    'scheduled_time' => $validated['start_time'],
                    'venue'          => $validated['location'],
                    'status'         => 'available',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Slots created successfully.',
                'data'    => $appointments,
            ], 201);
        }

        $query = Appointment::query();

        if ($request->filled('cycle_id')) {
            $query->whereHas('application', fn($q) => $q->where('cycle_id', $request->cycle_id));
        }
        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('scheduled_date')->paginate(20),
        ]);
    }

    public function appointments(Request $request): JsonResponse
    {
        $query = Appointment::with(['application.applicant', 'application.cycle']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('cycle_id')) {
            $query->whereHas('application', fn($q) => $q->where('cycle_id', $request->cycle_id));
        }
        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('created_at', 'desc')->paginate(20),
        ]);
    }

    public function screeningResults(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'applicant_id'          => 'required|exists:applicants,id',
            'medical_status'        => 'nullable|in:fit,unfit,pending',
            'medical_notes'         => 'nullable|string|max:2000',
            'fitness_score'         => 'nullable|numeric|min:0|max:100',
            'fitness_details'       => 'nullable|string|max:2000',
            'interview_score'       => 'nullable|numeric|min:0|max:100',
            'interview_notes'       => 'nullable|string|max:2000',
            'interview_decision'    => 'nullable|in:pass,fail',
            'overall_status'        => 'nullable|in:qualified,disqualified',
            'reviewed_by'           => 'nullable|integer|exists:users,id',
        ]);

        $application = Application::where('applicant_id', $validated['applicant_id'])->firstOrFail();

        $result = ScreeningResult::updateOrCreate(
            ['application_id' => $application->id],
            array_merge($validated, ['conducted_by' => $request->user()->id, 'conducted_at' => now()])
        );

        if (($validated['overall_status'] ?? null) === 'qualified') {
            $application->update(['status' => 'final_decision_pending']);
            $this->notificationService->finalDecisionPending($application->fresh());
        } elseif (($validated['overall_status'] ?? null) === 'disqualified') {
            $application->update(['status' => 'disqualified']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Screening results recorded.',
            'data'    => $result,
        ]);
    }

    public function finalizeSelection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'applicant_id' => 'required|exists:applicants,id',
            'status'       => 'required|in:selected,rejected,reserve',
            'remarks'      => 'nullable|string|max:1000',
            'offer_letter' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $application = Application::where('applicant_id', $validated['applicant_id'])->firstOrFail();

        $statusMapping = [
            'selected' => ['decision' => 'admitted', 'app_status' => 'selected'],
            'rejected' => ['decision' => 'rejected', 'app_status' => 'rejected'],
            'reserve' => ['decision' => 'reserve', 'app_status' => 'reserve'],
        ];

        $mapped = $statusMapping[$validated['status']];

        $decision = FinalDecision::updateOrCreate(
            ['application_id' => $application->id],
            [
                'decision'         => $mapped['decision'],
                'decision_reason'  => $validated['remarks'] ?? null,
                'committee_members' => [$request->user()->id],
                'committee_approved_at' => now(),
                'committee_approved_by' => $request->user()->id,
                'decision_date'    => now(),
            ]
        );

        Application::where('applicant_id', $validated['applicant_id'])
            ->update(['status' => $mapped['app_status']]);

        if ($validated['status'] === 'reserve') {
            $lastPosition = ReserveList::max('position') ?? 0;
            ReserveList::create([
                'application_id' => $application->id,
                'priority_score' => $application->ai_ranking_score ?? 0,
                'position' => $lastPosition + 1,
                'notes' => $validated['remarks'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Selection finalized.',
            'data'    => $decision,
        ]);
    }

    public function exportReports(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'     => 'required|in:pdf,excel',
            'report'   => 'required|in:applicants,applications,selections,screening',
            'cycle_id' => 'nullable|exists:cycles,id',
            'status'   => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report generation initiated.',
            'data'    => [
                'download_url' => url("api/v1/admin/reports/download?type={$validated['type']}&report={$validated['report']}"),
                'format'       => $validated['type'],
            ],
        ]);
    }

    public function cycles(Request $request, $id = null): JsonResponse
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name'                 => 'required|string|max:255',
                'cycle_code'           => 'required|string|max:50|unique:cycles,cycle_code',
                'start_date'           => 'required|date',
                'end_date'             => 'required|date|after:start_date',
                'application_deadline' => 'required|date|before_or_equal:end_date',
                'total_vacancies'      => 'required|integer|min:1',
                'requirements'         => 'nullable|array',
                'ai_enabled'           => 'boolean',
                'status'               => 'sometimes|in:active,inactive,closed',
            ]);

            $validated['requirements'] = $validated['requirements'] ?? [];
            $validated['ai_enabled'] = $validated['ai_enabled'] ?? false;
            $validated['created_by'] = $request->user()->id;
            $validated['status'] = $validated['status'] ?? 'inactive';

            $cycle = Cycle::create($validated);

            return response()->json(['success' => true, 'message' => 'Cycle created.', 'data' => $cycle], 201);
        }

        if ($id) {
            $cycle = Cycle::withCount(['vouchers', 'applications'])->findOrFail($id);

            return response()->json(['success' => true, 'data' => $cycle]);
        }

        if ($request->isMethod('put') && $id) {
            $cycle = Cycle::findOrFail($id);
            $validated = $request->validate([
                'name'                 => 'sometimes|string|max:255',
                'cycle_code'           => 'sometimes|string|max:50|unique:cycles,cycle_code,' . $id,
                'start_date'           => 'sometimes|date',
                'end_date'             => 'sometimes|date|after:start_date',
                'application_deadline' => 'sometimes|date',
                'total_vacancies'      => 'sometimes|integer|min:1',
                'requirements'         => 'nullable|array',
                'ai_enabled'           => 'boolean',
                'status'               => 'sometimes|in:active,inactive,closed',
            ]);

            $cycle->update($validated);

            return response()->json(['success' => true, 'message' => 'Cycle updated.', 'data' => $cycle]);
        }

        if ($request->isMethod('delete') && $id) {
            Cycle::findOrFail($id)->delete();

            return response()->json(['success' => true, 'message' => 'Cycle deleted.']);
        }

        $query = Cycle::withCount(['vouchers', 'applications']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(['success' => true, 'data' => $query->orderBy('start_date', 'desc')->paginate(20)]);
    }

    public function users(Request $request, $id = null): JsonResponse
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name'  => 'nullable|string|max:255',
                'email'      => 'required|email|max:255|unique:administrators,email',
                'password'   => 'required|string|min:8|confirmed',
                'role'       => 'required|in:admin,screener,super_admin',
            ]);

            $validated['password'] = Hash::make($validated['password']);
            $user = Administrator::create($validated);

            return response()->json(['success' => true, 'message' => 'User created.', 'data' => $user], 201);
        }

        if ($id) {
            $user = Administrator::findOrFail($id);

            return response()->json(['success' => true, 'data' => $user]);
        }

        if ($request->isMethod('put') && $id) {
            $user = Administrator::findOrFail($id);
            $validated = $request->validate([
                'first_name' => 'sometimes|string|max:255',
                'last_name'  => 'sometimes|string|max:255',
                'email'      => 'sometimes|email|max:255|unique:administrators,email,' . $id,
                'password'   => 'sometimes|string|min:8|confirmed',
                'role'       => 'sometimes|in:admin,screener,super_admin',
            ]);

            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }
            $user->update($validated);

            return response()->json(['success' => true, 'message' => 'User updated.', 'data' => $user]);
        }

        if ($request->isMethod('delete') && $id) {
            Administrator::findOrFail($id)->delete();

            return response()->json(['success' => true, 'message' => 'User deleted.']);
        }

        return response()->json([
            'success' => true,
            'data'    => Administrator::orderBy('created_at', 'desc')->paginate(20),
        ]);
    }

    public function aiConfig(Request $request): JsonResponse
    {
        $admin = $request->user();
        if ($admin->role !== 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only super admin can update AI configuration.'], 403);
        }

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'provider'       => 'sometimes|string|max:255',
                'model'          => 'sometimes|string|max:255',
                'temperature'    => 'sometimes|numeric|min:0|max:2',
                'max_tokens'     => 'sometimes|integer|min:100|max:10000',
                'features'       => 'sometimes|array',
                'features.eligibility_analysis' => 'boolean',
                'features.document_verification' => 'boolean',
                'features.chatbot' => 'boolean',
                'features.ranking' => 'boolean',
                'features.insights' => 'boolean',
                'is_active'      => 'sometimes|boolean',
            ]);

            $existingConfig = cache('ai_config', []);
            $newConfig = array_merge($existingConfig, $validated);
            cache(['ai_config' => $newConfig], now()->addYear());

            return response()->json([
                'success' => true,
                'message' => 'AI configuration updated.',
                'data'    => $newConfig,
            ]);
        }

        $config = cache('ai_config', [
            'provider'    => config('ai.default_provider'),
            'model'       => config('ai.openai.model'),
            'temperature' => config('ai.openai.temperature'),
            'max_tokens'  => config('ai.openai.max_tokens'),
            'features'    => [
                'eligibility_analysis'  => in_array('ai_eligibility_check', config('subscription.features.ai_eligibility_check', [])),
                'document_verification' => in_array('ai_document_analysis', config('subscription.features.ai_document_analysis', [])),
                'chatbot'               => in_array('ai_chatbot', config('subscription.features.ai_chatbot', [])),
                'ranking'               => in_array('ai_candidate_ranking', config('subscription.features.ai_candidate_ranking', [])),
                'insights'              => in_array('ai_insights_dashboard', config('subscription.features.ai_insights_dashboard', [])),
            ],
            'is_active'   => config('ai.features_enabled.pro', false),
        ]);

        return response()->json(['success' => true, 'data' => $config]);
    }

    public function aiUsage(): JsonResponse
    {
        $stats = AiUsage::selectRaw('
            COUNT(*) as total_records,
            COALESCE(SUM(total_tokens), 0) as total_tokens,
            COALESCE(SUM(total_cost), 0) as total_cost,
            COALESCE(SUM(requests_count), 0) as total_requests
        ')->first();

        $dailyUsage = AiUsage::selectRaw('date, SUM(requests_count) as requests, SUM(total_cost) as cost')
            ->where('date', '>=', now()->subDays(30)->format('Y-m-d'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $byAdmin = AiUsage::selectRaw('admin_id, SUM(requests_count) as count, SUM(total_cost) as cost')
            ->groupBy('admin_id')
            ->with('administrator')
            ->get()
            ->map(fn($item) => [
                'admin' => $item->administrator?->email ?? "Admin #{$item->admin_id}",
                'count' => $item->count,
                'cost'  => $item->cost,
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'summary'  => $stats,
                'daily'    => $dailyUsage,
                'by_admin' => $byAdmin,
            ],
        ]);
    }

    public function subscription(Request $request): JsonResponse
    {
        $admin = $request->user();

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'plan'          => 'required|in:basic,professional,enterprise',
                'billing_cycle' => 'required|in:monthly,yearly',
            ]);

            $admin->update([
                'subscription_tier'       => $validated['plan'],
                'subscription_expires_at' => $validated['billing_cycle'] === 'yearly' ? now()->addYear() : now()->addMonth(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription updated.',
                'data'    => [
                    'subscription_tier'       => $admin->subscription_tier,
                    'subscription_expires_at' => $admin->subscription_expires_at,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'subscription_tier'       => $admin->subscription_tier,
                'subscription_expires_at' => $admin->subscription_expires_at,
            ],
        ]);
    }
}
