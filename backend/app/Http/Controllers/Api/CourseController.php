<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CourseRequest;
use App\Http\Resources\CourseModuleResource;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Course Management API Controller
 *
 * Handles CRUD operations for courses including:
 * - Course listing with filtering, search, and pagination
 * - Course creation, retrieval, update, and deletion
 * - Course enrollment operations
 * - Related data retrieval (modules, enrollments, assignments, etc.)
 */
class CourseController extends ApiController
{
    /**
     * Display a listing of courses with optional filtering and search.
     *
     * @queryParam faculty_id Filter by faculty ID
     * @queryParam major_id Filter by major ID
     * @queryParam instructor_id Filter by instructor ID
     * @queryParam semester Filter by semester (Fall, Spring, Summer)
     * @queryParam year Filter by year
     * @queryParam is_active Filter by active status (true/false)
     * @queryParam search Search by course code or name
     * @queryParam per_page Items per page (default: 15)
     * @queryParam page Page number (default: 1)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Course::query();

        // Filter by faculty
        if ($request->has('faculty_id')) {
            $query->where('faculty_id', $request->input('faculty_id'));
        }

        // Filter by major
        if ($request->has('major_id')) {
            $query->where('major_id', $request->input('major_id'));
        }

        // Filter by instructor
        if ($request->has('instructor_id')) {
            $query->where('instructor_id', $request->input('instructor_id'));
        }

        // Filter by semester
        if ($request->has('semester')) {
            $query->where('semester', $request->input('semester'));
        }

        // Filter by year
        if ($request->has('year')) {
            $query->where('year', $request->input('year'));
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        // Search by course code or name
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'like', "%{$searchTerm}%")
                    ->orWhere('name', 'like', "%{$searchTerm}%");
            });
        }

        // Eager load relationships
        $query->with(['faculty', 'major', 'instructor']);

        // Apply ordering
        $query->orderBy('code');

        // Paginate results
        $perPage = $request->input('per_page', 15);
        $courses = $query->paginate($perPage);

        return $this->paginated(
            CourseResource::collection($courses),
            'Courses retrieved successfully'
        );
    }

    /**
     * Store a newly created course in storage.
     *
     * Requires admin or faculty role (enforced by CourseRequest).
     */
    public function store(CourseRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Set default values
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['current_enrollment'] = $validated['current_enrollment'] ?? 0;

        $course = Course::create($validated);

        return $this->created(
            new CourseResource($course->load(['faculty', 'major', 'instructor'])),
            'Course created successfully'
        );
    }

    /**
     * Display the specified course.
     */
    public function show(string $id): JsonResponse
    {
        $course = Course::with(['faculty', 'major', 'instructor', 'modules'])
            ->findOrFail($id);

        return $this->success(
            new CourseResource($course),
            'Course retrieved successfully'
        );
    }

    /**
     * Update the specified course in storage.
     *
     * Requires admin or faculty role (enforced by CourseRequest).
     */
    public function update(CourseRequest $request, string $id): JsonResponse
    {
        $course = Course::findOrFail($id);

        $validated = $request->validated();

        $course->update($validated);

        return $this->success(
            new CourseResource($course->load(['faculty', 'major', 'instructor'])),
            'Course updated successfully'
        );
    }

    /**
     * Remove the specified course from storage.
     *
     * Requires admin or faculty role (enforced by route middleware).
     */
    public function destroy(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);

        // Prevent deletion if there are active enrollments
        $activeEnrollments = $course->enrollments()->where('status', 'enrolled')->count();
        if ($activeEnrollments > 0) {
            return $this->error(
                'Cannot delete course with active enrollments. Please drop all students first.',
                409
            );
        }

        $course->delete();

