<?php

namespace App\Http\Controllers\Api;

use App\Models\DiscussionThread;
use App\Models\DiscussionPost;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DiscussionController extends ApiController
{
    /**
     * Display a listing of discussions.
     */
    public function index(): JsonResponse
    {
        $threads = DiscussionThread::with(['course', 'module', 'creator'])
            ->open()
            ->recent()
            ->get();
        return $this->success($threads);
    }

    /**
     * Get all discussion threads.
     */
    public function threads(): JsonResponse
    {
        $threads = DiscussionThread::with(['course', 'module', 'creator'])
            ->recent()
            ->get();
        return $this->success($threads);
    }

    /**
     * Display the specified discussion thread.
     */
    public function showThread(string $id): JsonResponse
    {
        $thread = DiscussionThread::with(['course', 'module', 'creator', 'solution'])
            ->findOrFail($id);
        $thread->increment('view_count');
        return $this->success($thread);
    }

    /**
     * Get posts for a thread.
     */
    public function posts(string $id): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);
        $posts = $thread->posts()
            ->with(['user', 'replies.user'])
            ->newest()
            ->get();
        return $this->success($posts);
    }

    /**
     * Create a new discussion thread.
     */
    public function storeThread(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'module_id' => 'nullable|exists:course_modules,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'nullable|in:question,discussion,announcement',
            'attachment_url' => 'nullable|url|max:500',
            'attachment_type' => 'nullable|string|max:50',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'open';
        $validated['last_post_by'] = auth()->id();
        $validated['last_post_at'] = now();

        $thread = DiscussionThread::create($validated);

        // Create the first post
        DiscussionPost::create([
            'thread_id' => $thread->id,
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        $thread->update(['reply_count' => 1]);

        return $this->created($thread, 'Discussion thread created successfully');
    }

    /**
     * Create a new post in a thread.
     */
    public function storePost(string $id, Request $request): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);

        $validated = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:discussion_posts,id',
            'attachment_url' => 'nullable|url|max:500',
            'attachment_type' => 'nullable|string|max:50',
        ]);

        $post = DiscussionPost::create([
            'thread_id' => $id,
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $thread->updateReplyCount();
        $thread->update([
            'last_post_by' => auth()->id(),
            'last_post_at' => now(),
        ]);

        return $this->created($post, 'Post created successfully');
    }

    /**
     * Update a discussion thread.
     */
    public function updateThread(string $id, Request $request): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);

        if ($thread->created_by !== auth()->id()) {
            return $this->forbidden('You can only edit your own threads');
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'type' => 'nullable|in:question,discussion,announcement',
            'attachment_url' => 'nullable|url|max:500',
            'attachment_type' => 'nullable|string|max:50',
        ]);

        $thread->update($validated);
        return $this->success($thread, 'Thread updated successfully');
    }

    /**
     * Update a discussion post.
     */
    public function updatePost(string $id, Request $request): JsonResponse
    {
        $post = DiscussionPost::findOrFail($id);

        if ($post->user_id !== auth()->id()) {
            return $this->forbidden('You can only edit your own posts');
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $post->update([
            'content' => $validated['content'],
            'is_edited' => true,
            'edited_at' => now(),
            'edited_by' => auth()->id(),
        ]);

        return $this->success($post, 'Post updated successfully');
    }

    /**
     * Delete a discussion thread.
     */
    public function deleteThread(string $id): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);

        if ($thread->created_by !== auth()->id()) {
            return $this->forbidden('You can only delete your own threads');
        }

        $thread->delete();
        return $this->noContent();
    }

    /**
     * Delete a discussion post.
     */
    public function deletePost(string $id): JsonResponse
    {
        $post = DiscussionPost::findOrFail($id);

        if ($post->user_id !== auth()->id()) {
            return $this->forbidden('You can only delete your own posts');
        }

        $thread = $post->thread;
        $post->delete();
        $thread->updateReplyCount();

        return $this->noContent();
    }

    /**
     * Like a post.
     */
    public function likePost(string $id): JsonResponse
    {
        $post = DiscussionPost::findOrFail($id);
        $post->incrementLikes();
        return $this->success($post, 'Post liked');
    }

    /**
     * Pin a thread.
     */
    public function pinThread(string $id): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);
        $thread->update(['is_pinned' => true]);
        return $this->success($thread, 'Thread pinned');
    }

    /**
     * Unpin a thread.
     */
    public function unpinThread(string $id): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);
        $thread->update(['is_pinned' => false]);
        return $this->success($thread, 'Thread unpinned');
    }

    /**
     * Lock a thread.
     */
    public function lockThread(string $id): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);
        $thread->lock(auth()->user());
        return $this->success($thread, 'Thread locked');
    }

    /**
     * Unlock a thread.
     */
    public function unlockThread(string $id): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);
        $thread->unlock();
        return $this->success($thread, 'Thread unlocked');
    }

    /**
     * Close a thread.
     */
    public function closeThread(string $id): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);
        $thread->close(auth()->user());
        return $this->success($thread, 'Thread closed');
    }

    /**
     * Reopen a thread.
     */
    public function reopenThread(string $id): JsonResponse
    {
        $thread = DiscussionThread::findOrFail($id);
        $thread->reopen();
        return $this->success($thread, 'Thread reopened');
    }

    /**
     * Mark a post as solution.
     */
    public function markSolution(string $id): JsonResponse
    {
        $post = DiscussionPost::findOrFail($id);
        $thread = $post->thread;

        // Unmark any existing solution
        $thread->posts()->where('is_solution', true)->update(['is_solution' => false]);

        $post->markAsSolution(auth()->user());
        return $this->success($post, 'Post marked as solution');
    }

    /**
     * Unmark a post as solution.
     */
    public function unmarkSolution(string $id): JsonResponse
    {
        $post = DiscussionPost::findOrFail($id);
        $post->unmarkAsSolution();
        return $this->success($post, 'Post unmarked as solution');
    }
}