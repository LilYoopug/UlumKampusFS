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
 * @property string|null $image_url
 * @property string|null $mode
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $instructor_avatar_url
 * @property array $learning_objectives
 * @property array $syllabus_data
 * @property-read \App\Models\User|null $instructor
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
            'title' => $this->name,
            'instructor' => $this->instructor?->name ?? '',
            'instructorId' => $this->instructor_id,
            'facultyId' => $this->faculty_id,
            'majorId' => $this->major_id,
            'sks' => $this->credit_hours,
            'description' => $this->description,
            'imageUrl' => $this->image_url,
            'progress' => null, // Will be computed based on user's progress
            'gradeLetter' => null, // Will be computed based on user's grade
            'gradeNumeric' => null, // Will be computed based on user's grade
            'completionDate' => null, // Will be computed based on completion
            'mode' => $this->mode,
            'status' => $this->is_active ? 'Published' : 'Draft',
            'learningObjectives' => $this->learning_objectives ?? [],
            'syllabus' => $this->syllabus_data ?? [],
            'modules' => CourseModuleResource::collection($this->whenLoaded('modules')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'instructorAvatarUrl' => $this->instructor_avatar_url,
            'instructorBioKey' => $this->instructor_bio_key ?? null,
        ];
    }
}