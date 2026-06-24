<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AiRateLimitMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $userId = $user->id;
        $userType = $user instanceof \App\Models\Administrator ? 'administrator' : 'applicant';
        $now = now();

        $perMinuteLimit = config('ai.rate_limit.per_minute', 10);
        $perDayLimit = config('ai.rate_limit.per_user_per_day', 100);

        $perMinuteCount = DB::table('ai_usage')
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('created_at', '>=', $now->copy()->subMinute())
            ->count();

        if ($perMinuteCount >= $perMinuteLimit) {
            return response()->json([
                'message' => 'AI rate limit exceeded. Maximum ' . $perMinuteLimit . ' requests per minute.',
                'retry_after' => 60,
            ], 429);
        }

        $perDayCount = DB::table('ai_usage')
            ->where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('created_at', '>=', $now->copy()->startOfDay())
            ->count();

        if ($perDayCount >= $perDayLimit) {
            return response()->json([
                'message' => 'Daily AI usage limit exceeded. Maximum ' . $perDayLimit . ' requests per day.',
                'retry_after' => $now->copy()->endOfDay()->diffInSeconds($now),
            ], 429);
        }

        return $next($request);
    }
}
