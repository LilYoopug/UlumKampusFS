<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\GradeRequest;
use App\Http\Resources\GradeResource;
use App\Models\Grade;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Grade Management API Controller
 *
 * Handles CRUD operations for grades including:
 * - Grade listing for students and faculty
 * - Grade creation, retrieval, update, and deletion
 * - Grade filtering by course, assignment, and student
 * - GPA calculation and grade distribution analytics
 */
class GradeController extends ApiController
{
    /**
     * Display a listing of grades for the current student.
     *
     * @queryParam course_id Filter by course ID
     * @queryParam assignment_id Filter by assignment ID
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = Grade::where('user_id', $user->id);

        // Filter by course
        if ($request->has('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }

        // Filter by assignment
        if ($request->has('assignment_id')) {
            $query->where('assignment_id', $request->input('assignment_id'));
        }

        $grades = $query->with(['course', 'assignment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(
            GradeResource::collection($grades),
            'Grades retrieved successfully'
        );
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

        return $this->success(
            new GradeResource($grade),
            'Grade retrieved successfully'
        );
    }

    /**
     * Get grades for a specific course (admin/faculty only).
     *
     * @queryParam student_id Filter by student ID
     */
    public function byCourse(string $courseId, Request $request): JsonResponse
    {
        $query = Grade::where('course_id', $courseId);

        // Filter by student if provided
        if ($request->has('student_id')) {
            $query->where('user_id', $request->input('student_id'));
        }

        $grades = $query->with(['user', 'assignment', 'course'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(
            GradeResource::collection($grades),
            'Course grades retrieved successfully'
        );
    }

    /**
     * Get grades for a specific assignment (admin/faculty only).
     */
    public function byAssignment(string $assignmentId): JsonResponse
    {
        $grades = Grade::where('assignment_id', $assignmentId)
            ->with(['user', 'course', 'assignment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(
            GradeResource::collection($grades),
            'Assignment grades retrieved successfully'
        );
    }

    /**
     * Get grades for a specific student (admin/faculty only).
     *
     * @queryParam course_id Filter by course ID
     */
    public function byStudent(string $studentId, Request $request): JsonResponse
    {
        $query = Grade::where('user_id', $studentId);

        // Filter by course if provided
        if ($request->has('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }

        $grades = $query->with(['course', 'assignment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(
            GradeResource::collection($grades),
            'Student grades retrieved successfully'
        );
    }

    /**
     * Store a newly created grade (admin/faculty only).
     *
     * Requires admin or faculty role (enforced by GradeRequest).
     */
    public function store(GradeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $grade = Grade::create($validated);

        return $this->created(
            new GradeResource($grade->load(['user', 'course', 'assignment'])),
            'Grade created successfully'
        );
    }

    /**
     * Update the specified grade (admin/faculty only).
     *
     * Requires admin or faculty role (enforced by route middleware).
     */
    public function update(GradeRequest $request, string $id): JsonResponse
    {
        $grade = Grade::findOrFail($id);

        $validated = $request->validated();

        $grade->update($validated);

        return $this->success(
            new GradeResource($grade->load(['user', 'course', 'assignment'])),
            'Grade updated successfully'
        );
    }

    /**
     * Remove the specified grade (admin/faculty only).
     *
     * Requires admin or faculty role (enforced by route middleware).
     */
    public function destroy(string $id): JsonResponse
    {
        $grade = Grade::findOrFail($id);
        $grade->delete();

        return $this->noContent();
    }

    /**
     * Get the current student's grades with GPA calculation.
     */
    public function myGrades(): JsonResponse
    {
        $user = auth()->user();
        $grades = Grade::where('user_id', $user->id)
            ->with(['course', 'assignment'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate GPA based on grade letters
        $gpa = $grades->isNotEmpty()
            ? $grades->map(fn($g) => $g->grade_letter === 'A' ? 4.0
                : ($g->grade_letter === 'B' ? 3.0
                    : ($g->grade_letter === 'C' ? 2.0
                        : ($g->grade_letter === 'D' ? 1.0 : 0.0))))
                ->avg()
            : 0.0;

        return $this->success([
            'grades' => GradeResource::collection($grades),
            'gpa' => round($gpa, 2),
            'total_courses' => $grades->pluck('course_id')->unique()->count(),
            'total_assignments' => $grades->count(),
        ], 'My grades retrieved successfully');
    }

    /**
     * Get grade distribution for a course (admin/faculty only).
     *
     * Returns counts of A, B, C, D, and F grades.
     */
    public function distribution(string $courseId): JsonResponse
    {
        $grades = Grade::where('course_id', $courseId)->get();

        $distribution = [
            'A' => $grades->where('grade_letter', 'A')->count(),
            'B' => $grades->where('grade_letter', 'B')->count(),
            'C' => $grades->where('grade_letter', 'C')->count(),
            'D' => $grades->where('grade_letter', 'D')->count(),
            'F' => $grades->where('grade_letter', 'F')->count(),
            'total' => $grades->count(),
            'average' => $grades->avg('grade') ? round($grades->avg('grade'), 2) : 0,
        ];

        return $this->success(
            $distribution,
            'Grade distribution retrieved successfully'
        );
    }

    /**
     * Get grade analytics by faculty (admin only).
     */
    public function analyticsByFaculty(): JsonResponse
    {
        $grades = Grade::with(['course.faculty', 'user'])->get();

        $analytics = $grades->groupBy('course.faculty.name')->map(function ($facultyGrades) {
            return [
                'total_grades' => $facultyGrades->count(),
                'average' => round($facultyGrades->avg('grade'), 2),
                'distribution' => [
                    'A' => $facultyGrades->where('grade_letter', 'A')->count(),
                    'B' => $facultyGrades->where('grade_letter', 'B')->count(),
                    'C' => $facultyGrades->where('grade_letter', 'C')->count(),
                    'D' => $facultyGrades->where('grade_letter', 'D')->count(),
                    'F' => $facultyGrades->where('grade_letter', 'F')->count(),
                ],
            ];
        });

        return $this->success(
            $analytics,
            'Faculty grade analytics retrieved successfully'
        );
    }

    /**
     * Get grade analytics by course (admin/faculty only).
     */
    public function analyticsByCourse(): JsonResponse
    {
        $grades = Grade::with(['course', 'user'])->get();

        $analytics = $grades->groupBy('course.code')->map(function ($courseGrades, $courseCode) {
            $course = $courseGrades->first()->course;
            return [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'total_grades' => $courseGrades->count(),
                'average' => round($courseGrades->avg('grade'), 2),
                'distribution' => [
                    'A' => $courseGrades->where('grade_letter', 'A')->count(),
                    'B' => $courseGrades->where('grade_letter', 'B')->count(),
                    'C' => $courseGrades->where('grade_letter', 'C')->count(),
                    'D' => $courseGrades->where('grade_letter', 'D')->count(),
                    'F' => $courseGrades->where('grade_letter', 'F')->count(),
                ],
            ];
        });

        return $this->success(
            $analytics,
            'Course grade analytics retrieved successfully'
        );
    }
}