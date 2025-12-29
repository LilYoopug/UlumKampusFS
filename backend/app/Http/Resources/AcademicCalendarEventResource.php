<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $title
 * @property string $start_date
 * @property string $end_date
 * @property string $category
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class AcademicCalendarEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start_date' => $this->start_date ? (is_string($this->start_date) ? $this->start_date : $this->start_date->toIso8601String()) : null,
            'end_date' => $this->end_date ? (is_string($this->end_date) ? $this->end_date : $this->end_date->toIso8601String()) : null,
            'category' => $this->category,
            'description' => $this->description,
            'created_at' => $this->created_at ? (is_string($this->created_at) ? $this->created_at : $this->created_at->toIso8601String()) : null,
            'updated_at' => $this->updated_at ? (is_string($this->updated_at) ? $this->updated_at : $this->updated_at->toIso8601String()) : null,
            'is_active' => $this->isActive(),
            'is_upcoming' => $this->isUpcoming(),
            'is_past' => $this->isPast(),
        ];
    }

    /**
     * Check if the event is active (currently happening)
     */
    public function isActive(): bool
    {
        if (!$this->start_date || !$this->end_date) {
            return false;
        }

        $now = now();
        $startDate = is_string($this->start_date) ? \Carbon\Carbon::parse($this->start_date) : $this->start_date;
        $endDate = is_string($this->end_date) ? \Carbon\Carbon::parse($this->end_date) : $this->end_date;
        return $now->between($startDate, $endDate);
    }

    /**
     * Check if the event is upcoming
     */
    public function isUpcoming(): bool
    {
        if (!$this->start_date) {
            return false;
        }

        $now = now();
        $startDate = is_string($this->start_date) ? \Carbon\Carbon::parse($this->start_date) : $this->start_date;
        return $startDate->isFuture();
    }

    /**
     * Check if the event is in the past
     */
    public function isPast(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        $now = now();
        $endDate = is_string($this->end_date) ? \Carbon\Carbon::parse($this->end_date) : $this->end_date;
        return $endDate->isPast();
    }
}