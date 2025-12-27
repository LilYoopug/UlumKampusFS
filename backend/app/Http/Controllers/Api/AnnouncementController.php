<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::query();

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        // Filter by target audience
        if ($request->has('target_audience') && $request->target_audience) {
            $query->where('target_audience', $request->target_audience);
        }

        // Filter by course
        if ($request->has('course_id') && $request->course_id) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by faculty
        if ($request->has('faculty_id') && $request->faculty_id) {
            $query->where('faculty_id', $request->faculty_id);
        }

        // Search in title and content
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        // Only show published and active announcements for non-admin/faculty
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'faculty'])) {
            $query->published()->active();
        }

        $announcements = $query->with(['course', 'faculty', 'creator'])
            ->ordered()
            ->latest('created_at')
            ->get();

        return $this->success($announcements);
    }

    /**
     * Store a newly created announcement.
     */
    public function store(AnnouncementRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $validated['published_at'] = $validated['is_published'] ?? false ? now() : null;

        $announcement = Announcement::create($validated);
        return $this->created(new AnnouncementResource($announcement), 'Announcement created successfully');
    }

    /**
     * Display the specified announcement.
     */
    public function show(string $id): JsonResponse
    {
        $announcement = Announcement::with(['course', 'faculty', 'creator'])->findOrFail($id);
        $announcement->increment('view_count');
        return $this->success(new AnnouncementResource($announcement));
    }

    /**
     * Update the specified announcement.
     */
    public function update(AnnouncementRequest $request, string $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);
        $validated = $request->validated();

        if (isset($validated['is_published']) && $validated['is_published'] && !$announcement->is_published) {
            $validated['published_at'] = now();
        }

        $announcement->update($validated);
        return $this->success(new AnnouncementResource($announcement), 'Announcement updated successfully');
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
        return $this->success(new AnnouncementResource($announcement), 'Announcement published successfully');
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
        return $this->success(new AnnouncementResource($announcement), 'Announcement unpublished');
    }
}