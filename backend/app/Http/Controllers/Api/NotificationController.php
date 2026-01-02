<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Notification Management API Controller
 *
 * Handles CRUD operations for notifications including:
 * - Notification listing with filtering and pagination
 * - Notification creation, retrieval, update, and deletion
 * - Mark-as-read functionality
 * - User-specific notification filtering
 */
class NotificationController extends ApiController
{
    /**
     * Display a listing of notifications.
     *
     * @queryParam user_id Filter by user ID (admin only)
     * @queryParam type Filter by notification type
     * @queryParam priority Filter by priority (low, medium, high, urgent)
     * @queryParam is_read Filter by read status (true/false)
     * @queryParam is_sent Filter by sent status (true/false)
     * @queryParam search Search by title or message
     * @queryParam sort Sort by field (newest, oldest, priority)
     * @queryParam per_page Items per page (default: 15)
     * @queryParam page Page number (default: 1)
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = Notification::query();

        // Non-admin users can only see their own notifications
        if (!$this->isAdmin($user)) {
            $query->where('user_id', $user->id);
        } else {
            // Admin can filter by user_id
            if ($request->has('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }
        }

        // Filter by type
        if ($request->has('type')) {
            $query->byType($request->input('type'));
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->byPriority($request->input('priority'));
        }

        // Filter by read status
        if ($request->has('is_read')) {
            $isRead = filter_var($request->input('is_read'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_read', $isRead);
        }

        // Filter by sent status
        if ($request->has('is_sent')) {
            $isSent = filter_var($request->input('is_sent'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_sent', $isSent);
        }

        // Search by title or message
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('message', 'like', "%{$searchTerm}%");
            });
        }

        // Only show active (not expired) notifications by default
        if (!$request->has('include_expired')) {
            $query->active();
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        if ($sort === 'priority') {
            $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
                  ->orderBy('created_at', 'desc');
        } elseif ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->newest();
        }

        // Eager load relationships
        $query->with('user');

        // Get all notifications
        $notifications = $query->get();

        // Return with NotificationResource for frontend-compatible format
        return $this->success(
            NotificationResource::collection($notifications),
            'Notifications retrieved successfully'
        );
    }

    /**
     * Store a newly created notification in storage.
     *
     * Requires admin role.
     */
    public function store(NotificationRequest $request): JsonResponse
    {
        $this->verifyAdmin();

        $validated = $request->validated();
        $validated['is_read'] = $validated['is_read'] ?? false;
        $validated['is_sent'] = $validated['is_sent'] ?? false;

        $notification = Notification::create($validated);

        return $this->created(
            $notification->load('user'),
            'Notification created successfully'
        );
    }

    /**
     * Display the specified notification.
     */
    public function show(string $id): JsonResponse
    {
        $user = auth()->user();
        $query = Notification::query();

        // Non-admin users can only see their own notifications
        if (!$this->isAdmin($user)) {
            $query->where('user_id', $user->id);
        }

        $notification = $query->with('user')->findOrFail($id);

        return $this->success(
            $notification,
            'Notification retrieved successfully'
        );
    }

    /**
     * Update the specified notification in storage.
     *
     * Requires admin role.
     */
    public function update(NotificationRequest $request, string $id): JsonResponse
    {
        $this->verifyAdmin();

        $notification = Notification::findOrFail($id);
        $validated = $request->validated();

        $notification->update($validated);

        return $this->success(
            $notification->load('user'),
            'Notification updated successfully'
        );
    }

    /**
     * Remove the specified notification from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = auth()->user();
        $query = Notification::query();

        // Non-admin users can only delete their own notifications
        if (!$this->isAdmin($user)) {
            $query->where('user_id', $user->id);
        }

        $notification = $query->findOrFail($id);
        $notification->delete();

        return $this->noContent();
    }

    /**
     * Mark notification as read.
     */
    public function markRead(string $id): JsonResponse
    {
        $user = auth()->user();
        $query = Notification::query();

        // Users can only mark their own notifications as read
        $query->where('user_id', $user->id);

        $notification = $query->findOrFail($id);
        $notification->markAsRead();

        return $this->success(
            $notification->load('user'),
            'Notification marked as read'
        );
    }

    /**
     * Mark notification as unread.
     */
    public function markUnread(string $id): JsonResponse
    {
        $user = auth()->user();
        $query = Notification::query();

        // Users can only mark their own notifications as unread
        $query->where('user_id', $user->id);

        $notification = $query->findOrFail($id);
        $notification->markAsUnread();

        return $this->success(
            $notification->load('user'),
            'Notification marked as unread'
        );
    }

    /**
     * Mark all notifications as read for the current user.
     */
    public function markAllRead(): JsonResponse
    {
        $user = auth()->user();

        $count = $user->notifications()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $this->success(
            ['marked_count' => $count],
            'All notifications marked as read'
        );
    }

    /**
     * Get unread notifications for the current user.
     */
    public function unread(Request $request): JsonResponse
    {
        $user = auth()->user();

        $notifications = $user->notifications()
            ->unread()
            ->active()
            ->newest()
            ->get();

        return $this->success($notifications, 'Unread notifications retrieved successfully');
    }

    /**
     * Get urgent notifications for the current user.
     */
    public function urgent(Request $request): JsonResponse
    {
        $user = auth()->user();

        $notifications = $user->notifications()
            ->urgent()
            ->active()
            ->newest()
            ->get();

        return $this->success($notifications, 'Urgent notifications retrieved successfully');
    }

    /**
     * Clear all read notifications for the current user.
     */
    public function clearRead(): JsonResponse
    {
        $user = auth()->user();

        $count = $user->notifications()->read()->delete();

        return $this->success(
            ['cleared_count' => $count],
            'Read notifications cleared'
        );
    }

    /**
     * Get notifications count for the current user.
     */
    public function counts(): JsonResponse
    {
        $user = auth()->user();

        return $this->success([
            'total' => $user->notifications()->active()->count(),
            'unread' => $user->notifications()->unread()->active()->count(),
            'urgent' => $user->notifications()->urgent()->active()->count(),
        ], 'Notification counts retrieved successfully');
    }

    /**
     * Check if the current user is admin.
     */
    private function isAdmin($user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Verify the current user is admin, throw 403 if not.
     */
    private function verifyAdmin(): void
    {
        if (!$this->isAdmin(auth()->user())) {
            abort(403, 'This action requires admin role');
        }
    }
}
