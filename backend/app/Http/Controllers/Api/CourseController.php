<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    /**
     * Display a listing of courses.
     */
    public function index(): JsonResponse
    {
        $courses = Course::active()
            ->with(['faculty', 'major', 'instructor'])
            ->get();
        return $this->success($courses);
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'major_id' => 'nullable|exists:majors,id',
            'instructor_id' => 'required|exists:users,id',
            'code' => 'required|string|max:50|unique:courses',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'credit_hours' => 'nullable|integer|min:1',
            'capacity' => 'nullable|integer|min:1',
            'semester' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:2000|max:2100',
            'schedule' => 'nullable|string',
            'room' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $course = Course::create($validated);
        return $this->created($course, 'Course created successfully');
    }

    /**
     * Display the specified course.
     */
    public function show(string $id): JsonResponse
    {
        $course = Course::with(['faculty', 'major', 'instructor', 'modules'])->findOrFail($id);
        return $this->success($course);
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'faculty_id' => 'sometimes|exists:faculties,id',
            'major_id' => 'nullable|exists:majors,id',
            'instructor_id' => 'sometimes|exists:users,id',
            'code' => 'sometimes|string|max:50|unique:courses,code,' . $id,
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'credit_hours' => 'nullable|integer|min:1',
            'capacity' => 'nullable|integer|min:1',
            'semester' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:2000|max:2100',
            'schedule' => 'nullable|string',
            'room' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $course->update($validated);
        return $this->success($course, 'Course updated successfully');
    }

    /**
     * Remove the specified course.
     */
    public function destroy(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $course->delete();
        return $this->noContent();
    }

    /**
     * Get modules for this course.
     */
    public function modules(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $modules = $course->modules()->ordered()->get();
        return $this->success($modules);
    }

    /**
     * Get enrollments for this course.
     */
    public function enrollments(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $enrollments = $course->enrollments()->with('student')->get();
        return $this->success($enrollments);
    }

    /**
     * Get students enrolled in this course.
     */
    public function students(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $students = $course->students()->get();
        return $this->success($students);
    }

    /**
     * Get assignments for this course.
     */
    public function assignments(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $assignments = $course->assignments()->ordered()->get();
        return $this->success($assignments);
    }

    /**
     * Get announcements for this course.
     */
    public function announcements(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $announcements = $course->announcements()->ordered()->published()->get();
        return $this->success($announcements);
    }

    /**
     * Get library resources for this course.
     */
    public function libraryResources(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $resources = $course->libraryResources()->ordered()->published()->get();
        return $this->success($resources);
    }

    /**
     * Get discussion threads for this course.
     */
    public function discussionThreads(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $threads = $course->discussionThreads()->recent()->get();
        return $this->success($threads);
    }

    /**
     * Get grades for this course.
     */
    public function grades(string $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $grades = $course->grades()->with('user')->get();
        return $this->success($grades);
    }

    /**
     * Enroll the current student in this course.
     */
    public function enroll(string $id): JsonResponse
    {
        $user = auth()->user();
        $course = Course::findOrFail($id);

        if (!$course->hasCapacity()) {
            return $this->error('Course is at full capacity', 409);
        }

        $existingEnrollment = CourseEnrollment::where('course_id', $id)
            ->where('student_id', $user->id)
            ->first();

        if ($existingEnrollment) {
            return $this->error('Already enrolled in this course', 409);
        }

        $enrollment = CourseEnrollment::create([
            'course_id' => $id,
            'student_id' => $user->id,
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        $course->increment('current_enrollment');

        return $this->created($enrollment, 'Successfully enrolled in course');
    }

    /**
     * Drop the current student from this course.
     */
    public function drop(string $id): JsonResponse
    {
        $user = auth()->user();
        $enrollment = CourseEnrollment::where('course_id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        $enrollment->update([
            'status' => 'dropped',
        ]);

        $course = Course::findOrFail($id);
        $course->decrement('current_enrollment');

        return $this->success(null, 'Successfully dropped from course');
    }
}