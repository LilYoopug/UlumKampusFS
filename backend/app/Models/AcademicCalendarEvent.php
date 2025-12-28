<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Academic Calendar Event Model
 *
 * Represents events in the academic calendar such as:
 * - Exams
 * - Holidays
 * - Registration periods
 * - Orientation
 * - Graduation
 */
class AcademicCalendarEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'category',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the category values.
     *
     * @return array<string>
     */
    public static function getCategories(): array
    {
        return [
            'exam',
            'holiday',
            'registration',
            'orientation',
            'graduation',
            'conference',
            'workshop',
            'other',
        ];
    }

    /**
     * Check if the event is currently active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $now = now();
        return $now->between($this->start_date, $this->end_date->endOfDay());
    }

    /**
     * Check if the event is upcoming.
     *
     * @return bool
     */
    public function isUpcoming(): bool
    {
        return now()->lt($this->start_date);
    }

    /**
     * Check if the event has passed.
     *
     * @return bool
     */
    public function isPast(): bool
    {
        return now()->gt($this->end_date->endOfDay());
    }

    /**
     * Get the startDate value (alias for start_date for frontend compatibility).
     */
    protected function getStartDateAttribute(): ?string
    {
        return $this->attributes['start_date'] ?? null;
    }

    /**
     * Set the startDate value (alias for start_date for frontend compatibility).
     */
    protected function setStartDateAttribute(?string $value): void
    {
        $this->attributes['start_date'] = $value;
    }

    /**
     * Get the endDate value (alias for end_date for frontend compatibility).
     */
    protected function getEndDateAttribute(): ?string
    {
        return $this->attributes['end_date'] ?? null;
    }

    /**
     * Set the endDate value (alias for end_date for frontend compatibility).
     */
    protected function setEndDateAttribute(?string $value): void
    {
        $this->attributes['end_date'] = $value;
    }
}