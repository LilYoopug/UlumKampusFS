<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $course_id
 * @property int|null $module_id
 * @property int $created_by
 * @property string $title
 * @property string|null $description
 * @property string|null $instructions
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property float|null $max_points
 * @property string|null $submission_type
 * @property string|null $allowed_file_types
 * @property int|null $max_file_size
 * @property int|null $attempts_allowed
 * @property bool $is_published
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property bool $allow_late_submission
 * @property float|null $late_penalty
 * @property int $order
 */
class AssignmentResource extends JsonResource
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
            'module_id' => $this->module_id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'due_date' => $this->due_date?->toIso8601String(),
            'max_points' => $this->max_points,
            'submission_type' => $this->submission_type,
            'allowed_file_types' => $this->allowed_file_types,
            'max_file_size' => $this->max_file_size,
            'attempts_allowed' => $this->attempts_allowed,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toIso8601String(),
            'allow_late_submission' => $this->allow_late_submission,
            'late_penalty' => $this->late_penalty,
            'order' => $this->order,
            'course' => new CourseResource($this->whenLoaded('course')),
            'module' => $this->whenLoaded('module'),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'submissions_count' => $this->whenCounted('submissions'),
            'is_due_soon' => $this->when(isset($this->due_date), $this->isDueSoon()),
            'is_overdue' => $this->when(isset($this->due_date), $this->isOverdue()),
            'days_until_due' => $this->when(isset($this->due_date), function () {
                if (!$this->due_date) return null;
                $days = now()->diffInDays($this->due_date, false);
                return $this->due_date->isFuture() ? $days : -$days;
            }),
        ];
    }
}