<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $assignment_id
 * @property int $student_id
 * @property string|null $content
 * @property string|null $file_url
 * @property string|null $file_name
 * @property int|null $file_size
 * @property string|null $link_url
 * @property string $status
 * @property \Illuminate\Support\Carbon $submitted_at
 * @property \Illuminate\Support\Carbon|null $graded_at
 * @property int|null $graded_by
 * @property float|null $grade
 * @property string|null $feedback
 * @property string|null $instructor_notes
 * @property bool $is_late
 * @property \Illuminate\Support\Carbon|null $late_submission_at
 * @property int $attempt_number
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Assignment|null $assignment
 * @property-read \App\Models\User|null $student
 * @property-read \App\Models\User|null $grader
 */
class AssignmentSubmissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Check if this resource is being used in the context of an assignment (for frontend compatibility)
        $forAssignment = $request->route() && strpos($request->route()->getName() ?? '', 'assignments') !== false;

        if ($forAssignment) {
            // Format for assignment submissions array (frontend compatibility)
            return [
                'studentId' => $this->student_id,
                'submittedAt' => $this->submitted_at->toIso8601String(),
                'file' => [
                    'name' => $this->file_name ?? '',
                    'url' => $this->file_url ?? ''
                ],
                'gradeLetter' => $this->grade_letter ?? $this->getGradeLetterFromNumeric($this->grade),
                'gradeNumeric' => $this->grade,
                'feedback' => $this->feedback,
            ];
        }

        // Default format for direct access to submission
        return [
            'id' => $this->id,
            'assignment_id' => $this->assignment_id,
            'student_id' => $this->student_id,
            'content' => $this->content,
            'file_url' => $this->file_url,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'link_url' => $this->link_url,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at->toIso8601String(),
            'graded_at' => $this->graded_at?->toIso8601String(),
            'graded_by' => $this->graded_by,
            'grade' => $this->grade,
            'feedback' => $this->feedback,
            'instructor_notes' => $this->instructor_notes,
            'is_late' => $this->is_late,
            'late_submission_at' => $this->late_submission_at?->toIso8601String(),
            'attempt_number' => $this->attempt_number,
            'assignment' => new AssignmentResource($this->whenLoaded('assignment')),
            'student' => new UserResource($this->whenLoaded('student')),
            'grader' => new UserResource($this->whenLoaded('grader')),
            'is_graded' => $this->isGraded(),
            'grade_percentage' => $this->when($this->grade !== null && isset($this->assignment), fn() => $this->gradePercentage()),
            'can_edit' => $this->when($request->user()?->id === $this->student_id, $this->status === 'draft'),
        ];
    }

    /**
     * Check if the submission is graded
     */
    public function isGraded(): bool
    {
        return $this->grade !== null;
    }

    /**
     * Calculate grade percentage
     */
    public function gradePercentage(): float
    {
        if ($this->grade === null || !$this->assignment) {
            return 0.0;
        }

        $maxPoints = $this->assignment->max_points;
        if ($maxPoints === null || $maxPoints === 0) {
            return 0.0;
        }

        return ($this->grade / $maxPoints) * 100;
    }

    /**
     * Convert numeric grade to letter grade
     */
    private function getGradeLetterFromNumeric(?float $grade): ?string
    {
        if ($grade === null) {
            return null;
        }

        if ($grade >= 85) return 'A';
        if ($grade >= 80) return 'A-';
        if ($grade >= 75) return 'B+';
        if ($grade >= 70) return 'B';
        if ($grade >= 65) return 'B-';
        if ($grade >= 60) return 'C+';
        if ($grade >= 55) return 'C';
        if ($grade >= 40) return 'D';
        return 'E';
    }
}