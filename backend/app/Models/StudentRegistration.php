<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRegistration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        // Informasi Pribadi
        'nisn',
        'nik',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'religion',
        'address',
        'city',
        'postal_code',
        'citizenship',
        'parent_name',
        'parent_phone',
        'parent_job',
        // Informasi Pendidikan
        'school_name',
        'school_address',
        'graduation_year_school',
        'school_type',
        'school_major',
        'average_grade',
        // Preferensi
        'first_choice_id',
        'second_choice_id',
        // Status & Review
        'status',
        'submitted_at',
        'documents',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'average_grade' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'documents' => 'array',
        ];
    }

    /**
     * Get the user that owns the registration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the first choice major.
     */
    public function firstChoice(): BelongsTo
    {
        return $this->belongsTo(Major::class, 'first_choice_id', 'code');
    }

    /**
     * Get the second choice major.
     */
    public function secondChoice(): BelongsTo
    {
        return $this->belongsTo(Major::class, 'second_choice_id', 'code');
    }

    /**
     * Get the reviewer (admin/staff).
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if registration is submitted.
     */
    public function isSubmitted(): bool
    {
        return $this->status !== 'draft';
    }

    /**
     * Check if registration is under review.
     */
    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    /**
     * Check if registration is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if registration is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
