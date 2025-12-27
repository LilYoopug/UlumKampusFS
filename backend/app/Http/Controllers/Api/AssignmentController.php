<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments.
     */
    public function index(): JsonResponse
    {
        $assignments = Assignment::published()
            ->with(['course', 'module', 'creator'])
            ->ordered()
            ->get();
        return $this->success($assignments);
    }

    /**
     * Store a newly created assignment.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'module_id' => 'nullable|exists:course_modules,id',
            'created_by' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'due_date' => 'nullable|date',
            'max_points' => 'nullable|numeric|min:0',
            'submission_type' => 'nullable|in:text,file,link,mixed',
            'allowed_file_types' => 'nullable|string',
            'max_file_size' => 'nullable|integer|min:1',
            'attempts_allowed' => 'nullable|integer|min:1',
            'is_published' => 'boolean',
            'allow_late_submission' => 'boolean',
            'late_penalty' => 'nullable|numeric|min:0',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['published_at'] = $validated['is_published'] ? now() : null;

        $assignment = Assignment::create($validated);
        return $this->created($assignment, 'Assignment created successfully');
    }

    /**
     * Display the specified assignment.
     */
    public function show(string $id): JsonResponse
    {
        $assignment = Assignment::with(['course', 'module', 'creator'])->findOrFail($id);
        return $this->success($assignment);
    }

    /**
     * Update the specified assignment.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);

        $validated = $request->validate([
            'course_id' => 'sometimes|exists:courses,id',
            'module_id' => 'nullable|exists:course_modules,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'due_date' => 'nullable|date',
            'max_points' => 'nullable|numeric|min:0',
            'submission_type' => 'nullable|in:text,file,link,mixed',
            'allowed_file_types' => 'nullable|string',
            'max_file_size' => 'nullable|integer|min:1',
            'attempts_allowed' => 'nullable|integer|min:1',
            'is_published' => 'boolean',
            'allow_late_submission' => 'boolean',
            'late_penalty' => 'nullable|numeric|min:0',
            'order' => 'nullable|integer|min:0',
        ]);

        if (isset($validated['is_published']) && $validated['is_published'] && !$assignment->is_published) {
            $validated['published_at'] = now();
        }

        $assignment->update($validated);
        return $this->success($assignment, 'Assignment updated successfully');
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
        return $this->success($submissions);
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

        return $this->created($submission, 'Assignment submitted successfully');
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

        return $this->success($submission);
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
        return $this->success($assignment, 'Assignment published successfully');
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
        return $this->success($assignment, 'Assignment unpublished');
    }
}