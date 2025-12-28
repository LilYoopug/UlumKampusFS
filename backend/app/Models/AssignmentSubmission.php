<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'assignment_id',
        'student_id',
        'content',
        'file_url',
        'file_name',
        'file_size',
        'link_url',
        'status',
        'submitted_at',
        'graded_at',
        'graded_by',
        'grade',
        'feedback',
        'instructor_notes',
        'is_late',
        'late_submission_at',
        'attempt_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
            'grade' => 'decimal:2',
            'is_late' => 'boolean',
            'late_submission_at' => 'datetime',
            'attempt_number' => 'integer',
        ];
    }

    /**
     * Get the assignment for this submission.
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the student (user) who submitted this.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the user (instructor) who graded this submission.
     */
    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Scope a query to only include submitted assignments.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope a query to only include late submissions.
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope a query to only include graded submissions.
     */
    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    /**
     * Scope a query to only include draft submissions.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Check if the submission has been graded.
     */
    public function isGraded(): bool
    {
        return $this->status === 'graded' && $this->grade !== null;
    }

    /**
     * Check if the submission is late.
     */
    public function isLateSubmission(): bool
    {
        return $this->is_late || $this->status === 'late';
    }

    /**
     * Get the grade as a percentage of max points.
     */
    public function gradePercentage(): ?float
    {
        if ($this->grade === null) {
            return null;
        }

        if ($this->relationLoaded('assignment') && $this->assignment) {
            return ($this->grade / $this->assignment->max_points) * 100;
        }

        // If assignment is not loaded, we can't calculate percentage
        return null;
    }

    /**
     * Get the studentId value (alias for student_id for frontend compatibility).
     */
    protected function getStudentIdAttribute(): ?string
    {
        return $this->attributes['student_id'] ?? null;
    }

    /**
     * Set the studentId value (alias for student_id for frontend compatibility).
     */
    protected function setStudentIdAttribute(?string $value): void
    {
        $this->attributes['student_id'] = $value;
    }

    /**
     * Get the submittedAt value (alias for submitted_at for frontend compatibility).
     */
    protected function getSubmittedAtAttribute(): ?string
    {
        return $this->attributes['submitted_at'] ?? null;
    }

    /**
     * Set the submittedAt value (alias for submitted_at for frontend compatibility).
     */
    protected function setSubmittedAtAttribute(?string $value): void
    {
        $this->attributes['submitted_at'] = $value;
    }
}