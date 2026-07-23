<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user();

        if (!$admin) {
            return response()->json([
                'message' => 'Unauthenticated. Admin login required.',
            ], 401);
        }

        $tier = $admin->subscription_tier ?? null;
        $expiresAt = $admin->subscription_expires_at ?? null;

        if (!$tier || !in_array($tier, ['pro', 'enterprise'])) {
            return response()->json([
                'message' => 'Active Pro or Enterprise subscription required.',
            ], 403);
        }

        if ($expiresAt && now()->gt($expiresAt)) {
            return response()->json([
                'message' => 'Your subscription has expired. Please renew to continue.',
            ], 403);
        }

        return $next($request);
    }
}
