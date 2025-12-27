<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscussionThreadRequest;
use App\Models\DiscussionPost;
use App\Models\DiscussionThread;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Discussion Thread Management API Controller
 *
 * Handles CRUD operations for discussion threads including:
 * - Thread listing with filtering and pagination
 * - Thread creation, retrieval, update, and deletion
 * - Thread moderation (pin, lock, close)
 * - Ownership verification for updates and deletes
 */
class DiscussionThreadController extends ApiController
{
    /**
     * Display a listing of discussion threads.
     *
     * @queryParam course_id Filter by course ID
     * @queryParam module_id Filter by module ID
     * @queryParam type Filter by thread type (question, discussion, announcement, help)
     * @queryParam status Filter by status (open, closed, archived)
     * @queryParam is_pinned Filter by pinned status (true/false)
     * @queryParam is_locked Filter by locked status (true/false)
     * @queryParam search Search by title or content
     * @queryParam sort Sort by field (recent, popular, created_at)
     * @queryParam per_page Items per page (default: 15)
     * @queryParam page Page number (default: 1)
     */
    public function index(Request $request): JsonResponse
    {
        $query = DiscussionThread::query();

        // Filter by course
        if ($request->has('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }

        // Filter by module
        if ($request->has('module_id')) {
            $query->where('module_id', $request->input('module_id'));
        }

        // Filter by type
        if ($request->has('type')) {
            $query->byType($request->input('type'));
        }

        // Filter by status
        if ($request->has('status')) {
            $status = $request->input('status');
            $query->where('status', $status);
        }

        // Filter by pinned status
        if ($request->has('is_pinned')) {
            $isPinned = filter_var($request->input('is_pinned'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_pinned', $isPinned);
        }

        // Filter by locked status
        if ($request->has('is_locked')) {
            $isLocked = filter_var($request->input('is_locked'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_locked', $isLocked);
        }

        // Search by title or content
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        // Sorting
        $sort = $request->input('sort', 'recent');
        if ($sort === 'popular') {
            $query->popular();
        } elseif ($sort === 'created_at') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->recent();
        }

        // Eager load relationships
        $query->with(['course', 'module', 'creator', 'lastPostBy']);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $threads = $query->paginate($perPage);

        return $this->paginated($threads, 'Discussion threads retrieved successfully');
    }

    /**
     * Store a newly created discussion thread in storage.
     *
     * Requires admin or faculty role (enforced by DiscussionThreadRequest).
     */
    public function store(DiscussionThreadRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        // Set default values
        $validated['created_by'] = $user->id;
        $validated['status'] = $validated['status'] ?? 'open';
        $validated['is_pinned'] = $validated['is_pinned'] ?? false;
        $validated['is_locked'] = $validated['is_locked'] ?? false;
        $validated['view_count'] = 0;
        $validated['reply_count'] = 0;
        $validated['last_post_by'] = $user->id;
        $validated['last_post_at'] = now();

        $thread = DiscussionThread::create($validated);

        // Create the first post from the thread content
        DiscussionPost::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
            'attachment_url' => $validated['attachment_url'] ?? null,
            'attachment_type' => $validated['attachment_type'] ?? null,
        ]);

        // Update reply count
        $thread->update(['reply_count' => 1]);

        return $this->created(
            $thread->load(['course', 'module', 'creator']),
            'Discussion thread created successfully'
        );
    }

    /**
     * Display the specified discussion thread.
     *
     * Increments view count on retrieval.
     */
    public function show(string $id): JsonResponse
    {
        $thread = DiscussionThread::with([
            'course',
            'module',
            'creator',
            'lockedBy',
            'closedBy',
            'lastPostBy',
            'solution',
        ])->findOrFail($id);

        // Increment view count
        $thread->incrementViewCount();

        return $this->success(
            $thread,
            'Discussion thread retrieved successfully'
        );
    }

    /**
     * Update the specified discussion thread in storage.
     *
     * Only the thread creator can update their own thread.
     */
    public function update(DiscussionThreadRequest $request, string $id): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);
        $user = auth()->user();

        // Ownership check
        if ($thread->created_by !== $user->id && !$this->isAdminOrFaculty($user)) {
            return $this->forbidden('You can only edit your own threads');
        }

        $validated = $request->validated();

        // For updates, remove content as posts should be updated via DiscussionPostController
        unset($validated['content']);

        $thread->update($validated);

        return $this->success(
            $thread->load(['course', 'module', 'creator']),
            'Discussion thread updated successfully'
        );
    }

    /**
     * Remove the specified discussion thread from storage.
     *
     * Only the thread creator can delete their own thread.
     */
    public function destroy(string $id): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);
        $user = auth()->user();

        // Ownership check
        if ($thread->created_by !== $user->id && !$this->isAdminOrFaculty($user)) {
            return $this->forbidden('You can only delete your own threads');
        }

        $thread->delete();

