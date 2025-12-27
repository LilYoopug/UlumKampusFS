<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionPost extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'thread_id',
        'parent_id',
        'user_id',
        'content',
        'is_edited',
        'edited_at',
        'edited_by',
        'is_solution',
        'marked_as_solution_by',
        'marked_as_solution_at',
        'likes_count',
        'attachment_url',
        'attachment_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_edited' => 'boolean',
            'edited_at' => 'datetime',
            'is_solution' => 'boolean',
            'marked_as_solution_at' => 'datetime',
            'likes_count' => 'integer',
        ];
    }

    /**
     * Get the thread that owns this post.
     */
    public function thread()
    {
        return $this->belongsTo(DiscussionThread::class, 'thread_id');
    }

    /**
     * Get the parent post (for replies).
     */
    public function parent()
    {
        return $this->belongsTo(DiscussionPost::class, 'parent_id');
    }

    /**
     * Get the replies to this post.
     */
    public function replies()
    {
        return $this->hasMany(DiscussionPost::class, 'parent_id');
    }

    /**
     * Get the user who wrote this post.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who edited this post.
     */
    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Get the user who marked this post as solution.
     */
    public function markedAsSolutionBy()
    {
        return $this->belongsTo(User::class, 'marked_as_solution_by');
    }

    /**
     * Scope a query to only include top-level posts (no parent).
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include replies (has parent).
     */
    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope a query to only include solution posts.
     */
    public function scopeSolution($query)
    {
        return $query->where('is_solution', true);
    }

    /**
     * Scope a query to order by newest first.
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to order by oldest first.
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * Check if this post is a reply.
     */
    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Check if this post has replies.
     */
    public function hasReplies(): bool
    {
        return $this->replies()->exists();
    }

    /**
     * Mark this post as solution.
     */
    public function markAsSolution(User $user): void
    {
        $this->update([
            'is_solution' => true,
            'marked_as_solution_by' => $user->id,
            'marked_as_solution_at' => now(),
        ]);
    }

    /**
     * Unmark this post as solution.
     */
    public function unmarkAsSolution(): void
    {
        $this->update([
            'is_solution' => false,
            'marked_as_solution_by' => null,
            'marked_as_solution_at' => null,
        ]);
    }

    /**
     * Increment the likes count.
     */
    public function incrementLikes(): int
    {
        $this->increment('likes_count');
        return $this->likes_count;
    }

    /**
     * Decrement the likes count.
     */
    public function decrementLikes(): int
    {
        $this->decrement('likes_count');
        return $this->likes_count;
    }
}