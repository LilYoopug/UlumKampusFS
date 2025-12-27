<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GradeController extends Controller
{
    /**
     * Display a listing of grades for the current student.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $grades = Grade::where('user_id', $user->id)
            ->with(['course', 'assignment'])
            ->get();
        return $this->success($grades);
    }

    /**
     * Display the specified grade.
     */
    public function show(string $id): JsonResponse
    {
        $user = auth()->user();
        $grade = Grade::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['course', 'assignment'])
            ->firstOrFail();
        return $this->success($grade);
    }

    /**
     * Get grades for a specific course (admin/faculty only).
     */
    public function byCourse(string $courseId): JsonResponse
    {
        $grades = Grade::where('course_id', $courseId)
            ->with(['user', 'assignment'])
            ->get();
        return $this->success($grades);
    }

    /**
     * Get grades for a specific assignment (admin/faculty only).
     */
    public function byAssignment(string $assignmentId): JsonResponse
    {
        $grades = Grade::where('assignment_id', $assignmentId)
            ->with(['user', 'course'])
            ->get();
        return $this->success($grades);
    }

    /**
     * Get grades for a specific student (admin/faculty only).
     */
    public function byStudent(string $studentId): JsonResponse
    {
        $grades = Grade::where('user_id', $studentId)
            ->with(['course', 'assignment'])
            ->get();
        return $this->success($grades);
    }

    /**
     * Store a newly created grade (admin/faculty only).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'assignment_id' => 'nullable|exists:assignments,id',
            'grade' => 'required|numeric|min:0',
            'comments' => 'nullable|string',
        ]);

        $grade = Grade::create($validated);
        return $this->created($grade, 'Grade created successfully');
    }

    /**
     * Update the specified grade (admin/faculty only).
     */
    public function update(string $id, Request $request): JsonResponse
    {
        $grade = Grade::findOrFail($id);

        $validated = $request->validate([
            'grade' => 'sometimes|required|numeric|min:0',
            'comments' => 'nullable|string',
        ]);

        $grade->update($validated);
        return $this->success($grade, 'Grade updated successfully');
    }

    /**
     * Remove the specified grade (admin/faculty only).
     */
    public function destroy(string $id): JsonResponse
    {
        $grade = Grade::findOrFail($id);
        $grade->delete();
        return $this->noContent();
    }

    /**
     * Get the current student's grades.
     */
    public function myGrades(): JsonResponse
    {
        $user = auth()->user();
        $grades = Grade::where('user_id', $user->id)
            ->with(['course', 'assignment'])
            ->get();

        $gpa = $grades->isNotEmpty()
            ? $grades->map(fn($g) => $g->grade_letter === 'A' ? 4.0
                : ($g->grade_letter === 'B' ? 3.0
                    : ($g->grade_letter === 'C' ? 2.0
                        : ($g->grade_letter === 'D' ? 1.0 : 0.0))))
                ->avg()
            : 0.0;

        return $this->success([
            'grades' => $grades,
            'gpa' => round($gpa, 2),
            'total_courses' => $grades->pluck('course_id')->unique()->count(),
        ]);
    }
}