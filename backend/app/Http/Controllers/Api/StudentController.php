<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\AssignmentSubmission;
use Illuminate\Http\JsonResponse;

class StudentController extends ApiController
{
    /**
     * Get courses for the current student.
     */
    public function myCourses(): JsonResponse
    {
        $user = auth()->user();
        $courses = $user->enrolledCourses()
            ->wherePivot('status', 'enrolled')
            ->with(['faculty', 'major', 'instructor'])
            ->get();
        return $this->success($courses);
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