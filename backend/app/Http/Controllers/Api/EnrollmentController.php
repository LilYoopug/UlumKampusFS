<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentResource;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EnrollmentController extends ApiController
{
    /**
     * Display a listing of enrollments for the current student.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $enrollments = CourseEnrollment::where('student_id', $user->id)
            ->with(['course.faculty', 'course.major', 'course.instructor'])
            ->get();
        return $this->success(
            EnrollmentResource::collection($enrollments),
            'Enrollments retrieved successfully'
        );
    }

    /**
     * Display the specified enrollment.
     */
    public function show(string $id): JsonResponse
    {
        $user = auth()->user();
        $enrollment = CourseEnrollment::where('id', $id)
            ->where('student_id', $user->id)
            ->with('course')
            ->firstOrFail();
        return $this->success(
            new EnrollmentResource($enrollment),
            'Enrollment retrieved successfully'
        );
    }

    /**
     * Get enrollments for a specific course (admin/faculty only).
     */
    public function byCourse(string $courseId): JsonResponse
    {
        $enrollments = CourseEnrollment::where('course_id', $courseId)
            ->with('student')
            ->get();
        return $this->success(
            EnrollmentResource::collection($enrollments),
            'Course enrollments retrieved successfully'
        );
    }

    /**
     * Approve a pending enrollment.
     */
    public function approve(string $id): JsonResponse
    {
        $enrollment = CourseEnrollment::findOrFail($id);
        $enrollment->update([
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        $course = $enrollment->course;
        $course->increment('current_enrollment');

        return $this->success(
            new EnrollmentResource($enrollment->load('course', 'student')),
            'Enrollment approved successfully'
        );
    }

    /**
     * Reject a pending enrollment.
     */
    public function reject(string $id): JsonResponse
    {
        $enrollment = CourseEnrollment::findOrFail($id);
        $enrollment->update([
            'status' => 'rejected',
        ]);
        return $this->success(
            new EnrollmentResource($enrollment->load('course', 'student')),
            'Enrollment rejected'
        );
    }

    /**
     * Remove the specified enrollment.
     */
    public function destroy(string $id): JsonResponse
    {
        $enrollment = CourseEnrollment::findOrFail($id);

        if ($enrollment->status === 'enrolled') {
            $course = $enrollment->course;
            $course->decrement('current_enrollment');
        }

        $enrollment->delete();
        return $this->noContent();
    }
}