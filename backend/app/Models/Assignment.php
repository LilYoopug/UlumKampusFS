<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
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
        'description',
        'instructions',
        'due_date',
        'max_points',
        'submission_type',
        'allowed_file_types',
        'max_file_size',
        'attempts_allowed',
        'is_published',
        'published_at',
        'allow_late_submission',
        'late_penalty',
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
            'due_date' => 'datetime',
            'max_points' => 'decimal:2',
            'max_file_size' => 'integer',
            'attempts_allowed' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'allow_late_submission' => 'boolean',
            'late_penalty' => 'decimal:2',
            'order' => 'integer',
        ];
    }

    /**
     * Get the course that owns this assignment.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the module that owns this assignment.
     */
    public function module()
    {
        return $this->belongsTo(CourseModule::class);
    }

    /**
     * Get the user (instructor) who created this assignment.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the submissions for this assignment.
     */
    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /**
     * Get the grades for this assignment.
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Scope a query to only include published assignments.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to get upcoming assignments (not yet due).
     */
    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>', now());
    }

    /**
     * Scope a query to get past due assignments.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now());
    }

    /**
     * Scope a query to order by order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Check if the assignment is due soon (within 24 hours).
     */
    public function isDueSoon(): bool
    {
        return $this->due_date && $this->due_date->diffInHours(now()) <= 24 && $this->due_date->isFuture();
    }

    /**
     * Check if the assignment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }
}