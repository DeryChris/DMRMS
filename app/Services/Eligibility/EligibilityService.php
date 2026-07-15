<?php

namespace App\Services\Eligibility;

use App\Events\EligibilityPassed;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\EligibilityResult;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class EligibilityService
{
    public function __construct(
        protected EligibilityEngine $engine,
        protected NotificationService $notificationService,
    ) {}

    public function evaluate(Application $app): array
    {
        if ($app->status === 'submitted') {
            $app->update(['status' => 'documents_verified']);
            $this->notificationService->documentsVerified($app);
        }

        $result = $this->engine->evaluate($app);

        $eligibilityResult = EligibilityResult::updateOrCreate(
            ['application_id' => $app->id],
            [
                'age_check' => $result['checks']['age']['passed'],
                'nationality_check' => $result['checks']['nationality']['passed'],
                'education_check' => $result['checks']['education']['passed'],
                'height_check' => $result['checks']['height']['passed'],
                'criminal_check' => $result['checks']['criminal_record']['passed'],
                'document_check' => $result['checks']['documents']['passed'],
                'marital_check' => $result['checks']['marital_status']['passed'],
                'overall_status' => $result['overall_status'],
                'rejection_reasons' => $result['rejection_reasons'],
                'evaluation_date' => Carbon::now(),
            ]
        );

        $newStatus = $result['overall_status'] === 'eligible' ? 'eligibility_passed' : 'eligibility_failed';
        $app->update(['status' => $newStatus]);

        $app->load('eligibilityResult');
        $this->notificationService->eligibilityResult($app);

        if ($newStatus === 'eligibility_passed') {
            EligibilityPassed::dispatch($app);
            $this->notificationService->notifyAdminsByRole(
                ['admin', 'super_admin'],
                'eligibility_passed',
                'New Eligible Applicant',
                ($app->applicant->name ?? 'An applicant') . ' (GAF ID: ' . ($app->gaf_id ?? 'N/A') . ') has passed eligibility and is ready for shortlisting.'
            );
        }

        AuditLog::create([
            'user_id' => $app->applicant_id,
            'user_type' => 'applicant',
            'action' => 'eligibility_' . $result['overall_status'],
            'details' => [
                'application_id' => $app->id,
                'overall_status' => $result['overall_status'],
                'checks' => $result['checks'],
                'rejection_reasons' => $result['rejection_reasons'],
            ],
            'ip_address' => Request::ip(),
        ]);

        return [
            'status' => $newStatus,
            'result' => $eligibilityResult,
            'checks' => $result['checks'],
            'rejection_reasons' => $result['rejection_reasons'],
        ];
    }

    public function reEvaluate(Application $app): array
    {
        $result = $this->engine->evaluate($app);

        $eligibilityResult = EligibilityResult::updateOrCreate(
            ['application_id' => $app->id],
            [
                'age_check' => $result['checks']['age']['passed'],
                'nationality_check' => $result['checks']['nationality']['passed'],
                'education_check' => $result['checks']['education']['passed'],
                'height_check' => $result['checks']['height']['passed'],
                'criminal_check' => $result['checks']['criminal_record']['passed'],
                'document_check' => $result['checks']['documents']['passed'],
                'marital_check' => $result['checks']['marital_status']['passed'],
                'overall_status' => $result['overall_status'],
                'rejection_reasons' => $result['rejection_reasons'],
                'evaluation_date' => Carbon::now(),
            ]
        );

        $newStatus = $result['overall_status'] === 'eligible' ? 'eligibility_passed' : 'eligibility_failed';

        $app->load('eligibilityResult');

        AuditLog::create([
            'user_id' => auth()->id() ?? $app->applicant_id,
            'user_type' => 'admin',
            'action' => 'eligibility_refresh_' . $result['overall_status'],
            'details' => [
                'application_id' => $app->id,
                'overall_status' => $result['overall_status'],
                'checks' => $result['checks'],
                'rejection_reasons' => $result['rejection_reasons'],
                'refreshed_by' => auth()->user()?->email ?? 'system',
            ],
            'ip_address' => Request::ip(),
        ]);

        return [
            'status' => $newStatus,
            'result' => $eligibilityResult,
            'checks' => $result['checks'],
            'rejection_reasons' => $result['rejection_reasons'],
        ];
    }
}
