<?php

namespace App\Http\Controllers\Web;

use App\Events\ScreeningCompleted;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Application;
use App\Models\ScreeningResult;
use App\Models\VerificationCode;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScreeningWebController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function dashboard(): View
    {
        $todayAppointments = Appointment::with('application.applicant')
            ->whereDate('scheduled_date', Carbon::today())
            ->orderBy('scheduled_time')
            ->get();

        $todayCount = $todayAppointments->count();
        $checkedInCount = $todayAppointments->whereNotNull('checked_in_at')->count();
        $pendingCount = $todayAppointments->whereNull('checked_in_at')
            ->where('status', '!=', 'missed')->count();

        return view('screening.dashboard', compact(
            'todayAppointments', 'todayCount', 'checkedInCount', 'pendingCount'
        ));
    }

    public function verify(): View
    {
        return view('screening.verify');
    }

    public function verifyEntry(Request $request): JsonResponse
    {
        $this->authorize('verifyEntry', ScreeningResult::class);

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
            'name' => $applicant->name,
            'gaf_id' => $app->gaf_id,
            'contact_number' => $applicant->contact_number,
            'status' => $app->status,
            'application_id' => $app->id,
        ]);
    }

    public function checkin(Request $request): RedirectResponse
    {
        $request->validate(['application_id' => 'required|exists:applications,id']);

        $appointment = Appointment::where('application_id', $request->application_id)
            ->whereDate('scheduled_date', Carbon::today())
            ->first();

        if ($appointment) {
            $appointment->update([
                'checked_in_at' => now(),
                'status' => 'checked_in',
            ]);
        }

        return redirect()->route('screening.verify')->with('success', 'Applicant checked in successfully.');
    }

    public function searchApplicant(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string']);

        $application = Application::with('applicant', 'screeningResult')
            ->whereHas('applicant', function ($q) use ($request) {
                $q->where('first_name', 'ilike', "%{$request->q}%")
                  ->orWhere('last_name', 'ilike', "%{$request->q}%")
                  ->orWhere('gaf_id', 'ilike', "%{$request->q}%");
            })
            ->whereIn('status', ['appointment_scheduled', 'screening_completed'])
            ->first();

        if (!$application) {
            return response()->json(['error' => 'No applicant found.'], 404);
        }

        $sr = $application->screeningResult;

        return response()->json([
            'application_id' => $application->id,
            'gaf_id' => $application->gaf_id,
            'name' => $application->applicant->name,
            'medical_status' => $sr?->medical_status ?? 'pending',
            'fitness_score' => $sr?->fitness_score,
            'interview_decision' => $sr?->interview_decision ?? 'pending',
        ]);
    }

    public function recordMedical(Request $request): RedirectResponse
    {
        $this->authorize('recordMedical', ScreeningResult::class);

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

        $result = ScreeningResult::updateOrCreate(
            ['application_id' => $validated['application_id']],
            [
                'medical_status'  => $validated['medical_status'],
                'medical_result'  => $validated['medical_status'],
                'medical_notes'   => $validated['notes'] ?? null,
                'medical_data'    => [
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

        $result->updateOverallStatus();

        return redirect()->route('screening.medical')->with('success', 'Medical results recorded.');
    }

    public function recordFitness(Request $request): RedirectResponse
    {
        $this->authorize('recordFitness', ScreeningResult::class);

        $validated = $request->validate([
            'application_id'  => 'required|exists:applications,id',
            'fitness_score'   => 'required|numeric|min:0|max:100',
            'run_time_seconds'=> 'nullable|integer|min:0',
            'push_ups'        => 'nullable|integer|min:0',
            'sit_ups'         => 'nullable|integer|min:0',
            'pull_ups'        => 'nullable|integer|min:0',
            'shuttle_run'     => 'nullable|numeric|min:0',
            'fitness_grade'   => 'nullable|in:a,b,c,d,f',
            'notes'           => 'nullable|string|max:2000',
        ]);

        $result = ScreeningResult::updateOrCreate(
            ['application_id' => $validated['application_id']],
            [
                'fitness_result'   => $validated['fitness_score'] >= 50 ? 'pass' : 'fail',
                'fitness_score'     => $validated['fitness_score'],
                'fitness_details'   => [
                    'run_time_seconds' => $validated['run_time_seconds'] ?? null,
                    'push_ups'         => $validated['push_ups'] ?? null,
                    'sit_ups'          => $validated['sit_ups'] ?? null,
                    'pull_ups'         => $validated['pull_ups'] ?? null,
                    'shuttle_run'      => $validated['shuttle_run'] ?? null,
                    'fitness_grade'    => $validated['fitness_grade'] ?? null,
                ],
                'fitness_notes'   => $validated['notes'] ?? null,
                'conducted_at'    => now(),
            ]
        );

        $result->updateOverallStatus();

        return redirect()->route('screening.fitness')->with('success', 'Fitness results recorded.');
    }

    public function recordInterview(Request $request): RedirectResponse
    {
        $this->authorize('recordInterview', ScreeningResult::class);

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

        $result = ScreeningResult::updateOrCreate(
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

        $result->updateOverallStatus();

        $application = Application::find($validated['application_id']);
        if ($application) {
            $overall = $result->fresh()->overall_status;

            if ($overall === 'pass') {
                $newStatus = 'screening_completed';
            } elseif ($overall === 'fail') {
                $newStatus = 'disqualified';
            } else {
                $newStatus = $application->status;
            }

            if ($newStatus !== $application->status) {
                $application->update(['status' => $newStatus]);
            }

            if ($newStatus === 'screening_completed') {
                ScreeningCompleted::dispatch($application->fresh());
                $this->notificationService->notifyAdminsByRole(
                    ['admin', 'super_admin'],
                    'screening_completed',
                    'Screening Completed',
                    ($application->applicant->name ?? 'An applicant') . ' (GAF ID: ' . ($application->gaf_id ?? 'N/A') . ') has completed all screening stages and is pending committee review.'
                );

                Appointment::where('application_id', $application->id)
                    ->whereNull('checked_in_at')
                    ->update(['checked_in_at' => now(), 'status' => 'completed']);

                Appointment::where('application_id', $application->id)
                    ->whereNotNull('checked_in_at')
                    ->update(['status' => 'completed']);
            }
        }

        return redirect()->route('screening.interview')->with('success', 'Interview results recorded.');
    }

    public function medical(Request $request): View
    {
        $applicant = null;
        $application = null;
        $result = null;

        if ($request->get('application_id')) {
            $application = Application::with('applicant', 'screeningResult')->find($request->get('application_id'));
            $applicant = $application?->applicant;
            $result = $application?->screeningResult;
        }

        return view('screening.medical', compact('applicant', 'application', 'result'));
    }

    public function fitness(Request $request): View
    {
        $applicant = null;
        $application = null;
        $result = null;

        if ($request->get('application_id')) {
            $application = Application::with('applicant', 'screeningResult')->find($request->get('application_id'));
            $applicant = $application?->applicant;
            $result = $application?->screeningResult;
        }

        return view('screening.fitness', compact('applicant', 'application', 'result'));
    }

    public function interview(Request $request): View
    {
        $applicant = null;
        $application = null;
        $result = null;

        if ($request->get('application_id')) {
            $application = Application::with('applicant', 'screeningResult')->find($request->get('application_id'));
            $applicant = $application?->applicant;
            $result = $application?->screeningResult;
        }

        return view('screening.interview', compact('applicant', 'application', 'result'));
    }
}
