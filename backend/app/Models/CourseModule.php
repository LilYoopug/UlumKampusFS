<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseModule extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'content',
        'type',
        'video_url',
        'document_url',
        'duration',
        'captions_url',
        'attachment_url',
        'order',
        'is_published',
        'published_at',
        'start_time',
        'live_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'start_time' => 'datetime',
        ];
    }

    /**
     * Get the course that owns this module.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the assignments for this module.
     */
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get the discussion threads for this module.
     */
    public function discussionThreads()
    {
        return $this->hasMany(DiscussionThread::class);
    }

    /**
     * Scope a query to only include published modules.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to order by order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get the resourceUrl value (alias for video_url or document_url for frontend compatibility).
     */
    protected function getResourceUrlAttribute(): ?string
    {
        return $this->attributes['video_url'] ?? $this->attributes['document_url'] ?? null;
    }

    /**
     * Set the resourceUrl value (for frontend compatibility).
     */
    protected function setResourceUrlAttribute(?string $value): void
    {
        $this->attributes['video_url'] = $value;
    }

    /**
     * Get the captionsUrl value (alias for captions_url for frontend compatibility).
     */
    protected function getCaptionsUrlAttribute(): ?string
    {
        return $this->attributes['captions_url'] ?? null;
    }

    /**
     * Set the captionsUrl value (alias for captions_url for frontend compatibility).
     */
    protected function setCaptionsUrlAttribute(?string $value): void
    {
        $this->attributes['captions_url'] = $value;
    }

    /**
     * Get the attachmentUrl value (alias for attachment_url for frontend compatibility).
     */
    protected function getAttachmentUrlAttribute(): ?string
    {
        return $this->attributes['attachment_url'] ?? null;
    }

    /**
     * Set the attachmentUrl value (alias for attachment_url for frontend compatibility).
     */
    protected function setAttachmentUrlAttribute(?string $value): void
    {
        $this->attributes['attachment_url'] = $value;
    }
}