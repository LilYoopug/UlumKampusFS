<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\JsonResponse;

class AdminController extends ApiController
{
    /**
     * Get admin dashboard statistics.
     */
    public function stats(): JsonResponse
    {
        return $this->success([
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_faculty' => User::where('role', 'faculty')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_courses' => Course::count(),
            'active_courses' => Course::where('is_active', true)->count(),
            'total_enrollments' => CourseEnrollment::count(),
            'active_enrollments' => CourseEnrollment::where('status', 'enrolled')->count(),
        ]);
    }

    /**
     * Get all users (admin only).
     */
    public function users(): JsonResponse
    {
        $users = User::with(['faculty', 'major'])->get();
        return $this->success($users);
    }
}