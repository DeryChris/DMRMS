<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApplicantAccess
{
    protected array $accessMap = [
        'applicant.application'        => ['registered', 'draft'],
        'applicant.application.save'   => ['registered', 'draft'],
        'applicant.application.submit' => ['registered', 'draft'],
        'applicant.documents'          => ['registered', 'draft', 'submitted'],
        'applicant.documents.upload'   => ['registered', 'draft', 'submitted'],
        'applicant.documents.delete'   => ['registered', 'draft', 'submitted'],
        'applicant.status'             => [
            'submitted', 'documents_verified', 'eligibility_passed', 'eligibility_failed',
            'shortlisted', 'appointment_scheduled', 'screening_completed',
            'final_decision_pending', 'selected', 'recruited', 'reserve',
            'rejected', 'disqualified',
        ],
        'applicant.appointment'        => ['appointment_scheduled', 'screening_completed'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if (!$routeName || !isset($this->accessMap[$routeName])) {
            return $next($request);
        }

        $applicant = $request->user('applicant');

        if (!$applicant) {
            return redirect()->route('applicant.login');
        }

        $application = $applicant->application()->first();
        $appStatus = $application?->status ?? 'registered';

        if (!in_array($appStatus, $this->accessMap[$routeName])) {
            return redirect()->route('applicant.dashboard')
                ->with('error', 'You do not have access to that page at your current application stage.');
        }

        return $next($request);
    }
}
