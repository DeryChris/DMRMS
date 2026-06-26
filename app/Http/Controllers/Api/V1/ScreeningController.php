<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\VerificationCode;
use App\Models\ScreeningResult;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function verifyEntry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'verification_code' => 'required|string',
        ]);

        $code = VerificationCode::where('code_value', $validated['verification_code'])
            ->where('type', 'entry')
            ->where('expiry_date', '>', now())
            ->where('used_status', false)
            ->first();

        if (!$code) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 422);
        }

        $code->update(['used_status' => true, 'used_at' => now()]);

        $applicant = $code->application->applicant;

        return response()->json([
            'message'   => 'Entry verified successfully.',
            'data'      => [
                'applicant_id' => $applicant->id,
                'full_name'    => "{$applicant->first_name} {$applicant->last_name}",
                'date_of_birth'=> $applicant->date_of_birth,
                'region'       => $applicant->region,
                'district'     => $applicant->district,
                'status'       => $applicant->status,
            ],
        ]);
    }

    public function recordMedical(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'blood_pressure' => 'nullable|string|max:50',
            'heart_rate'     => 'nullable|integer|min:30|max:250',
            'vision_left'    => 'nullable|string|max:20',
            'vision_right'   => 'nullable|string|max:20',
            'hearing_test'   => 'nullable|in:pass,fail',
            'height_cm'      => 'nullable|numeric|min:100|max:250',
            'weight_kg'      => 'nullable|numeric|min:30|max:200',
            'bmi'            => 'nullable|numeric',
            'medical_status' => 'required|in:fit,unfit,pending',
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
                'screened_by'     => $request->user()->id ?? null,
                'conducted_by'    => $request->user()->id ?? null,
                'conducted_at'    => now(),
            ]
        );

        $result->updateOverallStatus();

        return response()->json([
            'message' => 'Medical results recorded.',
            'data'    => $result,
        ]);
    }

    public function recordFitness(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id'    => 'required|exists:applications,id',
            'run_time_seconds'=> 'nullable|integer|min:0',
            'push_ups'        => 'nullable|integer|min:0',
            'sit_ups'         => 'nullable|integer|min:0',
            'pull_ups'        => 'nullable|integer|min:0',
            'shuttle_run'     => 'nullable|numeric|min:0',
            'fitness_score'   => 'required|numeric|min:0|max:100',
            'fitness_grade'   => 'nullable|in:a,b,c,d,f',
            'fitness_result'  => 'nullable|in:pass,fail,pending',
            'notes'           => 'nullable|string|max:2000',
        ]);

        $result = ScreeningResult::updateOrCreate(
            ['application_id' => $validated['application_id']],
            [
                'fitness_result'   => $validated['fitness_result'] ?? ($validated['fitness_score'] >= 50 ? 'pass' : 'fail'),
                'fitness_score'    => $validated['fitness_score'],
                'fitness_details'  => [
                    'run_time_seconds' => $validated['run_time_seconds'] ?? null,
                    'push_ups'         => $validated['push_ups'] ?? null,
                    'sit_ups'          => $validated['sit_ups'] ?? null,
                    'pull_ups'         => $validated['pull_ups'] ?? null,
                    'shuttle_run'      => $validated['shuttle_run'] ?? null,
                    'fitness_grade'    => $validated['fitness_grade'] ?? null,
                ],
                'fitness_notes'   => $validated['notes'] ?? null,
                'screened_by'     => $request->user()->id ?? null,
                'conducted_by'    => $request->user()->id ?? null,
                'conducted_at'    => now(),
            ]
        );

        $result->updateOverallStatus();

        return response()->json([
            'message' => 'Fitness results recorded.',
            'data'    => $result,
        ]);
    }

    public function recordInterview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'application_id'    => 'required|exists:applications,id',
            'interview_score'   => 'required|numeric|min:0|max:100',
            'interview_decision'=> 'required|in:pass,fail',
            'communication'     => 'nullable|integer|min:1|max:10',
            'confidence'        => 'nullable|integer|min:1|max:10',
            'appearance'        => 'nullable|integer|min:1|max:10',
            'knowledge'         => 'nullable|integer|min:1|max:10',
            'attitude'          => 'nullable|integer|min:1|max:10',
            'notes'             => 'nullable|string|max:2000',
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
                'screened_by'     => $request->user()->id ?? null,
                'conducted_by'    => $request->user()->id ?? null,
                'conducted_at'    => now(),
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
                $this->notificationService->finalDecisionPending($application->fresh());
            }
        }

        return response()->json([
            'message' => 'Interview results recorded.',
            'data'    => $result,
        ]);
    }
}
