<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationWebController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 5);
        $notifications = Notification::where('admin_id', auth()->id())
            ->orderBy('sent_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
                'unread_count' => Notification::where('admin_id', auth()->id())->whereNull('read_at')->count(),
            ],
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        $count = Notification::where('admin_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead(int $id): JsonResponse
    {
        $notification = Notification::where('admin_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(): JsonResponse
    {
        Notification::where('admin_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function allNotifications(Request $request)
    {
        $perPage = $request->integer('per_page', 20);
        $notifications = Notification::where('admin_id', auth()->id())
            ->orderBy('sent_at', 'desc')
            ->paginate($perPage);

        return view('notifications.index', compact('notifications'));
    }
}
