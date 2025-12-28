<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'faculty_id',
        'major_id',
        'student_id',
        'gpa',
        'enrollment_year',
        'graduation_year',
        'phone',
        'address',
        'avatar',
        'bio',
        'student_status',
        'total_sks',
        'badges',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'badges' => 'array',
        ];
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function coursesInstructing()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class, 'student_id');
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_enrollments', 'student_id', 'course_id')
            ->withPivot('status', 'enrolled_at', 'completed_at', 'final_grade', 'notes')
            ->withTimestamps();
    }

    public function assignmentSubmissions()
    {
        return $this->hasMany(AssignmentSubmission::class, 'student_id');
    }

    public function gradedSubmissions()
    {
        return $this->hasMany(AssignmentSubmission::class, 'graded_by');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    public function libraryResources()
    {
        return $this->hasMany(LibraryResource::class, 'created_by');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'created_by');
    }

    public function discussionThreads()
    {
        return $this->hasMany(DiscussionThread::class, 'created_by');
    }

    public function discussionPosts()
    {
        return $this->hasMany(DiscussionPost::class, 'user_id');
    }

    /**
     * Get the phoneNumber value (alias for phone for frontend compatibility).
     */
    protected function getPhoneNumberAttribute(): ?string
    {
        return $this->attributes['phone'] ?? null;
    }

    /**
     * Set the phoneNumber value (alias for phone for frontend compatibility).
     */
    protected function setPhoneNumberAttribute(?string $value): void
    {
        $this->attributes['phone'] = $value;
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
     * Get the facultyId value (alias for faculty_id for frontend compatibility).
     */
    protected function getFacultyIdAttribute(): ?string
    {
        return $this->attributes['faculty_id'] ?? null;
    }

    /**
     * Set the facultyId value (alias for faculty_id for frontend compatibility).
     */
    protected function setFacultyIdAttribute(?string $value): void
    {
        $this->attributes['faculty_id'] = $value;
    }

    /**
     * Get the majorId value (alias for major_id for frontend compatibility).
     */
    protected function getMajorIdAttribute(): ?string
    {
        return $this->attributes['major_id'] ?? null;
    }

    /**
     * Set the majorId value (alias for major_id for frontend compatibility).
     */
    protected function setMajorIdAttribute(?string $value): void
    {
        $this->attributes['major_id'] = $value;
    }

    /**
     * Get the avatarUrl value (alias for avatar for frontend compatibility).
     */
    protected function getAvatarUrlAttribute(): ?string
    {
        return $this->attributes['avatar'] ?? null;
    }

    /**
     * Set the avatarUrl value (alias for avatar for frontend compatibility).
     */
    protected function setAvatarUrlAttribute(?string $value): void
    {
        $this->attributes['avatar'] = $value;
    }

    /**
     * Get the joinDate value (alias for created_at for frontend compatibility).
     */
    protected function getJoinDateAttribute(): ?string
    {
        return $this->attributes['created_at'] ?? null;
    }

    /**
     * Get the studentStatus value (computed from user data for frontend compatibility).
     */
    protected function getStudentStatusAttribute(): string
    {
        // This would typically come from an enrollment or student_status column
        // For now, default based on enrollment
        return $this->attributes['student_status'] ?? 'Aktif';
    }

    /**
     * Get the totalSks value (computed from enrollments for frontend compatibility).
     */
    protected function getTotalSksAttribute(): int
    {
        // This would typically be computed from enrolled courses
        return (int) ($this->attributes['total_sks'] ?? 0);
    }

    /**
     * Set the totalSks value (for frontend compatibility).
     */
    protected function setTotalSksAttribute(int $value): void
    {
        $this->attributes['total_sks'] = $value;
    }

    /**
     * Get the badges value (for frontend compatibility).
     */
    protected function getBadgesAttribute(): array
    {
        return $this->attributes['badges'] ?? [];
    }

    /**
     * Set the badges value (for frontend compatibility).
     */
    protected function setBadgesAttribute($value): void
    {
        $this->attributes['badges'] = is_array($value) ? json_encode($value) : $value;
    }
}
