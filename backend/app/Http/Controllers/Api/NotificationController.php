<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the current user.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $notifications = $user->notifications()
            ->active()
            ->newest()
            ->get();
        return $this->success($notifications);
    }

    /**
     * Display the specified notification.
     */
    public function show(string $id): JsonResponse
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($id);
        return $this->success($notification);
    }

    /**
     * Mark notification as read.
     */
    public function markRead(string $id): JsonResponse
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();
        return $this->success($notification, 'Notification marked as read');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): JsonResponse
    {
        $user = auth()->user();
        $user->notifications()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
        return $this->success(null, 'All notifications marked as read');
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();
        return $this->noContent();
    }

    /**
     * Clear all read notifications.
     */
    public function clearRead(): JsonResponse
    {
        $user = auth()->user();
        $user->notifications()->read()->delete();
        return $this->success(null, 'Read notifications cleared');
    }
}