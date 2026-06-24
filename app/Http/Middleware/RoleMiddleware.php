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

        if (in_array('applicant', $roles)) {
            $allowedStatuses = ['registered', 'application_submitted', 'eligibility_passed', 'shortlisted', 'appointment_scheduled', 'screening_completed'];

            if (in_array($user->status, $allowedStatuses)) {
                return $next($request);
            }

            abort(403, 'Unauthorized action.');
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized action. Required role: ' . implode(', ', $roles));
        }

        return $next($request);
    }
}
