<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $user_id
 * @property int $course_id
 * @property int|null $assignment_id
 * @property float $grade
 * @property string $grade_letter
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class GradeResource extends JsonResource
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
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'assignment_id' => $this->assignment_id,
            'grade' => (float) $this->grade,
            'grade_letter' => $this->grade_letter,
            'comments' => $this->comments,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'student' => new UserResource($this->whenLoaded('user')),
            'course' => new CourseResource($this->whenLoaded('course')),
            'assignment' => new AssignmentResource($this->whenLoaded('assignment')),
            'is_passing' => $this->isPassing(),
            'is_failing' => $this->isFailing(),
        ];
    }
}