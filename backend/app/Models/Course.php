<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read \App\Models\Faculty|null $faculty
 * @property-read \App\Models\Major|null $major
 * @property-read \App\Models\User|null $instructor
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CourseModule[] $modules
 */
class Course extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'faculty_id',
        'major_id',
        'instructor_id',
        'code',
        'name',
        'description',
        'credit_hours',
        'capacity',
        'current_enrollment',
        'semester',
        'year',
        'schedule',
        'room',
        'is_active',
        'mode',
        'status',
        'image_url',
        'instructor_avatar_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credit_hours' => 'integer',
            'capacity' => 'integer',
            'current_enrollment' => 'integer',
            'year' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The attributes that should be hidden from arrays.
     *
     * @var list<string>
     */
    protected $hidden = [
    ];

    /**
     * Get the faculty that owns this course.
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the major that owns this course.
     */
    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    /**
     * Get the instructor (user) that teaches this course.
     */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get the modules for this course.
     */
    public function modules()
    {
        return $this->hasMany(CourseModule::class);
    }

    /**
     * Get the enrollments for this course.
     */
    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Get the students enrolled in this course.
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'course_enrollments', 'course_id', 'student_id')
            ->withPivot('status', 'enrolled_at', 'completed_at', 'final_grade', 'notes')
            ->withTimestamps();
    }

    /**
     * Get the assignments for this course.
     */
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get the announcements for this course.
     */
    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * Get the library resources for this course.
     */
    public function libraryResources()
    {
        return $this->hasMany(LibraryResource::class);
    }

    /**
     * Get the discussion threads for this course.
     */
    public function discussionThreads()
    {
        return $this->hasMany(DiscussionThread::class);
    }

    /**
     * Get the grades for this course.
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Scope a query to only include active courses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by semester.
     */
    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope a query to filter by year.
     */
    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope a query to filter by faculty.
     */
    public function scopeByFaculty($query, $facultyId)
    {
        return $query->where('faculty_id', $facultyId);
    }

    /**
     * Scope a query to filter by major.
     */
    public function scopeByMajor($query, $majorId)
    {
        return $query->where('major_id', $majorId);
    }

    /**
     * Scope a query to filter by instructor.
     */
    public function scopeByInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    /**
     * Check if the course has available capacity.
     */
    public function hasCapacity(): bool
    {
        return $this->current_enrollment < $this->capacity;
    }

    /**
     * Get the number of available spots.
     */
    public function availableSpots(): int
    {
        return max(0, $this->capacity - $this->current_enrollment);
    }

    /**
     * Get the title value (alias for name for frontend compatibility).
     */
    protected function getTitleAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    /**
     * Set the title value (alias for name for frontend compatibility).
     */
    protected function setTitleAttribute(string $value): void
    {
        $this->attributes['name'] = $value;
    }

    /**
     * Get the sks value (alias for credit_hours for frontend compatibility).
     */
    protected function getSksAttribute(): int
    {
        return (int) ($this->attributes['credit_hours'] ?? 0);
    }

    /**
     * Set the sks value (alias for credit_hours for frontend compatibility).
     */
    protected function setSksAttribute(int $value): void
    {
        $this->attributes['credit_hours'] = $value;
    }

    /**
     * Get the instructorId value (alias for instructor_id for frontend compatibility).
     */
    protected function getInstructorIdAttribute(): ?string
    {
        return $this->attributes['instructor_id'] ?? null;
    }

    /**
     * Set the instructorId value (alias for instructor_id for frontend compatibility).
     */
    protected function setInstructorIdAttribute(?string $value): void
    {
        $this->attributes['instructor_id'] = $value;
    }

    /**
     * Get the imageUrl value (for frontend compatibility).
     */
    protected function getImageUrlAttribute(): ?string
    {
        return $this->attributes['image_url'] ?? null;
    }

    /**
     * Set the imageUrl value (for frontend compatibility).
     */
    protected function setImageUrlAttribute(?string $value): void
    {
        $this->attributes['image_url'] = $value;
    }

    /**
     * Get the instructorAvatarUrl value (for frontend compatibility).
     */
    protected function getInstructorAvatarUrlAttribute(): ?string
    {
        // Try to get from loaded instructor relationship
        if ($this->relationLoaded('instructor') && $this->instructor) {
            return $this->instructor->avatar_url ?? null;
        }

        // Load the relationship if not already loaded
        if ($this->instructor_id) {
            $instructor = $this->instructor()->first();
            return $instructor ? $instructor->avatar_url : null;
        }

        return $this->attributes['instructor_avatar_url'] ?? null;
    }

    /**
     * Alias for creditHours (frontend uses sks).
     */
    protected function getCreditHoursAttribute(): int
    {
        return (int) ($this->attributes['credit_hours'] ?? 0);
    }

    /**
     * Get the mode value (for frontend compatibility).
     */
    protected function getModeAttribute(): ?string
    {
        return $this->attributes['mode'] ?? null;
    }

    /**
     * Set the mode value (for frontend compatibility).
     */
    protected function setModeAttribute(?string $value): void
    {
        $this->attributes['mode'] = $value;
    }
}