        return $this->noContent();
    }

    /**
     * Get modules for this course.
     */
    public function modules(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $modules = $course->modules()->orderBy('order')->get();

        return $this->success(
            CourseModuleResource::collection($modules),
            'Course modules retrieved successfully'
        );
    }

    /**
     * Get enrollments for this course.
     *
     * Requires admin or faculty role.
     */
    public function enrollments(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $enrollments = $course->enrollments()->with('student')->get();

        return $this->success(
            $enrollments,
            'Course enrollments retrieved successfully'
        );
    }

    /**
     * Get students enrolled in this course.
     */
    public function students(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $students = $course->students()->wherePivot('status', 'enrolled')->get();

        return $this->success(
            $students,
            'Enrolled students retrieved successfully'
        );
    }

    /**
     * Get assignments for this course.
     */
    public function assignments(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $assignments = $course->assignments()->orderBy('due_date')->get();

        return $this->success(
            $assignments,
            'Course assignments retrieved successfully'
        );
    }

    /**
     * Get assignments with submission statistics for this course.
     *
     * Returns assignments with:
     * - Total enrolled students count
     * - Submitted count
     * - Graded count
     * - Pending grading count
     *
     * Useful for Gradebook's "Daftar Tugas" tab.
     */
    public function assignmentsWithStats(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        
        // Get all assignments for the course
        $assignments = $course->assignments()
            ->orderBy('due_date')
            ->get();
        
        // Get total enrolled students for the course (all enrollments, not just 'enrolled' status)
        // This matches the studentProgress endpoint which shows all students
        $totalStudents = $course->enrollments()->count();
        
        // Build assignments with statistics
        $assignmentsWithStats = [];
        
        foreach ($assignments as $assignment) {
            // Get all submissions for this assignment
            $submissions = $assignment->submissions;
            $submittedCount = $submissions->count();
            
            // Count graded submissions (those with a grade)
            $gradedCount = 0;
            foreach ($submissions as $submission) {
                if ($submission->grade !== null) {
                    $gradedCount++;
                }
            }
            
            $assignmentsWithStats[] = [
                'id' => $assignment->id,
                'course_id' => $assignment->course_id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'due_date' => $assignment->due_date ? $assignment->due_date->toIso8601String() : null,
                'submission_type' => $assignment->submission_type,
                'category' => $assignment->category,
                'max_points' => $assignment->max_points,
                'instructions' => $assignment->instructions,
                'is_published' => $assignment->is_published,
                'created_at' => $assignment->created_at ? $assignment->created_at->toIso8601String() : null,
                'updated_at' => $assignment->updated_at ? $assignment->updated_at->toIso8601String() : null,
                'statistics' => [
                    'total_students' => $totalStudents,
                    'submitted_count' => $submittedCount,
                    'graded_count' => $gradedCount,
                    'pending_grading_count' => $submittedCount - $gradedCount,
                    'not_submitted_count' => $totalStudents - $submittedCount,
                    'submission_rate' => $totalStudents > 0 ? round(($submittedCount / $totalStudents) * 100, 1) : 0,
                ],
            ];
        }
        
        return $this->success(
            $assignmentsWithStats,
            'Course assignments with statistics retrieved successfully'
        );
    }

    /**
     * Get announcements for this course.
     */
    public function announcements(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $announcements = $course->announcements()
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(
            $announcements,
            'Course announcements retrieved successfully'
        );
    }

    /**
     * Get library resources for this course.
     */
    public function libraryResources(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $resources = $course->libraryResources()
            ->where('is_published', true)
            ->orderBy('order')
            ->get();

        return $this->success(
            $resources,
            'Course library resources retrieved successfully'
        );
    }

    /**
     * Get discussion threads for this course.
     */
    public function discussionThreads(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $threads = $course->discussionThreads()
            ->where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(
            $threads,
            'Course discussion threads retrieved successfully'
        );
    }

    /**
     * Get grades for this course.
     *
     * Requires admin or faculty role.
     */
    public function grades(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $grades = $course->grades()->with('user')->get();

        return $this->success(
            $grades,
            'Course grades retrieved successfully'
        );
    }

    /**
     * Get student progress for this course.
     *
     * Returns comprehensive progress data including:
     * - Student information
     * - Enrollment details
     * - Grade summary
     * - Completion status
     *
     * Requires admin or faculty role.
     */
    public function studentProgress(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        
        // Get all enrollments for the course
        $enrollments = $course->enrollments()
            ->with('student')
            ->get();
        
        // Get all assignments for the course to calculate progress
        $assignments = $course->assignments()->get();
        $totalAssignments = $assignments->count();
        
        // Build student progress data
        $studentProgressData = [];
        
        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            
            if (!$student) {
                continue;
            }
            
            // Get grades for this student in this course
            $studentGrades = $course->grades()
                ->where('user_id', $student->id)
                ->get();
            
            // Calculate grade summary
            $averageGrade = null;
            $gradedAssignments = 0;
            $totalGrade = 0;
            
            foreach ($studentGrades as $grade) {
                if ($grade->grade !== null) {
                    $totalGrade += $grade->grade;
                    $gradedAssignments++;
                }
            }
            
            if ($gradedAssignments > 0) {
                $averageGrade = $totalGrade / $gradedAssignments;
            }
            
            // Calculate progress based on enrollment progress or completion
            $progress = $enrollment->progress_percentage ?? 0;
            
            // If no progress is set, calculate based on graded assignments
            if ($progress === 0 && $totalAssignments > 0) {
                $progress = ($gradedAssignments / $totalAssignments) * 100;
            }
            
            // Determine completion status
            $completionStatus = 'In Progress';
            if ($enrollment->status === 'completed' || $progress >= 100) {
                $completionStatus = 'Completed';
            } elseif ($enrollment->status === 'dropped') {
                $completionStatus = 'Dropped';
            }
            
            $studentProgressData[] = [
                'student' => [
                    'id' => $student->student_id ?? $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                ],
                'enrollment' => [
                    'id' => $enrollment->id,
                    'status' => $enrollment->status,
                    'progress' => (int) $progress,
                    'enrolled_at' => $enrollment->enrolled_at ? $enrollment->enrolled_at->toIso8601String() : null,
                    'completed_at' => $enrollment->completed_at ? $enrollment->completed_at->toIso8601String() : null,
                ],
                'grade_summary' => [
                    'average_grade' => $averageGrade,
                    'total_assignments' => $totalAssignments,
                    'graded_assignments' => $gradedAssignments,
                    'pending_grades' => $totalAssignments - $gradedAssignments,
                ],
                'completion_status' => $completionStatus,
            ];
        }
        
        return $this->success(
            $studentProgressData,
            'Student progress retrieved successfully'
        );
    }

    /**
     * Enroll the current student in this course.
     *
     * Requires student role (enforced by route middleware).
     */
    public function enroll(string $id): JsonResponse
    {
        $user = auth()->user();
        $course = Course::findOrFail($id);

        // Check if course is active
        if (!$course->is_active) {
            return $this->error(
                'Cannot enroll in an inactive course',
                400
            );
        }

        // Check if course has capacity
        if (!$course->hasCapacity()) {
            return $this->error(
                'Course is at full capacity',
                409
            );
        }

        // Check for existing enrollment
        $existingEnrollment = CourseEnrollment::where('course_id', $id)
            ->where('student_id', $user->id)
            ->first();

        if ($existingEnrollment) {
            if ($existingEnrollment->status === 'enrolled') {
                return $this->error(
                    'Already enrolled in this course',
                    409
                );
            } elseif ($existingEnrollment->status === 'dropped') {
                // Re-enroll a dropped student
                $existingEnrollment->update([
                    'status' => 'enrolled',
                    'enrolled_at' => now(),
                    'completed_at' => null,
                ]);
                $course->increment('current_enrollment');

                return $this->success(
                    $existingEnrollment,
                    'Successfully re-enrolled in course'
                );
            }
        }

        // Create new enrollment
        $enrollment = CourseEnrollment::create([
            'course_id' => $id,
            'student_id' => $user->id,
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        $course->increment('current_enrollment');

        return $this->created(
            $enrollment,
            'Successfully enrolled in course'
        );
    }

    /**
     * Drop the current student from this course.
     *
     * Requires student role (enforced by route middleware).
     */
    public function drop(string $id): JsonResponse
    {
        $user = auth()->user();
        $enrollment = CourseEnrollment::where('course_id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        if ($enrollment->status === 'dropped') {
            return $this->error(
                'Already dropped from this course',
                409
            );
        }

        $enrollment->update([
            'status' => 'dropped',
            'completed_at' => now(),
        ]);

        $course = Course::findOrFail($id);
        $course->decrement('current_enrollment');

        return $this->success(
            null,
            'Successfully dropped from course'
        );
    }

    /**
     * Get courses for the current instructor.
     *
     * Requires faculty role.
     */
    public function myCourses(): JsonResponse
    {
        $user = auth()->user();

        $courses = Course::where('instructor_id', $user->id)
            ->with(['faculty', 'major'])
            ->orderBy('code')
            ->get();

        return $this->success(
            CourseResource::collection($courses),
            'My courses retrieved successfully'
        );
    }

    /**
     * Toggle course active status.
     *
     * Requires admin or faculty role.
     */
    public function toggleStatus(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);

        $course->update([
            'is_active' => !$course->is_active,
        ]);

        return $this->success(
            new CourseResource($course->load(['faculty', 'major', 'instructor'])),
            'Course status updated successfully'
        );
    }

    /**
     * Get public course catalog (no auth required).
     * Returns active courses that are available for enrollment.
     */
    public function publicCourses(Request $request): JsonResponse
    {
        $query = Course::query()
            ->where('is_active', true)
            ->where('current_enrollment', '>=', 0);

        // Filter by faculty
        if ($request->has('faculty_id')) {
            $query->where('faculty_id', $request->input('faculty_id'));
        }

        // Filter by major
        if ($request->has('major_id')) {
            $query->where('major_id', $request->input('major_id'));
        }

        // Filter by semester
        if ($request->has('semester')) {
            $query->where('semester', $request->input('semester'));
        }

        // Filter by year
        if ($request->has('year')) {
            $query->where('year', $request->input('year'));
        }

        // Search by course code or name
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'like', "%{$searchTerm}%")
                    ->orWhere('name', 'like', "%{$searchTerm}%");
            });
        }

        // Eager load relationships
        $query->with(['faculty', 'major', 'instructor']);

        // Apply ordering and paginate
        $query->orderBy('code');
        $perPage = $request->input('per_page', 15);
        $courses = $query->paginate($perPage);

        return $this->paginated(
            CourseResource::collection($courses),
            'Public courses retrieved successfully'
        );
    }
}
