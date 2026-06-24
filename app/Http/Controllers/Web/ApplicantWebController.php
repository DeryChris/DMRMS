<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Document;
use App\Models\Cycle;
use App\Services\Ai\AiGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ApplicantWebController extends Controller
{
    protected AiGateway $aiGateway;

    public function __construct(AiGateway $aiGateway)
    {
        $this->aiGateway = $aiGateway;
    }

    public function dashboard(): View
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application;
        $notifications = $applicant->notifications()->orderBy('created_at', 'desc')->take(3)->get();

        $stageMap = [
            'registered' => 1,
            'draft' => 1,
            'submitted' => 2,
            'eligibility_passed' => 3,
            'eligibility_failed' => 3,
            'shortlisted' => 4,
            'appointment_scheduled' => 5,
            'screening_completed' => 6,
            'selected' => 7,
            'rejected' => 7,
        ];

        $currentStage = $stageMap[$application?->status] ?? 1;
        $statusText = $application?->status ?? 'registered';

        $stages = [
            ['title' => 'Registered', 'key' => 'registered', 'status' => $currentStage >= 1 ? ($currentStage > 1 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Application Submitted', 'key' => 'submitted', 'status' => $currentStage >= 2 ? ($currentStage > 2 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Eligibility', 'key' => 'eligibility', 'status' => $currentStage >= 3 ? ($currentStage > 3 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Shortlisted', 'key' => 'shortlisted', 'status' => $currentStage >= 4 ? ($currentStage > 4 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Appointment', 'key' => 'appointment', 'status' => $currentStage >= 5 ? ($currentStage > 5 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Screening', 'key' => 'screening', 'status' => $currentStage >= 6 ? ($currentStage > 6 ? 'completed' : 'current') : 'pending'],
            ['title' => 'Decision', 'key' => 'decision', 'status' => $currentStage >= 7 ? 'completed' : 'pending'],
        ];

        return view('applicant.dashboard', compact('applicant', 'application', 'notifications', 'currentStage', 'stages', 'statusText'));
    }

    public function applicationForm(): View
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application;
        $cycles = Cycle::where('status', 'active')->get();

        return view('applicant.application-form', compact('applicant', 'application', 'cycles'));
    }

    public function saveApplication(Request $request): RedirectResponse
    {
        $applicant = Auth::guard('applicant')->user();

        $validated = $request->validate([
            'cycle_id' => 'required|exists:cycles,id',
            'education_level' => 'required|string|max:255',
            'institution_name' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'year_obtained' => 'nullable|integer|min:1950|max:' . date('Y'),
            'certificate_number' => 'nullable|string|max:255',
            'height' => 'nullable|numeric|min:100|max:250',
            'weight' => 'nullable|numeric|min:30|max:200',
            'health_conditions' => 'nullable|array',
            'criminal_record' => 'nullable|string|in:yes,no',
            'fitness_status' => 'nullable|string|max:255',
        ]);

        $validated['criminal_record'] = $validated['criminal_record'] === 'yes';

        $action = $request->input('action', 'save');
        $application = $applicant->application;

        if ($application) {
            $application->update($validated);
            $message = 'Application updated successfully.';
        } else {
            Application::create(array_merge($validated, [
                'applicant_id' => $applicant->id,
                'application_date' => now(),
                'status' => 'draft',
            ]));
            $message = 'Application created successfully.';
        }

        if ($action === 'submit') {
            $app = $applicant->application;
            if ($app && $app->status === 'draft') {
                $app->update(['status' => 'submitted', 'submitted_at' => now()]);
                return redirect()->route('applicant.status')->with('success', 'Application submitted successfully.');
            }
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

        if ($application->status !== 'draft') {
            return redirect()->route('applicant.status')->with('info', 'Application is already submitted.');
        }

        $application->update(['status' => 'submitted']);

        return redirect()->route('applicant.status')->with('success', 'Application submitted successfully.');
    }

    public function documents(): View
    {
        $applicant = Auth::guard('applicant')->user();
        $application = $applicant->application;

        if ($application) {
            $documents = $application->documents()->orderBy('created_at', 'desc')->get();
        } else {
            $documents = collect();
        }

        return view('applicant.documents', compact('applicant', 'documents'));
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
        $application = $applicant->application()->with(['eligibilityResult', 'screeningResult', 'finalDecision', 'appointment'])->first();

        $stageMap = [
            'registered' => 1,
            'draft' => 1,
            'submitted' => 2,
            'eligibility_passed' => 3,
            'eligibility_failed' => 3,
            'shortlisted' => 4,
            'appointment_scheduled' => 5,
            'screening_completed' => 6,
            'selected' => 7,
            'rejected' => 7,
        ];

        $currentStage = $stageMap[$application?->status] ?? 1;

        $stages = [
            ['title' => 'Registered', 'status' => 'completed', 'date' => $applicant->created_at?->format('Y-m-d'), 'note' => 'Account created successfully.'],
            ['title' => 'Application Submitted', 'status' => $currentStage >= 2 ? 'completed' : 'pending', 'date' => $application?->submitted_at?->format('Y-m-d'), 'note' => $currentStage >= 2 ? 'Form submitted for review.' : 'Awaiting submission.'],
            ['title' => 'Eligibility', 'status' => $currentStage >= 3 ? ($currentStage > 3 ? 'completed' : 'current') : 'pending', 'date' => $application?->eligibilityResult?->created_at?->format('Y-m-d'), 'note' => $application?->eligibilityResult ? ($application?->eligibilityResult?->eligible ? 'Eligible.' : 'Not eligible.') : 'Documents being verified.'],
            ['title' => 'Shortlisted', 'status' => $currentStage >= 4 ? ($currentStage > 4 ? 'completed' : 'current') : 'pending', 'date' => null, 'note' => 'Awaiting shortlisting decision.'],
            ['title' => 'Appointment', 'status' => $currentStage >= 5 ? ($currentStage > 5 ? 'completed' : 'current') : 'pending', 'date' => $application?->appointment?->appointment_date?->format('Y-m-d'), 'note' => $application?->appointment ? 'Appointment scheduled.' : 'Screening appointment to be scheduled.'],
            ['title' => 'Screening', 'status' => $currentStage >= 6 ? ($currentStage > 6 ? 'completed' : 'current') : 'pending', 'date' => $application?->screeningResult?->created_at?->format('Y-m-d'), 'note' => $application?->screeningResult ? 'Screening completed.' : 'Medical, fitness, and interview pending.'],
            ['title' => 'Decision', 'status' => $currentStage >= 7 ? 'completed' : 'pending', 'date' => $application?->finalDecision?->created_at?->format('Y-m-d'), 'note' => $application?->finalDecision ? 'Final decision rendered.' : 'Final decision pending.'],
        ];

        $eligible = $application?->eligibilityResult?->eligible ?? false;
        $verificationCode = $application?->verificationCode;

        return view('applicant.status', compact('applicant', 'application', 'currentStage', 'stages', 'eligible', 'verificationCode'));
    }

    public function appointment(): View
    {
        $applicant = Auth::guard('applicant')->user();
        $appointment = $applicant->application?->appointment;

        return view('applicant.appointment', compact('applicant', 'appointment'));
    }

    public function notifications(): View
    {
        $applicant = Auth::guard('applicant')->user();
        $allNotifications = $applicant->notifications()->orderBy('created_at', 'desc')->paginate(20);

        return view('applicant.notifications', compact('applicant', 'allNotifications'));
    }
}
