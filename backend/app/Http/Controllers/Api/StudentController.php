<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentCourseResource;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\AssignmentSubmission;
use Illuminate\Http\JsonResponse;

class StudentController extends ApiController
{
    /**
     * Get courses for the current student.
     * Returns courses with enrollment data including progress and grades.
     */
    public function myCourses(): JsonResponse
    {
        $user = auth()->user();
        
        // Get all enrollments for this student (both active and completed)
        $enrollments = CourseEnrollment::where('student_id', $user->id)
            ->with(['course' => function ($query) {
                $query->with(['faculty', 'major', 'instructor', 'modules']);
            }])
            ->get();
        
        // Transform courses with enrollment data
        $courses = $enrollments->map(function ($enrollment) {
            $course = $enrollment->course;
            if (!$course) return null;
            
            return new StudentCourseResource($course, $enrollment, $enrollment->student_id);
        })->filter()->values();
        
        return $this->success($courses);
    }
    
    /**
     * Get all courses with student-specific enrollment data.
     * Used for dashboard display where we need progress/grades per course.
     */
    public function allCoursesWithProgress(): JsonResponse
    {
        $user = auth()->user();
        
        // Get all enrollments for this student
        $enrollments = CourseEnrollment::where('student_id', $user->id)
            ->with(['course' => function ($query) {
                $query->with(['faculty', 'major', 'instructor', 'modules']);
            }])
            ->get()
            ->keyBy('course_id');
        
        // Get all courses (not just enrolled) for catalog view
        $courses = Course::with(['faculty', 'major', 'instructor', 'modules'])->get();
        
        // Transform courses with enrollment data where available
        $coursesWithProgress = $courses->map(function ($course) use ($enrollments, $user) {
            $enrollment = $enrollments->get($course->id);
            return new StudentCourseResource($course, $enrollment, $user->id);
        });
        
        return $this->success($coursesWithProgress);
    }

    /**
     * Get assignments for the current student.
     */
    public function myAssignments(): JsonResponse
    {
        $user = auth()->user();

        // Get courses the student is enrolled in
        $courseIds = $user->enrolledCourses()
            ->wherePivot('status', 'enrolled')
            ->pluck('courses.id');

        // Get assignments for those courses
        $assignments = Assignment::whereIn('course_id', $courseIds)
            ->published()
            ->with(['course', 'module'])
            ->ordered()
            ->get();

        // Add submission status for each assignment
        $assignments->each(function ($assignment) use ($user) {
            $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->where('student_id', $user->id)
                ->latest('submitted_at')
                ->first();

            $assignment->submission_status = $submission ? $submission->status : 'not_submitted';
            $assignment->my_grade = $submission ? $submission->grade : null;
        });

        return $this->success($assignments);
    }

    /**
     * Get grades for the current student.
     */
    public function myGrades(): JsonResponse
    {
        $user = auth()->user();
        $grades = Grade::where('user_id', $user->id)
            ->with(['course', 'assignment'])
            ->get();

        $gpa = $grades->isNotEmpty()
            ? $grades->map(fn($g) => $g->getGradeLetter() === 'A' ? 4.0
                : ($g->getGradeLetter() === 'B' ? 3.0
                    : ($g->getGradeLetter() === 'C' ? 2.0
                        : ($g->getGradeLetter() === 'D' ? 1.0 : 0.0))))
                ->avg()
            : 0.0;

        return $this->success([
            'grades' => $grades,
            'gpa' => round($gpa, 2),
            'total_courses' => $grades->pluck('course_id')->unique()->count(),
        ]);
    }
}
