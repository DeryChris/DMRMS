<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationWebController extends Controller
{
    private function getNotificationsQuery()
    {
        if (Auth::guard('applicant')->check()) {
            return Notification::where('applicant_id', Auth::guard('applicant')->id());
        }

        $user = Auth::user();
        $query = Notification::where('admin_id', $user?->id);

        return $query;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 5);
        $notifications = $this->getNotificationsQuery()
            ->orderBy('sent_at', 'desc')
            ->paginate($perPage);

        $query = $this->getNotificationsQuery();

        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
                'unread_count' => (clone $query)->whereNull('read_at')->count(),
            ],
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        $count = $this->getNotificationsQuery()
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead(int $id): JsonResponse
    {
        $notification = $this->getNotificationsQuery()
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(): JsonResponse
    {
        $this->getNotificationsQuery()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function allNotifications(Request $request)
    {
        $perPage = $request->integer('per_page', 20);
        $notifications = $this->getNotificationsQuery()
            ->orderBy('sent_at', 'desc')
            ->paginate($perPage);

        return view('notifications.index', compact('notifications'));
    }
}
