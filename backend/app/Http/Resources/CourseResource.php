<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $faculty_id
 * @property int $major_id
 * @property int $instructor_id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int $credit_hours
 * @property int $capacity
 * @property int $current_enrollment
 * @property string $semester
 * @property int $year
 * @property string|null $schedule
 * @property string|null $room
 * @property bool $is_active
 */
class CourseResource extends JsonResource
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
            'faculty_id' => $this->faculty_id,
            'major_id' => $this->major_id,
            'instructor_id' => $this->instructor_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'credit_hours' => $this->credit_hours,
            'capacity' => $this->capacity,
            'current_enrollment' => $this->current_enrollment,
            'semester' => $this->semester,
            'year' => $this->year,
            'schedule' => $this->schedule,
            'room' => $this->room,
            'is_active' => $this->is_active,
            'faculty' => new FacultyResource($this->whenLoaded('faculty')),
            'major' => new MajorResource($this->whenLoaded('major')),
            'instructor' => new UserResource($this->whenLoaded('instructor')),
            'available_spots' => max(0, $this->capacity - $this->current_enrollment),
            'has_capacity' => $this->current_enrollment < $this->capacity,
        ];
    }
}