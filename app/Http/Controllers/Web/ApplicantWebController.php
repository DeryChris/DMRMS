<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Document;
use App\Models\Cycle;
use App\Events\ApplicationSubmitted;
use App\Events\DocumentUploaded;
use App\Services\Ai\AiGateway;
use App\Services\Application\CorpMatchingService;
use App\Services\Eligibility\EligibilityService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;

class ApplicantWebController extends Controller
{
    protected AiGateway $aiGateway;
    protected EligibilityService $eligibilityService;
    protected NotificationService $notificationService;
    protected CorpMatchingService $corpMatchingService;

    public function __construct(AiGateway $aiGateway, EligibilityService $eligibilityService, NotificationService $notificationService, CorpMatchingService $corpMatchingService)
    {
        $this->aiGateway = $aiGateway;
        $this->eligibilityService = $eligibilityService;
        $this->notificationService = $notificationService;
        $this->corpMatchingService = $corpMatchingService;
    }

    public function dashboard(): View
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application()->with('finalDecision.barrack')->first();
        $notifications = $applicant->notifications()->orderBy('sent_at', 'desc')->take(3)->get();
        $finalDecision = $application?->finalDecision;

        // Check for rejected documents
        $hasRejectedDocs = false;
        $rejectedDocTypes = [];
        $allDocsVerified = false;
        $rejectedReasons = [];
        if ($application) {
            $requiredDocs = ['birth_certificate', 'certificate', 'national_id', 'photograph'];
            $rejectedDocs = $application->documents()
                ->where('verification_status', 'rejected')
                ->get();
            $hasRejectedDocs = $rejectedDocs->isNotEmpty();
            $rejectedDocTypes = $rejectedDocs->map(fn($d) => str_replace('_', ' ', ucfirst($d->document_type)))->toArray();
            $rejectedReasons = $rejectedDocs->mapWithKeys(fn($d) => [
                str_replace('_', ' ', ucfirst($d->document_type)) => $d->rejection_reason ?? 'No specific reason provided.',
            ])->toArray();

            // Debug: log rejected doc query result
            \Illuminate\Support\Facades\Log::debug('Dashboard rejected docs check', [
                'applicant_id' => $applicant->id,
                'application_id' => $application->id,
                'application_status' => $application->status,
                'rejected_found' => $rejectedDocs->pluck('document_type')->toArray(),
                'has_rejected_docs' => $hasRejectedDocs,
            ]);

            $verified = $application->documents()
                ->whereIn('document_type', $requiredDocs)
                ->where('verification_status', 'verified')
                ->pluck('document_type')
                ->toArray();
            $allDocsVerified = empty(array_diff($requiredDocs, $verified));
        } else {
            \Illuminate\Support\Facades\Log::debug('Dashboard: no application found', [
                'applicant_id' => $applicant->id,
            ]);
        }

        // ── Document disqualification detection ────────────────────────
        // If the application is rejected and there are rejected documents,
        // this is a disqualification (not a fixable re-upload scenario).
        $documentDisqualified = false;
        $disqualificationDocTypes = [];
        $disqualificationReasons = [];
        if ($application && $application->status === 'rejected' && $hasRejectedDocs) {
            // Only show disqualification if this is NOT an eligibility failure
            // and there's no final decision yet (which has its own card).
            if (!$eligibilityFailed && !$finalDecision) {
                $documentDisqualified = true;
                $disqualificationDocTypes = $rejectedDocTypes;
                $disqualificationReasons = $rejectedReasons;
            }
        }

        // Can the applicant go back to edit application/documents?
        // Only when documents were rejected with fixable reasons (application not fully rejected).
        $canGoBack = !$documentDisqualified && $hasRejectedDocs;

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

        // Get eligibility result for rejection reasons
        $eligibilityResult = $application?->eligibilityResult;
        $eligibilityFailed = $application && $application->status === 'eligibility_failed';

