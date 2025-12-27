<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentSubmissionResource;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubmissionController extends ApiController
{
    /**
     * Display a listing of submissions for the current student.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $submissions = AssignmentSubmission::where('student_id', $user->id)
            ->with(['assignment.course', 'assignment.module'])
            ->latest('submitted_at')
            ->get();
        return $this->success(
            AssignmentSubmissionResource::collection($submissions),
            'Submissions retrieved successfully'
        );
    }

    /**
     * Display the specified submission.
     */
    public function show(string $id): JsonResponse
    {
        $user = auth()->user();
        $submission = AssignmentSubmission::where('id', $id)
            ->where('student_id', $user->id)
            ->with(['assignment', 'assignment.course', 'grader'])
            ->firstOrFail();
        return $this->success(
            new AssignmentSubmissionResource($submission),
            'Submission retrieved successfully'
        );
    }

    /**
     * Update the specified submission (student can edit draft submissions).
     */
    public function update(string $id, Request $request): JsonResponse
    {
        $user = auth()->user();
        $submission = AssignmentSubmission::where('id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        if ($submission->status !== 'draft') {
            return $this->error('Can only edit draft submissions', 403);
        }

        $validated = $request->validate([
            'content' => 'nullable|string',
            'file_url' => 'nullable|url|max:500',
            'file_name' => 'nullable|string|max:255',
            'file_size' => 'nullable|integer',
            'link_url' => 'nullable|url|max:500',
        ]);

        $submission->update($validated);
        return $this->success(
            new AssignmentSubmissionResource($submission->load(['assignment', 'student'])),
            'Submission updated successfully'
        );
    }

    /**
     * Get submissions for a specific assignment (admin/faculty only).
     */
    public function byAssignment(string $assignmentId): JsonResponse
    {
        $submissions = AssignmentSubmission::where('assignment_id', $assignmentId)
            ->with(['student', 'grader'])
            ->latest('submitted_at')
            ->get();
        return $this->success(
            AssignmentSubmissionResource::collection($submissions),
            'Assignment submissions retrieved successfully'
        );
    }

    /**
     * Grade a submission.
     */
    public function grade(string $id, Request $request): JsonResponse
    {
        $submission = AssignmentSubmission::findOrFail($id);

        $validated = $request->validate([
            'grade' => 'required|numeric|min:0',
        ]);

        $submission->update([
            'grade' => $validated['grade'],
            'graded_at' => now(),
            'graded_by' => auth()->id(),
            'status' => 'graded',
        ]);

        // Create or update grade record
        Grade::updateOrCreate(
            [
                'user_id' => $submission->student_id,
                'assignment_id' => $submission->assignment_id,
            ],
            [
                'course_id' => $submission->assignment->course_id,
                'grade' => $validated['grade'],
                'comments' => $submission->feedback,
            ]
        );

        return $this->success(
            new AssignmentSubmissionResource($submission->load(['assignment', 'student', 'grader'])),
            'Submission graded successfully'
        );
    }

    /**
     * Provide feedback on a submission.
     */
    public function feedback(string $id, Request $request): JsonResponse
    {
        $submission = AssignmentSubmission::findOrFail($id);

        $validated = $request->validate([
            'feedback' => 'required|string',
            'instructor_notes' => 'nullable|string',
        ]);

        $submission->update($validated);

        // Update grade record if it exists
        if ($submission->grade !== null) {
            Grade::where('user_id', $submission->student_id)
                ->where('assignment_id', $submission->assignment_id)
                ->update([
                    'comments' => $validated['feedback'],
                ]);
        }

        return $this->success(
            new AssignmentSubmissionResource($submission->load(['assignment', 'student', 'grader'])),
            'Feedback added successfully'
        );
    }
}