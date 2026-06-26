<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Document;
use App\Models\VerificationCode;
use App\Models\Appointment;
use App\Services\Ai\AiGateway;
use App\Services\Eligibility\EligibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ApplicantController extends Controller
{
    protected AiGateway $aiGateway;
    protected EligibilityService $eligibilityService;

    public function __construct(AiGateway $aiGateway, EligibilityService $eligibilityService)
    {
        $this->aiGateway = $aiGateway;
        $this->eligibilityService = $eligibilityService;
    }

    public function profile(Request $request): JsonResponse
    {
        $applicant = $request->user();

        if ($request->isMethod('put')) {
            $validated = $request->validate([
                'contact_number'      => 'sometimes|string|max:20|unique:applicants,contact_number,' . $applicant->id,
                'alternative_contact' => 'nullable|string|max:20',
                'residential_address' => 'sometimes|string|max:500',
                'region'              => 'sometimes|string|max:255',
                'district'            => 'sometimes|string|max:255',
                'marital_status'      => 'nullable|in:single,married,divorced,widowed',
            ]);

            $applicant->update($validated);

            return response()->json([
                'message'   => 'Profile updated successfully.',
                'applicant' => $applicant,
            ]);
        }

        return response()->json(['data' => $applicant->load('voucher.cycle')]);
    }

    public function application(Request $request): JsonResponse
    {
        $applicant = $request->user();
        $application = $applicant->application;

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'cycle_id'            => 'sometimes|exists:cycles,id',
                'region'              => 'sometimes|string|max:255',
                'district'            => 'sometimes|string|max:255',
                'highest_education'   => 'sometimes|string|max:255',
                'institution'         => 'nullable|string|max:255',
                'year_completed'      => 'nullable|integer|min:1950|max:' . date('Y'),
                'course_study'        => 'nullable|string|max:255',
                'height_cm'           => 'nullable|numeric|min:100|max:250',
                'weight_kg'           => 'nullable|numeric|min:30|max:200',
                'has_tattoos'         => 'nullable|boolean',
                'has_medical_conditions' => 'nullable|boolean',
                'medical_notes'       => 'nullable|string|max:1000',
                'criminal_record'     => 'nullable|boolean',
                'criminal_details'    => 'nullable|string|max:1000',
                'previous_application' => 'nullable|boolean',
                'previous_details'    => 'nullable|string|max:1000',
                'declaration'         => 'nullable|boolean',
            ]);

            if ($application) {
                $application->update($validated);
            } else {
                $validated['cycle_id'] = $validated['cycle_id'] ?? optional($applicant->voucher->cycle)->id;
                $application = Application::create(array_merge($validated, [
                    'applicant_id' => $applicant->id,
                    'status'       => 'draft',
                ]));
            }

            return response()->json([
                'message'     => $application->wasRecentlyCreated ? 'Application created.' : 'Application updated.',
                'application' => $application,
            ]);
        }

        return response()->json([
            'data' => $application ? $application->load('cycle') : null,
        ]);
    }

    public function submitApplication(Request $request): JsonResponse
    {
        $applicant = $request->user();
        $application = $applicant->application;

        if (!$application) {
            return response()->json(['message' => 'No application found. Please create an application first.'], 404);
        }

        if ($application->status !== 'draft') {
            return response()->json(['message' => 'Application is already submitted.'], 422);
        }

        $application->update(['status' => 'submitted', 'submitted_at' => now()]);

        return response()->json([
            'message'     => 'Application submitted successfully.',
            'application' => $application->fresh(),
        ]);
    }

    public function documents(Request $request): JsonResponse
    {
        $applicant = $request->user();

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'document_type' => 'required|string|in:birth_certificate,national_id,certificate,photograph,medical_report,other',
                'file'          => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'description'   => 'nullable|string|max:500',
            ]);

            $path = $request->file('file')->store("documents/{$applicant->id}", 'public');

            $document = Document::create([
                'applicant_id'   => $applicant->id,
                'document_type'  => $validated['document_type'],
                'file_path'      => $path,
                'file_name'      => $request->file('file')->getClientOriginalName(),
                'file_size'      => $request->file('file')->getSize(),
                'mime_type'      => $request->file('file')->getMimeType(),
                'description'    => $validated['description'] ?? null,
                'verification_status' => 'pending',
            ]);

            return response()->json([
                'message'  => 'Document uploaded successfully.',
                'document' => $document,
            ], 201);
        }

        if ($request->isMethod('delete')) {
            $request->validate(['document_id' => 'required|exists:documents,id']);

            $document = Document::where('applicant_id', $applicant->id)
                ->findOrFail($request->document_id);

            Storage::disk('public')->delete($document->file_path);
            $document->delete();

            return response()->json(['message' => 'Document deleted successfully.']);
        }

        return response()->json([
            'data' => $applicant->documents()->orderBy('created_at', 'desc')->get(),
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $applicant = $request->user();

        $application = $applicant->application()->with(['eligibility', 'appointment', 'selection'])->first();

        return response()->json([
            'data' => [
                'application_status' => $application?->status,
                'eligibility'        => $application?->eligibility,
                'appointment'        => $application?->appointment,
                'selection'          => $application?->selection,
            ],
        ]);
    }

    public function verificationCode(Request $request): JsonResponse
    {
        $applicant = $request->user();

        $code = $applicant->verificationCodes()
            ->where('type', 'eligibility')
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->first();

        if (!$code) {
            return response()->json(['message' => 'No valid verification code found.'], 404);
        }

        return response()->json(['data' => ['code' => $code->code]]);
    }

    public function appointment(Request $request): JsonResponse
    {
        $applicant = $request->user();

        $appointment = $applicant->application?->appointment;

        if (!$appointment) {
            return response()->json(['message' => 'No appointment scheduled.'], 404);
        }

        return response()->json(['data' => $appointment->load('slot')]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $applicant = $request->user();

        if ($request->isMethod('put')) {
            $request->validate(['notification_id' => 'required|exists:notifications,id']);

            $notification = $applicant->notifications()->findOrFail($request->notification_id);
            $notification->update(['read_at' => now()]);

            return response()->json(['message' => 'Notification marked as read.']);
        }

        return response()->json([
            'data' => $applicant->notifications()->orderBy('created_at', 'desc')->paginate(20),
        ]);
    }

    public function chatbot(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $response = $this->aiGateway->chat([
            ['role' => 'system', 'content' => 'You are a recruitment assistant for DMRMS.'],
            ['role' => 'user', 'content' => $validated['message']],
        ]);

        return response()->json([
            'data' => [
                'reply' => $response['content'] ?? 'I am unable to process your request at this time.',
            ],
        ]);
    }
}