        return view('applicant.dashboard', compact(
            'applicant', 'application', 'notifications', 'currentStage', 'stages',
            'statusText', 'finalDecision', 'hasRejectedDocs', 'rejectedDocTypes',
            'allDocsVerified', 'canGoBack', 'eligibilityResult', 'eligibilityFailed',
            'rejectedReasons', 'documentDisqualified', 'disqualificationDocTypes',
            'disqualificationReasons'
        ));
    }

    public function offerLetter()
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application()->with('cycle', 'finalDecision.barrack')->first();

        if (!$application || !in_array($application->status, ['selected', 'recruited'])) {
            abort(404, 'Offer letter not available.');
        }

        $pdf = Pdf::loadView('admin.offer-letter-pdf', [
            'applicant' => $applicant,
            'application' => $application,
            'cycle' => $application->cycle,
            'generatedAt' => now()->format('F j, Y'),
        ]);

        $filename = 'offer-letter-' . ($application->gaf_id ?? 'applicant') . '.pdf';

        return response()->streamDownload(fn() => print($pdf->output()), $filename);
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

        $eligibleSectors = $application
            ? $this->corpMatchingService->getEligibleSectors($application)
            : collect();

        $sectors = \App\Models\Sector::where('is_active', true)->orderBy('sort_order')->get();

        $existingSelections = $application
            ? $application->corpSelections()->with('corp.sector')->orderBy('priority')->get()
            : collect();

        $allCorps = \App\Models\Corp::where('is_active', true)->with('sector')->orderBy('name')->get();

        $serviceLabel = fn($v) => match (strtolower($v ?? '')) {
            'army' => 'Army',
            'navy' => 'Navy',
            'air_force' => 'Air Force',
            default => $v ?? '',
        };

        $allCorpsArray = $allCorps->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'service' => $serviceLabel($c->service),
            'description' => $c->description ?? '',
            'sector_id' => $c->sector_id,
            'sector_name' => $c->sector?->name ?? '',
        ])->values()->toArray();

        $sectorsArray = $sectors->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'service' => $s->service,
        ])->values()->toArray();

        $eligibleCorpIds = $application
            ? $this->corpMatchingService->getEligibleCorpIds($application)
            : [];

        $eligibleCorpIdsJson = json_encode($eligibleCorpIds);

        $sectorEligibility = $sectors->mapWithKeys(fn($s) => [
            $s->id => [
                'total' => $allCorps->where('sector_id', $s->id)->count(),
                'eligible' => $allCorps->where('sector_id', $s->id)
                    ->filter(fn($c) => in_array($c->id, $eligibleCorpIds))
                    ->count(),
            ],
        ]);

        $degreeFields = CorpMatchingService::getAllDegreeFields();

        return view('applicant.application-form', compact(
            'applicant', 'application', 'cycles', 'existing', 'defaultCycle',
            'currentStep', 'eligibleSectors', 'sectors', 'existingSelections',
            'allCorps', 'allCorpsArray', 'sectorsArray', 'degreeFields',
            'eligibleCorpIdsJson', 'sectorEligibility'
        ));
    }

    public function saveApplication(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $applicant = Auth::guard('applicant')->user();

        $isDraft = $request->input('action', 'save') === 'save';
        $action = $request->input('action', 'save');

        if ($action === 'submit') {
            $validator = Validator::make($request->all(), [
                'cycle_id' => 'required|exists:cycles,id',
                'education_level' => 'required|string|max:255',
                'institution_name' => 'required|string|max:255',
                'degree_field' => 'required|string|max:255',
                'year_obtained' => 'required|integer|min:1950|max:' . date('Y'),
                'height' => 'required|numeric|min:0.5|max:2.5',
                'criminal_record' => 'required|string|in:yes,no',
                'marital_status' => 'required|string|in:Single,Married,Divorced,Widowed',
                'nationality' => 'required|string|max:50|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ\s\'\-]+$/u',
                'national_id' => 'required|string|max:20',
                'residential_address' => 'required|string',
                'region' => 'required|string|max:50',
                'district' => 'required|string|max:50',
                'selected_sector_id' => 'required|exists:sectors,id',
                'corp_1' => 'required|exists:corps,id',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Please fill all required fields before submitting.'], 422);
                }
                return redirect()->back()
                    ->with('error', 'Please fill all required fields before submitting.')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $required = $isDraft ? 'nullable' : 'required';

        $appValidated = $request->validate([
            'cycle_id' => "{$required}|exists:cycles,id",
            'education_level' => "{$required}|string|max:255",
            'institution_name' => 'nullable|string|max:255',
            'degree_field' => 'nullable|string|max:255',
            'year_obtained' => 'nullable|integer|min:1950|max:' . date('Y'),
            'certificate_number' => 'nullable|string|max:255',
            'height' => 'nullable|numeric|min:0.5|max:2.5',
            'weight' => 'nullable|numeric|min:30|max:200',
            'health_conditions' => 'nullable|array',
            'criminal_record' => 'nullable|string|in:yes,no',
            'fitness_status' => 'nullable|string|max:255',
            'selected_sector_id' => "{$required}|exists:sectors,id",
            'corp_1' => "{$required}|exists:corps,id",
            'corp_2' => 'nullable|exists:corps,id',
            'corp_3' => 'nullable|exists:corps,id',
        ]);

        $personalValidated = $request->validate([
            'other_names' => 'nullable|string|max:50',
            'marital_status' => "{$required}|string|in:Single,Married,Divorced,Widowed",
            'nationality' => ["{$required}", 'string', 'max:50', 'regex:/^[A-Za-zÀ-ÖØ-öø-ÿ\s\'\-]+$/u'],
            'national_id' => "{$required}|string|max:20",
            'residential_address' => "{$required}|string",
            'region' => "{$required}|string|max:50",
            'district' => ["{$required}", 'string', 'max:50', 'regex:/^[A-Za-zÀ-ÖØ-öø-ÿ\s\'\-]+$/u'],
            'alternative_contact' => 'nullable|string|regex:/^[0-9]{10}$/',
        ]);

        // During draft saves, strip empty/null values so we only persist what was actually filled.
        // This prevents NOT NULL database columns from rejecting partial saves.
        if ($isDraft) {
            $appValidated = array_filter($appValidated, fn ($v) => $v !== null && $v !== '');
            $personalValidated = array_filter($personalValidated, fn ($v) => $v !== null && $v !== '');
        }

        $appValidated['criminal_record'] = !empty($appValidated['criminal_record']) && $appValidated['criminal_record'] === 'yes';

        if (!empty($personalValidated)) {
            $applicant->update($personalValidated);
        }

        $application = $applicant->application;

        $appValidated['current_step'] = $request->input('current_step', 1);

        if ($application) {
            if ($isDraft && $application->status === 'registered') {
                $appValidated['status'] = 'draft';
            }
            $application->update($appValidated);
            $message = 'Application updated successfully.';
        } else {
            // Provide defaults for NOT NULL columns so a minimal partial save (draft) never fails.
            $application = Application::create(array_merge([
                'cycle_id'          => null,
                'education_level'   => '',
                'institution_name'  => '',
                'qualification'     => '',
                'degree_field'      => '',
                'year_obtained'     => '',
                'height'            => 0,
                'criminal_record'   => false,
                'applicant_id'      => $applicant->id,
                'application_date'  => now(),
                'status'            => 'draft',
            ], $appValidated));
            $message = 'Application created successfully.';
        }

        // Save sector selection
        if ($request->has('selected_sector_id')) {
            $application->update(['selected_sector_id' => $request->input('selected_sector_id')]);
        }

        // Save corps selections (priorities 1-3)
        $application->corpSelections()->delete();
        for ($i = 1; $i <= 3; $i++) {
            $corpId = $request->input("corp_{$i}");
            if ($corpId) {
                $application->corpSelections()->create([
                    'corp_id' => $corpId,
                    'priority' => $i,
                ]);
            }
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

                $this->notificationService->applicationSubmitted($app);

                // Evaluate eligibility and store result (does NOT advance status — docs must be verified first)
                $this->eligibilityService->evaluate($app);

                $result = $app->fresh('eligibilityResult');
                if ($request->ajax() || $request->wantsJson()) {
                    $msg = 'Application submitted successfully. Your documents are pending verification by the recruitment board.';
                    return response()->json(['success' => true, 'message' => $msg]);
                }
                return redirect()->route('applicant.status')
                    ->with('success', 'Application submitted successfully. Your documents are being reviewed. You will be notified once they are verified.');
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

        $this->notificationService->applicationSubmitted($application);

        ApplicationSubmitted::dispatch($application);

        $this->notificationService->notifyAdminsByRole(
            'recruitment_officer',
            'new_application',
            'New Application Submitted',
            "{$application->applicant->name} ({$application->gaf_id}) has submitted an application and requires document review."
        );

        // Evaluate eligibility and store result (does NOT advance status — docs must be verified first)
        $this->eligibilityService->evaluate($application);

        return redirect()->route('applicant.status')
            ->with('success', 'Application submitted successfully. Your documents are being reviewed. You will be notified once they are verified.');
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

        // Check for rejected documents
        $rejectedDocTypes = [];
        $verificationStatuses = [];
        if ($application) {
            $rejected = $application->documents()
                ->where('verification_status', 'rejected')
                ->pluck('document_type')
                ->toArray();
            $rejectedDocTypes = array_map(fn($t) => str_replace('_', ' ', ucfirst($t)), $rejected);

            $verificationStatuses = $application->documents()
                ->pluck('verification_status', 'document_type')
                ->toArray();
        }

        return view('applicant.documents', compact(
            'applicant', 'documents', 'uploadedDocTypes', 'requiredDocTypes',
            'rejectedDocTypes', 'verificationStatuses'
        ));
    }

    private function validateWhiteBackground(string $path, string $mime): void
    {
        $img = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            default      => false,
        };

        if (!$img) {
            abort(422, 'Could not read image for background validation.');
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $threshold = 230;
        $points = [
            [(int)($w * 0.1), (int)($h * 0.03)], [(int)($w * 0.3), (int)($h * 0.03)],
            [(int)($w * 0.5), (int)($h * 0.03)], [(int)($w * 0.7), (int)($h * 0.03)],
            [(int)($w * 0.9), (int)($h * 0.03)], [(int)($w * 0.1), (int)($h * 0.08)],
            [(int)($w * 0.3), (int)($h * 0.08)], [(int)($w * 0.5), (int)($h * 0.08)],
            [(int)($w * 0.7), (int)($h * 0.08)], [(int)($w * 0.9), (int)($h * 0.08)],
        ];

        foreach ($points as [$px, $py]) {
            $rgb = imagecolorat($img, $px, $py);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            if ($r < $threshold || $g < $threshold || $b < $threshold) {
                imagedestroy($img);
                abort(422, 'Passport photo background must be plain white. The uploaded image has non-white areas on the edges.');
            }
        }

        imagedestroy($img);
    }

    public function uploadDocument(Request $request): RedirectResponse
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application;

        if (!$application) {
            return redirect()->route('applicant.application')->with('error', 'Please create an application first.');
        }

        $allowedMimes = $request->input('document_type') === 'photograph' ? 'jpg,jpeg,png' : 'pdf,jpg,jpeg,png';
        $validated = $request->validate([
            'document_type' => 'required|string|in:birth_certificate,national_id,certificate,photograph,medical_report,police_clearance,other',
            'file' => "required|file|mimes:{$allowedMimes}|max:5120",
        ]);

        if ($validated['document_type'] === 'photograph') {
            $file = $request->file('file');
            $photoPath = $file->getRealPath();
            $imageInfo = @getimagesize($photoPath);
            if (!$imageInfo || $imageInfo[0] !== 450 || $imageInfo[1] !== 540) {
                $msg = 'Passport photo must be exactly 450×540 pixels. Your image is ' . ($imageInfo[0] ?? 0) . '×' . ($imageInfo[1] ?? 0) . '.';
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg, 'errors' => ['file' => [$msg]]], 422);
                }
                return redirect()->back()->withErrors(['file' => $msg])->withInput();
            }
            try {
                $this->validateWhiteBackground($photoPath, $file->getMimeType());
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
                }
                throw $e;
            }
        }

        $file = $request->file('file');

        $newDoc = null;

        DB::transaction(function () use ($application, $applicant, $file, $validated, &$newDoc) {
            // Store the new file FIRST so if storage fails we don't lose the old doc
            $path = $file->store("documents/{$applicant->id}", 'public');

            // Delete the old document (file + DB record)
            $existing = Document::where('application_id', $application->id)
                ->where('document_type', $validated['document_type'])
                ->first();

            if ($existing) {
                Storage::disk('public')->delete($existing->file_path);
                $existing->delete();
            }

            // Create the new document record — always finalized + AI-verified instantly
            $newDoc = Document::create([
                'application_id' => $application->id,
                'document_type' => $validated['document_type'],
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'upload_date' => now(),
                'verification_status' => 'pending',
                'is_draft' => false,
                'finalized_at' => now(),
            ]);
        });

        // Trigger AI verification immediately (no need for "Proceed to Application" step)
        if ($newDoc) {
            DocumentUploaded::dispatch($newDoc);
        }

        // AJAX response — used by the frontend for auto-advance to next doc type
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Document uploaded. AI verification queued.',
                'document_type' => $validated['document_type'],
                'uploaded_doc_types' => $application->fresh()->documents()->pluck('document_type')->toArray(),
            ]);
        }

        return redirect()->route('applicant.documents')->with('success', 'Document uploaded. AI verification has been queued — you will see the result shortly.');
    }

    public function finalizeDocuments(): RedirectResponse
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application;

        if (!$application) {
            return redirect()->route('applicant.application')->with('error', 'Please create an application first.');
        }

        $requiredDocTypes = [
            'birth_certificate' => 'Birth Certificate',
            'certificate' => 'Educational Certificate',
            'national_id' => 'National ID (Ghana Card)',
            'photograph' => 'Passport Photograph',
        ];

        $uploadedDocTypes = $application->documents()->pluck('document_type')->toArray();
        $missingDocs = array_diff(array_keys($requiredDocTypes), $uploadedDocTypes);

        if (!empty($missingDocs)) {
            $missingLabels = array_map(fn($type) => $requiredDocTypes[$type] ?? $type, $missingDocs);
            return redirect()->route('applicant.documents')
                ->with('error', 'Cannot submit: missing required documents: ' . implode(', ', $missingLabels) . '. Please upload them first.');
        }

        $application->update(['documents_finalized' => true, 'documents_finalized_at' => now()]);

        $documents = $application->documents->where('is_draft', true);

        foreach ($documents as $document) {
            $document->update(['is_draft' => false, 'finalized_at' => now()]);
            DocumentUploaded::dispatch($document);
        }

        $this->notificationService->notifyAdminsByRole(
            'recruitment_officer',
            'document_uploaded',
            'Documents Finalized',
            "{$applicant->name} has finalized their document uploads for review."
        );

        return redirect()->route('applicant.application', ['step' => 6])->with('success', 'Documents submitted for review.');
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

    public function discardAllDocuments(): RedirectResponse
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application;

        if (!$application) {
            return redirect()->route('applicant.application')->with('error', 'Please create an application first.');
        }

        $drafts = $application->documents()->where('is_draft', true)->get();

        foreach ($drafts as $doc) {
            Storage::disk('public')->delete($doc->file_path);
            $doc->delete();
        }

        Log::info('All draft documents discarded', [
            'applicant_id' => $applicant->id,
            'count' => $drafts->count(),
        ]);

        return redirect()->route('applicant.documents')->with('success', 'All draft documents discarded successfully.');
    }

    public function dashboardStatus(): JsonResponse
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application;

        $hasRejectedDocs = false;
        $rejectedDocTypes = [];
        $rejectedReasons = [];
        $rejected = [];

        if ($application) {
            $rejectedDocs = $application->documents()
                ->where('verification_status', 'rejected')
                ->get();
            $rejected = $rejectedDocs->pluck('document_type')->toArray();
            $hasRejectedDocs = $rejectedDocs->isNotEmpty();
            $rejectedDocTypes = $rejectedDocs->map(fn($d) => str_replace('_', ' ', ucfirst($d->document_type)))->values()->toArray();
            $rejectedReasons = $rejectedDocs->mapWithKeys(fn($d) => [
                str_replace('_', ' ', ucfirst($d->document_type)) => $d->rejection_reason ?? 'No specific reason provided.',
            ])->toArray();
        }

        // Detect document disqualification (rejected docs + app rejected + no final decision)
        $documentDisqualified = false;
        $disqualificationDocTypes = [];
        $disqualificationReasons = [];
        if ($application && $application->status === 'rejected' && $hasRejectedDocs) {
            $finalDecision = $application->finalDecision;
            if (!$finalDecision) {
                $documentDisqualified = true;
                $disqualificationDocTypes = $rejectedDocTypes;
                $disqualificationReasons = $rejectedReasons;
            }
        }

        return response()->json([
            'hasRejectedDocs' => $hasRejectedDocs,
            'rejectedDocTypes' => $rejectedDocTypes,
            'rejectedReasons' => $rejectedReasons,
            'rejectedCount' => count($rejected),
            'applicationStatus' => $application?->status,
            'documentDisqualified' => $documentDisqualified,
            'disqualificationDocTypes' => $disqualificationDocTypes,
            'disqualificationReasons' => (object) $disqualificationReasons,
        ]);
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
