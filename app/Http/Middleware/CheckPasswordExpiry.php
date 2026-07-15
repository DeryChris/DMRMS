<?php

namespace App\Http\Middleware;

use App\Services\Security\PasswordPolicyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordExpiry
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? $request->user('applicant');

        if (!$user) {
            return $next($request);
        }

        $service = app(PasswordPolicyService::class);

        if ($service->isPasswordExpired($user->password_changed_at)) {
            $guard = $request->user() ? 'web' : 'applicant';
            $route = $guard === 'web'
                ? 'admin.password.request'
                : 'applicant.password.request';

            return redirect()->route($route)
                ->with('info', 'Your password has expired. Please reset it to continue.');
        }

        return $next($request);
    }
}
