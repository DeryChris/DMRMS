<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Document;
use App\Models\Cycle;
use App\Services\Ai\AiGateway;
use App\Services\Eligibility\EligibilityService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ApplicantWebController extends Controller
{
    protected AiGateway $aiGateway;
    protected EligibilityService $eligibilityService;
    protected NotificationService $notificationService;

    public function __construct(AiGateway $aiGateway, EligibilityService $eligibilityService, NotificationService $notificationService)
    {
        $this->aiGateway = $aiGateway;
        $this->eligibilityService = $eligibilityService;
        $this->notificationService = $notificationService;
    }

    public function dashboard(): View
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application()->with('finalDecision.barrack')->first();
        $notifications = $applicant->notifications()->orderBy('sent_at', 'desc')->take(3)->get();
        $finalDecision = $application?->finalDecision;

        $stageMap = [
            'registered' => 1,
            'draft' => 2,
            'submitted' => 3,
            'documents_verified' => 4,
            'eligibility_passed' => 5,
            'eligibility_failed' => 5,
            'shortlisted' => 6,
            'appointment_scheduled' => 7,
            'screening_completed' => 8,
            'final_decision_pending' => 9,
            'selected' => 10,
            'rejected' => 10,
            'disqualified' => 10,
            'reserve' => 10,
            'recruited' => 10,
        ];

        $currentStage = $stageMap[$application?->status] ?? 1;
        $statusText = $application?->status ?? 'registered';

        $stages = [
            ['title' => 'Registered', 'key' => 'registered', 'status' => $currentStage >= 1 ? ($currentStage > 1 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Draft', 'key' => 'draft', 'status' => $currentStage >= 2 ? ($currentStage > 2 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Submitted', 'key' => 'submitted', 'status' => $currentStage >= 3 ? ($currentStage > 3 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Docs Verified', 'key' => 'documents_verified', 'status' => $currentStage >= 4 ? ($currentStage > 4 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Eligibility', 'key' => 'eligibility', 'status' => $currentStage >= 5 ? ($currentStage > 5 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Shortlisted', 'key' => 'shortlisted', 'status' => $currentStage >= 6 ? ($currentStage > 6 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Appointment', 'key' => 'appointment', 'status' => $currentStage >= 7 ? ($currentStage > 7 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Screening', 'key' => 'screening', 'status' => $currentStage >= 8 ? ($currentStage > 8 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Decision Pending', 'key' => 'decision_pending', 'status' => $currentStage >= 9 ? ($currentStage > 9 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Final Decision', 'key' => 'decision', 'status' => $currentStage >= 10 ? 'completed' : 'pending'],
        ];

        return view('applicant.dashboard', compact('applicant', 'application', 'notifications', 'currentStage', 'stages', 'statusText', 'finalDecision'));
    }

    public function applicationForm(Request $request): View
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application;
        $cycles = Cycle::where('status', 'active')->get();

        $existing = $application;
        $defaultCycle = $application?->cycle_id ?? $cycles->first()?->id;

        $savedStep = (int) ($application?->current_step ?? 1);
        $queryStep = (int) $request->query('step', 0);
        $currentStep = $queryStep > $savedStep ? $queryStep : $savedStep;

        return view('applicant.application-form', compact('applicant', 'application', 'cycles', 'existing', 'defaultCycle', 'currentStep'));
    }

    public function saveApplication(Request $request): RedirectResponse
    {
        $applicant = Auth::guard('applicant')->user();

        $isDraft = $request->input('action', 'save') === 'save';

        $required = $isDraft ? 'nullable' : 'required';

        $appValidated = $request->validate([
            'cycle_id' => 'required|exists:cycles,id',
            'education_level' => "{$required}|string|max:255",
            'institution_name' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'year_obtained' => 'nullable|integer|min:1950|max:' . date('Y'),
            'certificate_number' => 'nullable|string|max:255',
            'height' => 'nullable|numeric|min:0.5|max:2.5',
            'weight' => 'nullable|numeric|min:30|max:200',
            'health_conditions' => 'nullable|array',
            'criminal_record' => 'nullable|string|in:yes,no',
            'fitness_status' => 'nullable|string|max:255',
        ]);

        $personalValidated = $request->validate([
            'other_names' => 'nullable|string|max:50',
            'marital_status' => "{$required}|string|in:Single,Married,Divorced,Widowed",
            'nationality' => "{$required}|string|max:50",
            'national_id' => "{$required}|string|max:20",
            'residential_address' => "{$required}|string",
            'region' => "{$required}|string|max:50",
            'district' => "{$required}|string|max:50",
            'alternative_contact' => 'nullable|string|max:15',
        ]);

        $appValidated['criminal_record'] = !empty($appValidated['criminal_record']) && $appValidated['criminal_record'] === 'yes';

        $applicant->update($personalValidated);

        $action = $request->input('action', 'save');
        $application = $applicant->application;

        $appValidated['current_step'] = $request->input('current_step', 1);

        if ($application) {
            if ($isDraft && $application->status === 'registered') {
                $appValidated['status'] = 'draft';
            }
            $application->update($appValidated);
            $message = 'Application updated successfully.';
        } else {
            Application::create(array_merge($appValidated, [
                'applicant_id' => $applicant->id,
                'application_date' => now(),
                'status' => 'draft',
            ]));
            $message = 'Application created successfully.';
        }

        if ($action === 'submit') {
            $app = $applicant->application()->first();
            if ($app && in_array($app->status, ['draft', 'registered'])) {
                $requiredDocTypes = ['birth_certificate', 'certificate', 'national_id', 'photograph'];
                $uploadedDocTypes = $app->documents()->whereIn('document_type', $requiredDocTypes)->pluck('document_type')->toArray();
                $missingDocs = array_diff($requiredDocTypes, $uploadedDocTypes);

                if (!empty($missingDocs)) {
                    $missingLabels = array_map(fn($type) => str_replace('_', ' ', ucfirst($type)), $missingDocs);
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => 'Missing documents: ' . implode(', ', $missingLabels)], 422);
                    }
                    return redirect()->route('applicant.application')
                        ->with('error', 'Cannot submit: missing required documents: ' . implode(', ', $missingLabels) . '. Please upload them first.');
                }

                $app->update(['status' => 'submitted', 'submitted_at' => now()]);

                $this->eligibilityService->evaluate($app);

                $result = $app->fresh('eligibilityResult');
                if ($result->overall_status === 'eligible') {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => true, 'message' => 'Application submitted and eligibility check passed!']);
                    }
                    return redirect()->route('applicant.status')->with('success', 'Application submitted and eligibility check passed! You have been advanced to the shortlisting pool.');
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => true, 'message' => 'Application submitted.']);
                }
                return redirect()->route('applicant.status')->with('success', 'Application submitted.');
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('applicant.application')->with('success', $message);
    }

    public function submitApplication(Request $request): RedirectResponse
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application;

        if (!$application) {
            return redirect()->route('applicant.application')->with('error', 'Please create an application first.');
        }

        if (!in_array($application->status, ['draft', 'registered'])) {
            return redirect()->route('applicant.status')->with('info', 'Application is already submitted.');
        }

        $application->update(['status' => 'submitted', 'submitted_at' => now()]);

        $this->notificationService->notifyAdminsByRole(
            'recruitment_officer',
            'new_application',
            'New Application Submitted',
            "{$application->applicant->name} ({$application->gaf_id}) has submitted an application and requires document review."
        );

        $this->eligibilityService->evaluate($application);

        $result = $application->fresh('eligibilityResult');
        if ($result && $result->overall_status === 'eligible') {
            return redirect()->route('applicant.status')->with('success', 'Application submitted and eligibility check passed! You have been advanced to the shortlisting pool.');
        }

        return redirect()->route('applicant.status')->with('success', 'Application submitted.');
    }

    public function documents(): View
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application;

        if ($application) {
            $documents = $application->documents()->orderBy('created_at', 'desc')->get();
            $uploadedDocTypes = $documents->pluck('document_type')->toArray();
        } else {
            $documents = collect();
            $uploadedDocTypes = [];
        }

        $requiredDocTypes = [
            'birth_certificate' => 'Birth Certificate',
            'certificate' => 'Educational Certificate',
            'national_id' => 'National ID (Ghana Card)',
            'photograph' => 'Passport Photograph',
        ];

        return view('applicant.documents', compact('applicant', 'documents', 'uploadedDocTypes', 'requiredDocTypes'));
    }

    public function uploadDocument(Request $request): RedirectResponse
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application;

        if (!$application) {
            return redirect()->route('applicant.application')->with('error', 'Please create an application first.');
        }

        $validated = $request->validate([
            'document_type' => 'required|string|in:birth_certificate,national_id,certificate,photograph,medical_report,police_clearance,other',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->store("documents/{$applicant->id}", 'public');

        Document::create([
            'application_id' => $application->id,
            'document_type' => $validated['document_type'],
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'upload_date' => now(),
            'verification_status' => 'pending',
        ]);

        $this->notificationService->notifyAdminsByRole(
            'recruitment_officer',
            'document_uploaded',
            'New Document Uploaded',
            "{$applicant->name} uploaded a new document ({$validated['document_type']}) for review."
        );

        return redirect()->route('applicant.documents')->with('success', 'Document uploaded successfully.');
    }

    public function deleteDocument(int $id): RedirectResponse
    {
        $applicant = Auth::guard('applicant')->user();
        $document = Document::whereHas('application', function ($q) use ($applicant) {
            $q->where('applicant_id', $applicant->id);
        })->findOrFail($id);

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('applicant.documents')->with('success', 'Document deleted successfully.');
    }

    public function status(): View
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application()->with(['eligibilityResult', 'screeningResult', 'finalDecision', 'appointment', 'verificationCode'])->first();

        $stageMap = [
            'registered' => 1,
            'draft' => 2,
            'submitted' => 3,
            'documents_verified' => 4,
            'eligibility_passed' => 5,
            'eligibility_failed' => 5,
            'shortlisted' => 6,
            'appointment_scheduled' => 7,
            'screening_completed' => 8,
            'final_decision_pending' => 9,
            'selected' => 10,
            'rejected' => 10,
            'disqualified' => 10,
            'reserve' => 10,
            'recruited' => 10,
        ];

        $currentStage = $stageMap[$application?->status] ?? 1;

        $stages = [
            ['title' => 'Registered', 'status' => 'completed', 'date' => $applicant->created_at?->format('Y-m-d'), 'note' => 'Account created successfully.'],
            ['title' => 'Draft', 'status' => $currentStage >= 2 ? 'completed' : 'pending', 'date' => null, 'note' => $currentStage >= 2 ? 'Application form started.' : 'Not yet started.'],
            ['title' => 'Submitted', 'status' => $currentStage >= 3 ? 'completed' : 'pending', 'date' => $application?->submitted_at?->format('Y-m-d'), 'note' => $currentStage >= 3 ? 'Form submitted for review.' : 'Awaiting submission.'],
            ['title' => 'Documents Verified', 'status' => $currentStage >= 4 ? ($currentStage > 4 ? 'completed' : 'current') : 'pending', 'date' => null, 'note' => $currentStage >= 4 ? 'All required documents verified.' : 'Documents pending verification.'],
            ['title' => 'Eligibility', 'status' => $currentStage >= 5 ? ($currentStage > 5 ? 'completed' : 'current') : 'pending', 'date' => $application?->eligibilityResult?->created_at?->format('Y-m-d'), 'note' => $application?->eligibilityResult ? ($application?->eligibilityResult?->overall_status === 'eligible' ? 'Eligible.' : 'Not eligible.') : 'Awaiting eligibility check.'],
            ['title' => 'Shortlisted', 'status' => $currentStage >= 6 ? ($currentStage > 6 ? 'completed' : 'current') : 'pending', 'date' => null, 'note' => 'Awaiting shortlisting decision.'],
            ['title' => 'Appointment', 'status' => $currentStage >= 7 ? ($currentStage > 7 ? 'completed' : 'current') : 'pending', 'date' => $application?->appointment?->appointment_date?->format('Y-m-d'), 'note' => $application?->appointment ? 'Appointment scheduled.' : 'Screening appointment to be scheduled.'],
            ['title' => 'Screening', 'status' => $currentStage >= 8 ? ($currentStage > 8 ? 'completed' : 'current') : 'pending', 'date' => $application?->screeningResult?->created_at?->format('Y-m-d'), 'note' => $application?->screeningResult ? 'Screening completed.' : 'Medical, fitness, and interview pending.'],
            ['title' => 'Decision Pending', 'status' => $currentStage >= 9 ? ($currentStage > 9 ? 'completed' : 'current') : 'pending', 'date' => null, 'note' => 'Awaiting final committee review.'],
            ['title' => 'Final Decision', 'status' => $currentStage >= 10 ? 'completed' : 'pending', 'date' => $application?->finalDecision?->created_at?->format('Y-m-d'), 'note' => $application?->finalDecision ? 'Final decision rendered.' : 'Final decision pending.'],
        ];

        $eligible = ($application?->eligibilityResult?->overall_status ?? '') === 'eligible';
        $verificationCode = $application?->verificationCode;

        $barracks = \App\Models\Barrack::where('region', $applicant->region)
            ->where('is_active', true)
            ->get();

        return view('applicant.status', compact('applicant', 'application', 'currentStage', 'stages', 'eligible', 'verificationCode', 'barracks'));
    }

    public function appointment(): View
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application;
        $appointment = $application?->appointment;
        $verificationCode = $application?->verificationCode;

        return view('applicant.appointment', compact('applicant', 'application', 'appointment', 'verificationCode'));
    }

    public function notifications(): View
    {
        $applicant = Auth::guard('applicant')->user();
        $allNotifications = $applicant->notifications()->orderBy('sent_at', 'desc')->paginate(20);

        return view('applicant.notifications', compact('applicant', 'allNotifications'));
    }
}