        return $this->noContent();
    }

    /**
     * Get posts for a specific thread.
     *
     * @queryParam include_replies Include nested replies (true/false, default: false)
     */
    public function posts(string $id, Request $request): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);
        $includeReplies = filter_var($request->input('include_replies', false), FILTER_VALIDATE_BOOLEAN);

        $postsQuery = $thread->posts()->newest();

        if ($includeReplies) {
            $posts = $postsQuery->with(['user', 'replies.user', 'replies.user'])->get();
        } else {
            $posts = $postsQuery->topLevel()->with('user')->get();
        }

        return $this->success(
            $posts,
            'Thread posts retrieved successfully'
        );
    }

    /**
     * Pin a discussion thread.
     *
     * Requires admin or faculty role.
     */
    public function pin(string $id): JsonResponse
    {
        $this->verifyAdminOrFaculty();

        $thread = DiscussionThread::findOrFail($id);
        $thread->update(['is_pinned' => true]);

        return $this->success(
            $thread->load(['course', 'module', 'creator']),
            'Thread pinned successfully'
        );
    }

    /**
     * Unpin a discussion thread.
     *
     * Requires admin or faculty role.
     */
    public function unpin(string $id): JsonResponse
    {
        $this->verifyAdminOrFaculty();

        $thread = DiscussionThread::findOrFail($id);
        $thread->update(['is_pinned' => false]);

        return $this->success(
            $thread->load(['course', 'module', 'creator']),
            'Thread unpinned successfully'
        );
    }

    /**
     * Lock a discussion thread (prevents new posts).
     *
     * Requires admin or faculty role.
     */
    public function lock(string $id): JsonResponse
    {
        $this->verifyAdminOrFaculty();

        $thread = DiscussionThread::findOrFail($id);
        $user = auth()->user();
        $thread->lock($user);

        return $this->success(
            $thread->load(['course', 'module', 'creator', 'lockedBy']),
            'Thread locked successfully'
        );
    }

    /**
     * Unlock a discussion thread.
     *
     * Requires admin or faculty role.
     */
    public function unlock(string $id): JsonResponse
    {
        $this->verifyAdminOrFaculty();

        $thread = DiscussionThread::findOrFail($id);
        $thread->unlock();

        return $this->success(
            $thread->load(['course', 'module', 'creator']),
            'Thread unlocked successfully'
        );
    }

    /**
     * Close a discussion thread (marks as resolved/closed).
     *
     * Requires admin or faculty role, or thread creator.
     */
    public function close(string $id): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);
        $user = auth()->user();

        // Ownership check - only creator, admin, or faculty can close
        if ($thread->created_by !== $user->id && !$this->isAdminOrFaculty($user)) {
            return $this->forbidden('You can only close your own threads');
        }

        $thread->close($user);

        return $this->success(
            $thread->load(['course', 'module', 'creator', 'closedBy']),
            'Thread closed successfully'
        );
    }

    /**
     * Reopen a discussion thread.
     *
     * Requires admin or faculty role, or thread creator.
     */
    public function reopen(string $id): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);
        $user = auth()->user();

        // Ownership check - only creator, admin, or faculty can reopen
        if ($thread->created_by !== $user->id && !$this->isAdminOrFaculty($user)) {
            return $this->forbidden('You can only reopen your own threads');
        }

        $thread->reopen();

        return $this->success(
            $thread->load(['course', 'module', 'creator']),
            'Thread reopened successfully'
        );
    }

    /**
     * Archive a discussion thread.
     *
     * Requires admin or faculty role.
     */
    public function archive(string $id): JsonResponse
    {
        $this->verifyAdminOrFaculty();

        $thread = DiscussionThread::findOrFail($id);
        $thread->update(['status' => 'archived']);

        return $this->success(
            $thread->load(['course', 'module', 'creator']),
            'Thread archived successfully'
        );
    }

    /**
     * Restore an archived discussion thread.
     *
     * Requires admin or faculty role.
     */
    public function restore(string $id): JsonResponse
    {
        $this->verifyAdminOrFaculty();

        $thread = DiscussionThread::findOrFail($id);
        $thread->update(['status' => 'open']);

        return $this->success(
            $thread->load(['course', 'module', 'creator']),
            'Thread restored successfully'
        );
    }

    /**
     * Get threads for the current user (threads they created).
     */
    public function myThreads(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = DiscussionThread::where('created_by', $user->id)
            ->with(['course', 'module']);

        // Sorting
        $sort = $request->input('sort', 'recent');
        if ($sort === 'popular') {
            $query->popular();
        } else {
            $query->recent();
        }

        $threads = $query->paginate($request->input('per_page', 15));

        return $this->paginated($threads, 'Your threads retrieved successfully');
    }

    /**
     * Get threads for a specific course.
     */
    public function byCourse(string $courseId, Request $request): JsonResponse
    {
        $threads = DiscussionThread::where('course_id', $courseId)
            ->with(['course', 'module', 'creator', 'lastPostBy'])
            ->recent()
            ->paginate($request->input('per_page', 15));

        return $this->paginated($threads, 'Course threads retrieved successfully');
    }

    /**
     * Get threads for a specific module.
     */
    public function byModule(string $moduleId, Request $request): JsonResponse
    {
        $threads = DiscussionThread::where('module_id', $moduleId)
            ->with(['course', 'module', 'creator', 'lastPostBy'])
            ->recent()
            ->paginate($request->input('per_page', 15));

        return $this->paginated($threads, 'Module threads retrieved successfully');
    }

    /**
     * Check if the current user is admin or faculty.
     */
    private function isAdminOrFaculty($user): bool
    {
        return in_array($user->role, ['admin', 'faculty']);
    }

    /**
     * Verify the current user is admin or faculty, throw 403 if not.
     */
    private function verifyAdminOrFaculty(): void
    {
        if (!$this->isAdminOrFaculty(auth()->user())) {
            abort(403, 'This action requires admin or faculty role');
        }
    }
}