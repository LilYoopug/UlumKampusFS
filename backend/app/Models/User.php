<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\PaymentItem;
use App\Models\UserPaymentStatus;
use Illuminate\Support\Facades\Date;

/**
 * @property-read \App\Models\Faculty|null $faculty
 * @property-read \App\Models\Major|null $major
 */
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

    public function userPaymentStatuses()
    {
        return $this->hasMany(UserPaymentStatus::class);
    }

    public function paymentItems()
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class);
    }

    /**
     * Get all payment items with their status for this user
     * Returns payment items with status (paid/pending/unpaid)
     * Checks both UserPaymentStatus and PaymentHistory to determine status
     * If no status exists in either, defaults to 'unpaid'
     */
    public function getPaymentItemsWithStatus()
    {
        $allPaymentItems = PaymentItem::all();
        $userPaymentStatuses = $this->userPaymentStatuses()->get()->keyBy('payment_item_id');
        
        // Also check PaymentHistory for completed payments
        // Match by title similarity (title_key in payment_item vs title in payment_history)
        $paymentHistories = $this->paymentHistories()
            ->whereIn('status', ['completed', 'paid'])
            ->get();

        return $allPaymentItems->map(function ($paymentItem) use ($userPaymentStatuses, $paymentHistories) {
            $status = $userPaymentStatuses->get($paymentItem->id);
            
            // Check if there's a matching payment history record
            $hasPaymentHistory = $paymentHistories->first(function ($history) use ($paymentItem) {
                $paymentItemTitle = strtolower($paymentItem->title_key ?: $paymentItem->title ?: '');
                $historyTitle = strtolower($history->title ?: '');
                
                // Check for partial match or similar titles
                return str_contains($historyTitle, $paymentItemTitle) 
                    || str_contains($paymentItemTitle, $historyTitle)
                    || $this->titlesMatch($paymentItemTitle, $historyTitle);
            });

            // Determine final status - if either UserPaymentStatus or PaymentHistory shows paid
            $finalStatus = 'unpaid';
            $paidAt = null;
            $dueDate = null;
            
            if ($status && $status->status === 'paid') {
                $finalStatus = 'paid';
                $paidAt = $status->paid_at;
                $dueDate = $status->due_date;
            } elseif ($hasPaymentHistory) {
                $finalStatus = 'paid';
                $paidAt = $hasPaymentHistory->payment_date;
            } elseif ($status) {
                $finalStatus = $status->status;
                $paidAt = $status->paid_at;
                $dueDate = $status->due_date;
            }

            return [
                'id' => $paymentItem->id,
                'item_id' => $paymentItem->item_id,
                'title_key' => $paymentItem->title_key ?: $paymentItem->title,
                'description_key' => $paymentItem->description_key ?: $paymentItem->description,
                'amount' => $paymentItem->amount,
                'status' => $finalStatus,
                'due_date' => $dueDate,
                'paid_at' => $paidAt,
            ];
        });
    }
    
    /**
     * Check if two payment titles match based on common keywords
     */
    private function titlesMatch(string $title1, string $title2): bool
    {
        // Define keyword mappings for common payment types
        $keywordMappings = [
            ['pendaftaran', 'registration', 'biaya pendaftaran'],
            ['semester', 'biaya semester', 'spp'],
            ['ujian', 'exam', 'biaya ujian', 'uts', 'uas'],
            ['lain', 'other', 'miscellaneous'],
        ];
        
        foreach ($keywordMappings as $keywords) {
            $title1Matches = false;
            $title2Matches = false;
            
            foreach ($keywords as $keyword) {
                if (str_contains($title1, $keyword)) $title1Matches = true;
                if (str_contains($title2, $keyword)) $title2Matches = true;
            }
            
            if ($title1Matches && $title2Matches) {
                return true;
            }
        }
        
        return false;
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
        $badges = $this->attributes['badges'] ?? null;
        if (is_string($badges)) {
            return json_decode($badges, true) ?: [];
        }
        return is_array($badges) ? $badges : [];
    }

    /**
     * Set the badges value (for frontend compatibility).
     */
    protected function setBadgesAttribute($value): void
    {
        $this->attributes['badges'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // No automatic payment status creation
        // Payment status is only created when payment is made
        // Otherwise, defaults to 'unpaid' via getPaymentItemsWithStatus()
    }
}
