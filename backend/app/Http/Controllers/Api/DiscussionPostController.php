<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscussionPostRequest;
use App\Models\DiscussionThread;
use App\Models\DiscussionPost;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Discussion Post Management API Controller
 *
 * Handles CRUD operations for discussion posts including:
 * - Post listing with filtering and pagination
 * - Post creation, retrieval, update, and deletion
 * - Reply creation (nested posts)
 * - Solution marking/unmarking
 * - Post likes
 * - Ownership verification for updates and deletes
 */
class DiscussionPostController extends ApiController
{
    /**
     * Display a listing of discussion posts.
     *
     * @queryParam thread_id Filter by thread ID (required)
     * @queryParam parent_id Filter by parent post ID (replies only)
     * @queryParam user_id Filter by user ID
     * @queryParam is_solution Filter by solution status (true/false)
     * @queryParam sort Sort by field (newest, oldest, popular)
     * @queryParam per_page Items per page (default: 15)
     * @queryParam page Page number (default: 1)
     */
    public function index(Request $request): JsonResponse
    {
        $query = DiscussionPost::query();

        // Thread filter is required
        if (!$request->has('thread_id')) {
            return $this->error('thread_id is required', 422);
        }

        $query->where('thread_id', $request->input('thread_id'));

        // Filter by parent (replies only)
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->input('parent_id'));
        } else {
            // By default, show top-level posts
            $query->whereNull('parent_id');
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by solution status
        if ($request->has('is_solution')) {
            $isSolution = filter_var($request->input('is_solution'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_solution', $isSolution);
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        if ($sort === 'oldest') {
            $query->oldest();
        } elseif ($sort === 'popular') {
            $query->orderBy('likes_count', 'desc');
        } else {
            $query->newest();
        }

        // Eager load relationships
        $query->with(['user', 'parent', 'replies.user']);

        // Get all posts
        $posts = $query->get();

        return $this->success($posts, 'Discussion posts retrieved successfully');
    }

    /**
     * Store a newly created discussion post in storage.
     *
     * Allows admin, faculty, and student roles (enforced by DiscussionPostRequest).
     * Verifies thread is open for posting.
     */
    public function store(DiscussionPostRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        // Verify thread exists and is open for posting
        $thread = DiscussionThread::findOrFail($validated['thread_id']);

        if (!$thread->isOpenForPosting()) {
            if ($thread->is_locked) {
                return $this->error('This thread is locked and cannot accept new posts', 403);
            }
            if ($thread->status !== 'open') {
                return $this->error('This thread is closed and cannot accept new posts', 403);
            }
        }

        // Handle parent_id validation - verify parent exists and belongs to same thread
        if (isset($validated['parent_id'])) {
            $parent = DiscussionPost::findOrFail($validated['parent_id']);
            if ($parent->thread_id !== $thread->id) {
                return $this->error('Parent post does not belong to the same thread', 422);
            }
        }

        // Create the post
        $validated['user_id'] = $user->id;
        $validated['is_edited'] = false;
        $validated['likes_count'] = 0;

        $post = DiscussionPost::create($validated);

        // Update thread metadata
        $thread->update([
            'reply_count' => $thread->posts()->count(),
            'last_post_by' => $user->id,
            'last_post_at' => now(),
        ]);

        return $this->created(
            $post->load(['user', 'thread']),
            'Discussion post created successfully'
        );
    }

    /**
     * Display the specified discussion post.
     */
    public function show(string $id): JsonResponse
    {
        $post = DiscussionPost::with([
            'user',
            'parent.user',
            'replies.user',
            'editedBy',
            'markedAsSolutionBy',
        ])->findOrFail($id);

        return $this->success(
            $post,
            'Discussion post retrieved successfully'
        );
    }

    /**
     * Update the specified discussion post in storage.
     *
     * Only the post author can update their own post.
     * Marks post as edited.
     */
    public function update(DiscussionPostRequest $request, string $id): JsonResponse
    {
        $post = DiscussionPost::findOrFail($id);
        $user = auth()->user();

        // Ownership check
        if ($post->user_id !== $user->id) {
            return $this->forbidden('You can only edit your own posts');
        }

        $validated = $request->validated();

        // Update post with edit tracking
        $post->update([
            'content' => $validated['content'],
            'attachment_url' => $validated['attachment_url'] ?? $post->attachment_url,
            'attachment_type' => $validated['attachment_type'] ?? $post->attachment_type,
            'is_edited' => true,
            'edited_at' => now(),
            'edited_by' => $user->id,
        ]);

        return $this->success(
            $post->load(['user', 'thread']),
            'Discussion post updated successfully'
        );
    }

    /**
     * Remove the specified discussion post from storage.
     *
     * Only the post author can delete their own post.
     * Updates thread reply count.
     */
    public function destroy(string $id): JsonResponse
    {
        $post = DiscussionPost::findOrFail($id);
        $user = auth()->user();

        // Ownership check
        if ($post->user_id !== $user->id) {
            return $this->forbidden('You can only delete your own posts');
        }

        $thread = $post->thread;

        // Delete the post (soft delete)
        $post->delete();

        // Update thread reply count
        $thread->updateReplyCount();

        return $this->noContent();
    }

    /**
     * Get replies for a specific post.
     *
     * @queryParam include_nested Include nested replies (true/false, default: false)
     */
    public function replies(string $id, Request $request): JsonResponse
    {
        $post = DiscussionPost::findOrFail($id);
        $includeNested = filter_var($request->input('include_nested', false), FILTER_VALIDATE_BOOLEAN);

        $repliesQuery = $post->replies()->newest();

        if ($includeNested) {
            $replies = $repliesQuery->with(['user', 'replies.user', 'editedBy'])->get();
        } else {
            $replies = $repliesQuery->with(['user', 'editedBy'])->get();
        }

        return $this->success(
            $replies,
            'Post replies retrieved successfully'
        );
    }

    /**
     * Create a reply to a post.
     *
     * Creates a new post as a child of the specified post.
     */
    public function reply(string $id, DiscussionPostRequest $request): JsonResponse
    {
        $parentPost = DiscussionPost::findOrFail($id);
        $user = auth()->user();

        // Verify thread is open for posting
        $thread = $parentPost->thread;
        if (!$thread->isOpenForPosting()) {
            if ($thread->is_locked) {
                return $this->error('This thread is locked and cannot accept new posts', 403);
            }
            if ($thread->status !== 'open') {
                return $this->error('This thread is closed and cannot accept new posts', 403);
            }
        }

        $validated = $request->validated();

        // Override thread_id and parent_id from parent post
        $validated['thread_id'] = $parentPost->thread_id;
        $validated['parent_id'] = $parentPost->id;
        $validated['user_id'] = $user->id;
        $validated['is_edited'] = false;
        $validated['likes_count'] = 0;

        $reply = DiscussionPost::create($validated);

        // Update thread metadata
        $thread->update([
            'reply_count' => $thread->posts()->count(),
            'last_post_by' => $user->id,
            'last_post_at' => now(),
        ]);

        return $this->created(
            $reply->load(['user', 'parent', 'thread']),
            'Reply created successfully'
        );
    }

    /**
     * Like a discussion post.
     *
     * Increments the likes count for the post.
     */
    public function like(string $id): JsonResponse
    {
        $post = DiscussionPost::findOrFail($id);
        $post->incrementLikes();

        return $this->success(
            $post,
            'Post liked successfully'
        );
    }

    /**
     * Unlike a discussion post.
     *
     * Decrements the likes count for the post.
     */
    public function unlike(string $id): JsonResponse
    {
        $post = DiscussionPost::findOrFail($id);

        if ($post->likes_count > 0) {
            $post->decrementLikes();
        }

        return $this->success(
            $post,
            'Post unliked successfully'
        );
    }

    /**
     * Mark a post as solution.
     *
     * Only the thread creator or admin/faculty can mark a post as solution.
     * Unmarks any existing solution for the thread.
     */
    public function markSolution(string $id): JsonResponse
    {
        $post = DiscussionPost::findOrFail($id);
        $user = auth()->user();
        $thread = $post->thread;

        // Authorization check: thread creator, admin, or faculty
        if ($thread->created_by !== $user->id && !$this->isAdminOrFaculty($user)) {
            return $this->forbidden('Only the thread creator or admin/faculty can mark a post as solution');
        }

        // Unmark any existing solution for this thread
        $thread->posts()->where('is_solution', true)->update([
            'is_solution' => false,
            'marked_as_solution_by' => null,
            'marked_as_solution_at' => null,
        ]);

        // Mark this post as solution
        $post->markAsSolution($user);

        // Close the thread when solution is marked
        if ($thread->status === 'open') {
            $thread->update([
                'status' => 'closed',
                'closed_by' => $user->id,
                'closed_at' => now(),
            ]);
        }

        return $this->success(
            $post->load(['user', 'markedAsSolutionBy']),
            'Post marked as solution successfully'
        );
    }

    /**
     * Unmark a post as solution.
     *
     * Only the thread creator or admin/faculty can unmark a solution.
     */
    public function unmarkSolution(string $id): JsonResponse
    {
        $post = DiscussionPost::findOrFail($id);
        $user = auth()->user();
        $thread = $post->thread;

        // Authorization check: thread creator, admin, or faculty
        if ($thread->created_by !== $user->id && !$this->isAdminOrFaculty($user)) {
            return $this->forbidden('Only the thread creator or admin/faculty can unmark a solution');
        }

        $post->unmarkAsSolution();

        return $this->success(
            $post,
            'Post unmarked as solution successfully'
        );
    }

    /**
     * Get posts for the current user.
     */
    public function myPosts(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = DiscussionPost::where('user_id', $user->id)
            ->with(['thread', 'parent']);

        // Sorting
        $sort = $request->input('sort', 'newest');
        if ($sort === 'oldest') {
            $query->oldest();
        } elseif ($sort === 'popular') {
            $query->orderBy('likes_count', 'desc');
        } else {
            $query->newest();
        }

        $posts = $query->get();

        return $this->success($posts, 'Your posts retrieved successfully');
    }

    /**
     * Get posts for a specific thread.
     */
    public function byThread(string $threadId, Request $request): JsonResponse
    {
        $posts = DiscussionPost::where('thread_id', $threadId)
            ->with(['user', 'parent', 'replies.user'])
            ->newest()
            ->get();

        return $this->success($posts, 'Thread posts retrieved successfully');
    }

    /**
     * Get solution posts for a specific thread.
     */
    public function solutionByThread(string $threadId): JsonResponse
    {
        $solutions = DiscussionPost::where('thread_id', $threadId)
            ->where('is_solution', true)
            ->with(['user', 'markedAsSolutionBy'])
            ->get();

        return $this->success(
            $solutions,
            'Solution posts retrieved successfully'
        );
    }

    /**
     * Check if the current user is admin or faculty.
     */
    private function isAdminOrFaculty($user): bool
    {
        return in_array($user->role, ['admin', 'faculty']);
    }
}
