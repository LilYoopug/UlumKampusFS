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
 * @property string|null $category
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AssignmentSubmission[] $submissions
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
            'courseId' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'dueDate' => $this->due_date?->toIso8601String(),
            'files' => [], // This would need to be populated based on attachments
            'submissions' => AssignmentSubmissionResource::collection($this->whenLoaded('submissions')),
            'type' => $this->submission_type,
            'category' => $this->category ?? 'Tugas',
            'maxScore' => $this->max_points,
            'instructions' => $this->instructions,
            'attachments' => [], // This would need to be populated based on actual attachments
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}