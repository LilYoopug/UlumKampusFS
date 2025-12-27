<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $course_id
 * @property int $student_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $enrolled_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $dropped_at
 * @property string|null $grade
 * @property string|null $notes
 * @property string|null $withdrawal_reason
 */
class EnrollmentResource extends JsonResource
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
            'course_id' => $this->course_id,
            'student_id' => $this->student_id,
            'status' => $this->status,
            'enrolled_at' => $this->enrolled_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'dropped_at' => $this->dropped_at?->toIso8601String(),
            'grade' => $this->grade,
            'notes' => $this->notes,
            'withdrawal_reason' => $this->withdrawal_reason,
            'course' => new CourseResource($this->whenLoaded('course')),
            'student' => new UserResource($this->whenLoaded('student')),
            'is_active' => $this->status === 'enrolled',
            'is_completed' => $this->status === 'completed',
            'is_dropped' => $this->status === 'dropped',
            'is_pending' => $this->status === 'pending',
        ];
    }
}