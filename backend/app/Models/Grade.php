<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'assignment_id',
        'grade',
        'grade_letter',
        'comments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'grade' => 'decimal:2',
        ];
    }

    /**
     * Get the user (student) that owns this grade.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course that owns this grade.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the assignment that owns this grade.
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter by course.
     */
    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope a query to filter by assignment.
     */
    public function scopeByAssignment($query, $assignmentId)
    {
        return $query->where('assignment_id', $assignmentId);
    }

    /**
     * Scope a query to get passing grades.
     */
    public function scopePassing($query, $threshold = 60.00)
    {
        return $query->where('grade', '>=', $threshold);
    }

    /**
     * Scope a query to get failing grades.
     */
    public function scopeFailing($query, $threshold = 59.99)
    {
        return $query->where('grade', '<=', $threshold);
    }

    /**
     * Get the grade letter based on the numeric grade.
     */
    public function getGradeLetter(): string
    {
        if ($this->grade === null) {
            return 'N/A';
        }

        return match (true) {
            $this->grade >= 90 => 'A',
            $this->grade >= 80 => 'B',
            $this->grade >= 70 => 'C',
            $this->grade >= 60 => 'D',
            default => 'F',
        };
    }

    /**
     * Check if the grade is passing.
     */
    public function isPassing($threshold = 60.00): bool
    {
        return $this->grade !== null && $this->grade >= $threshold;
    }

    /**
     * Check if the grade is failing.
     */
    public function isFailing($threshold = 59.99): bool
    {
        return $this->grade !== null && $this->grade <= $threshold;
    }

    /**
     * Get the grade percentage if there's a max points value from the assignment.
     */
    public function getPercentage(): ?float
    {
        if ($this->grade === null || $this->assignment === null) {
            return null;
        }

        return ($this->grade / $this->assignment->max_points) * 100;
    }

    /**
     * Get the userId value (alias for user_id for frontend compatibility).
     */
    protected function getUserIdAttribute(): ?string
    {
        return $this->attributes['user_id'] ?? null;
    }

    /**
     * Set the userId value (alias for user_id for frontend compatibility).
     */
    protected function setUserIdAttribute(?string $value): void
    {
        $this->attributes['user_id'] = $value;
    }

    /**
     * Get the courseId value (alias for course_id for frontend compatibility).
     */
    protected function getCourseIdAttribute(): ?string
    {
        return $this->attributes['course_id'] ?? null;
    }

    /**
     * Set the courseId value (alias for course_id for frontend compatibility).
     */
    protected function setCourseIdAttribute(?string $value): void
    {
        $this->attributes['course_id'] = $value;
    }

    /**
     * Get the assignmentId value (alias for assignment_id for frontend compatibility).
     */
    protected function getAssignmentIdAttribute(): ?string
    {
        return $this->attributes['assignment_id'] ?? null;
    }

    /**
     * Set the assignmentId value (alias for assignment_id for frontend compatibility).
     */
    protected function setAssignmentIdAttribute(?string $value): void
    {
        $this->attributes['assignment_id'] = $value;
    }
}