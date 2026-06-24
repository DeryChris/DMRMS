<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthenticated.');
        }

        if ($request->user('admin')) {
            $admin = $request->user('admin');

            if (!in_array($admin->role, $roles)) {
                abort(403, 'Unauthorized action. Required role: ' . implode(', ', $roles));
            }

            return $next($request);
        }

        if ($guard = $request->user('web')) {
            $allowedStatuses = [];

            if (in_array('applicant', $roles)) {
                $allowedStatuses = ['registered', 'application_submitted', 'eligibility_passed', 'shortlisted', 'appointment_scheduled', 'screening_completed'];
            }

            $applicant = $request->user('web');

            if (!in_array('applicant', $roles) || ($allowedStatuses && !in_array($applicant->status, $allowedStatuses))) {
                abort(403, 'Unauthorized action.');
            }

            return $next($request);
        }

        abort(403, 'Unauthorized.');
    }
}
