<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionThread extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'module_id',
        'created_by',
        'title',
        'content',
        'type',
        'status',
        'is_closed',
        'is_pinned',
        'is_locked',
        'locked_by',
        'locked_at',
        'closed_by',
        'closed_at',
        'view_count',
        'reply_count',
        'last_post_by',
        'last_post_at',
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
            'is_pinned' => 'boolean',
            'is_locked' => 'boolean',
            'is_closed' => 'boolean',
            'locked_at' => 'datetime',
            'closed_at' => 'datetime',
            'view_count' => 'integer',
            'reply_count' => 'integer',
            'last_post_at' => 'datetime',
        ];
    }

    /**
     * Get the course that owns this thread.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the module that owns this thread.
     */
    public function module()
    {
        return $this->belongsTo(CourseModule::class);
    }

    /**
     * Get the user who created this thread.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who locked this thread.
     */
    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Get the user who closed this thread.
     */
    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Get the user who made the last post.
     */
    public function lastPostBy()
    {
        return $this->belongsTo(User::class, 'last_post_by');
    }

    /**
     * Get the posts for this thread.
     */
    public function posts()
    {
        return $this->hasMany(DiscussionPost::class, 'thread_id');
    }

    /**
     * Get the solution post for this thread.
     */
    public function solution()
    {
        return $this->hasOne(DiscussionPost::class, 'thread_id')->where('is_solution', true);
    }

    /**
     * Scope a query to only include open threads.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope a query to only include closed threads.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope a query to only include archived threads.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope a query to only include pinned threads.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope a query to only include locked threads.
     */
    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to order by most recent activity.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('last_post_at', 'desc');
    }

    /**
     * Scope a query to order by most views.
     */
    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    /**
     * Check if the thread is open for new posts.
     */
    public function isOpenForPosting(): bool
    {
        return $this->status === 'open' && !$this->is_locked;
    }

    /**
     * Check if the thread has a solution.
     */
    public function hasSolution(): bool
    {
        return $this->posts()->where('is_solution', true)->exists();
    }

    /**
     * Increment the view count.
     */
    public function incrementViewCount(): int
    {
        $this->increment('view_count');
        return $this->view_count;
    }

    /**
     * Update reply count.
     */
    public function updateReplyCount(): int
    {
        $count = $this->posts()->count();
        $this->update(['reply_count' => $count]);
        return $count;
    }

    /**
     * Lock the thread.
     */
    public function lock(User $user): void
    {
        $this->update([
            'is_locked' => true,
            'locked_by' => $user->id,
            'locked_at' => now(),
        ]);
    }

    /**
     * Unlock the thread.
     */
    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'locked_by' => null,
            'locked_at' => null,
        ]);
    }

    /**
     * Close the thread.
     */
    public function close(User $user): void
    {
        $this->update([
            'status' => 'closed',
            'closed_by' => $user->id,
            'closed_at' => now(),
        ]);
    }

    /**
     * Reopen the thread.
     */
    public function reopen(): void
    {
        $this->update([
            'status' => 'open',
            'closed_by' => null,
            'closed_at' => null,
        ]);
    }

    /**
     * Get the authorId value (alias for created_by for frontend compatibility).
     */
    protected function getAuthorIdAttribute(): ?string
    {
        return $this->attributes['created_by'] ?? null;
    }

    /**
     * Set the authorId value (alias for created_by for frontend compatibility).
     */
    protected function setAuthorIdAttribute(?string $value): void
    {
        $this->attributes['created_by'] = $value;
    }

    /**
     * Get the createdAt value (alias for created_at for frontend compatibility).
     */
    protected function getCreatedAtAttribute(): ?string
    {
        return $this->attributes['created_at'] ?? null;
    }

    /**
     * Get the isPinned value (alias for is_pinned for frontend compatibility).
     */
    protected function getIsPinnedAttribute(): bool
    {
        return (bool) ($this->attributes['is_pinned'] ?? false);
    }

    /**
     * Set the isPinned value (alias for is_pinned for frontend compatibility).
     */
    protected function setIsPinnedAttribute(bool $value): void
    {
        $this->attributes['is_pinned'] = $value;
    }

}