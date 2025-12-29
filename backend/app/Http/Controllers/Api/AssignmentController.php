<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AssignmentRequest;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\AssignmentSubmissionResource;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssignmentController extends ApiController
{
    /**
     * Display a listing of assignments.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $assignments = Assignment::published()
            ->with(['course', 'module', 'creator'])
            ->ordered()
            ->paginate($perPage);
        return $this->paginated(
            AssignmentResource::collection($assignments),
            'Assignments retrieved successfully'
        );
    }

    /**
     * Store a newly created assignment.
     */
    public function store(AssignmentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['published_at'] = $validated['is_published'] ?? false ? now() : null;

        $assignment = Assignment::create($validated);
        return $this->created(
            new AssignmentResource($assignment->load(['course', 'module', 'creator'])),
            'Assignment created successfully'
        );
    }

    /**
     * Display the specified assignment.
     */
    public function show(string $id): JsonResponse
    {
        $assignment = Assignment::with(['course', 'module', 'creator'])->findOrFail($id);
        return $this->success(
            new AssignmentResource($assignment),
            'Assignment retrieved successfully'
        );
    }

    /**
     * Update the specified assignment.
     */
    public function update(AssignmentRequest $request, string $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        $validated = $request->validated();

        if (isset($validated['is_published']) && $validated['is_published'] && !$assignment->is_published) {
            $validated['published_at'] = now();
        }

        $assignment->update($validated);
        return $this->success(
            new AssignmentResource($assignment->load(['course', 'module', 'creator'])),
            'Assignment updated successfully'
        );
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy(string $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        $assignment->delete();
        return $this->noContent();
    }

    /**
     * Get submissions for this assignment.
     */
    public function submissions(string $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        $submissions = $assignment->submissions()->with('student')->get();
        return $this->success(
            AssignmentSubmissionResource::collection($submissions),
            'Assignment submissions retrieved successfully'
        );
    }

    /**
     * Submit the current student's assignment.
     */
    public function submit(string $id, Request $request): JsonResponse
    {
        $user = auth()->user();
        $assignment = Assignment::findOrFail($id);

        $validated = $request->validate([
            'content' => 'nullable|string',
            'file_url' => 'nullable|url|max:500',
            'file_name' => 'nullable|string|max:255',
            'file_size' => 'nullable|integer',
            'link_url' => 'nullable|url|max:500',
        ]);

        $isLate = $assignment->due_date && now()->isAfter($assignment->due_date);
        $attemptNumber = AssignmentSubmission::where('assignment_id', $id)
            ->where('student_id', $user->id)
            ->count() + 1;

        $submission = AssignmentSubmission::create([
            'assignment_id' => $id,
            'student_id' => $user->id,
            'content' => $validated['content'] ?? null,
            'file_url' => $validated['file_url'] ?? null,
            'file_name' => $validated['file_name'] ?? null,
            'file_size' => $validated['file_size'] ?? null,
            'link_url' => $validated['link_url'] ?? null,
            'status' => $isLate ? 'late' : 'submitted',
            'submitted_at' => now(),
            'is_late' => $isLate,
            'late_submission_at' => $isLate ? now() : null,
            'attempt_number' => $attemptNumber,
        ]);

        return $this->created(
            new AssignmentSubmissionResource($submission->load(['assignment', 'student'])),
            'Assignment submitted successfully'
        );
    }

    /**
     * Get the current student's submission for this assignment.
     */
    public function mySubmission(string $id): JsonResponse
    {
        $user = auth()->user();
        $submission = AssignmentSubmission::where('assignment_id', $id)
            ->where('student_id', $user->id)
            ->orderBy('attempt_number', 'desc')
            ->first();

        if (!$submission) {
            return $this->notFound('No submission found for this assignment');
        }

        return $this->success(
            new AssignmentSubmissionResource($submission->load(['assignment', 'student'])),
            'Submission retrieved successfully'
        );
    }

    /**
     * Publish the assignment.
     */
    public function publish(string $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        $assignment->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
        return $this->success(
            new AssignmentResource($assignment->load(['course', 'module', 'creator'])),
            'Assignment published successfully'
        );
    }

    /**
     * Unpublish the assignment.
     */
    public function unpublish(string $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        $assignment->update([
            'is_published' => false,
            'published_at' => null,
        ]);
        return $this->success(
            new AssignmentResource($assignment->load(['course', 'module', 'creator'])),
            'Assignment unpublished'
        );
    }
}