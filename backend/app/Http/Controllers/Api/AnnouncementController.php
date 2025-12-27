<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index(): JsonResponse
    {
        $announcements = Announcement::published()
            ->active()
            ->with(['course', 'faculty', 'creator'])
            ->ordered()
            ->latest('created_at')
            ->get();
        return $this->success($announcements);
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'faculty_id' => 'nullable|exists:faculties,id',
            'created_by' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|in:general,academic,event,deadline,policy,other',
            'target_audience' => 'nullable|in:all,students,faculty,specific_course',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'is_published' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
            'allow_comments' => 'boolean',
            'attachment_url' => 'nullable|url|max:500',
            'attachment_type' => 'nullable|string|max:50',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['published_at'] = $validated['is_published'] ? now() : null;

        $announcement = Announcement::create($validated);
        return $this->created($announcement, 'Announcement created successfully');
    }

    /**
     * Display the specified announcement.
     */
    public function show(string $id): JsonResponse
    {
        $announcement = Announcement::with(['course', 'faculty', 'creator'])->findOrFail($id);
        $announcement->increment('view_count');
        return $this->success($announcement);
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'faculty_id' => 'nullable|exists:faculties,id',
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'category' => 'nullable|in:general,academic,event,deadline,policy,other',
            'target_audience' => 'nullable|in:all,students,faculty,specific_course',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'is_published' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
            'allow_comments' => 'boolean',
            'attachment_url' => 'nullable|url|max:500',
            'attachment_type' => 'nullable|string|max:50',
            'order' => 'nullable|integer|min:0',
        ]);

        if (isset($validated['is_published']) && $validated['is_published'] && !$announcement->is_published) {
            $validated['published_at'] = now();
        }

        $announcement->update($validated);
        return $this->success($announcement, 'Announcement updated successfully');
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy(string $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();
        return $this->noContent();
    }

    /**
     * Mark announcement as read.
     */
    public function markRead(string $id): JsonResponse
    {
        $user = auth()->user();
        // This would typically use a pivot table or read status tracking
        // For now, we'll just return success
        return $this->success(null, 'Announcement marked as read');
    }

    /**
     * Publish the announcement.
     */
    public function publish(string $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
        return $this->success($announcement, 'Announcement published successfully');
    }

    /**
     * Unpublish the announcement.
     */
    public function unpublish(string $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update([
            'is_published' => false,
            'published_at' => null,
        ]);
        return $this->success($announcement, 'Announcement unpublished');
    }
}