<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'faculty_id',
        'created_by',
        'title',
        'content',
        'category',
        'target_audience',
        'priority',
        'is_published',
        'published_at',
        'expires_at',
        'allow_comments',
        'view_count',
        'attachment_url',
        'attachment_type',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'allow_comments' => 'boolean',
            'view_count' => 'integer',
            'order' => 'integer',
        ];
    }

    /**
     * Get the course that owns this announcement.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the faculty that owns this announcement.
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the user who created this announcement.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include published announcements.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include active announcements (not expired).
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to filter by target audience.
     */
    public function scopeForAudience($query, $audience)
    {
        return $query->where('target_audience', $audience);
    }

    /**
     * Scope a query to order by order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Check if the announcement is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the announcement is active (published and not expired).
     */
    public function isActive(): bool
    {
        return $this->is_published && !$this->isExpired();
    }

    /**
     * Increment the view count.
     */
    public function incrementViewCount(): int
    {
        $this->increment('view_count');
        return $this->view_count;
    }
}