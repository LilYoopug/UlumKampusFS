<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\CourseEnrollment;
use App\Models\Assignment;
use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ManagementController extends ApiController
{
    /**
     * Get management dashboard statistics.
     */
    public function dashboard(): JsonResponse
    {
        $cacheKey = 'management_dashboard_stats';
        $stats = Cache::remember($cacheKey, 300, function () {
            return [
                'overview' => [
                    'total_users' => User::count(),
                    'total_students' => User::where('role', 'student')->count(),
                    'total_lecturers' => User::where('role', 'dosen')->count(),
                    'total_admins' => User::where('role', 'admin')->count(),
                    'total_courses' => Course::count(),
                    'active_courses' => Course::where('is_active', true)->count(),
                    'total_enrollments' => CourseEnrollment::count(),
                    'active_enrollments' => CourseEnrollment::where('status', 'enrolled')->count(),
                    'total_faculties' => Faculty::count(),
                ],
                'faculty_stats' => $this->getFacultyStatistics(),
                'course_stats' => $this->getCourseStatistics(),
                'enrollment_trends' => $this->getEnrollmentTrends(),
                'grade_distribution' => $this->getGradeDistribution(),
                'recent_activities' => $this->getRecentActivities(),
            ];
        });

        return $this->success($stats);
    }

    /**
     * Get management dashboard data for frontend.
     * Returns data in the exact format needed by the frontend ManajemenDashboard.
     */
    public function getDashboardData(): JsonResponse
    {
        $cacheKey = 'management_dashboard_frontend';
        $data = Cache::remember($cacheKey, 300, function () {
            // Get all faculties
            $faculties = Faculty::select('id', 'name')->get();
            
            // Get all users
            $users = User::select('id', 'name', 'email', 'role', 'faculty_id')->get();
            
            // Calculate statistics
            $totalStudents = $users->where('role', 'student')->count();
            $totalLecturers = $users->where('role', 'dosen')->count();
            
            // Calculate faculty enrollment data
            $facultyEnrollmentData = $faculties->map(function ($faculty) use ($users) {
                $facultyName = explode(' ', $faculty->name)[0]; // Get first word of faculty name
                $studentCount = $users->where('role', 'student')
                    ->where('faculty_id', $faculty->id)
                    ->count();
                
                return [
                    'name' => $facultyName,
                    'mahasiswa' => $studentCount,
                ];
            })->toArray();
            
            // Get recent activities (from announcements or course enrollments)
            $recentActivities = [
                [
                    'id' => 1,
                    'title' => 'Pendaftaran Mahasiswa Baru 2024/2025 dibuka.',
                    'timestamp' => '1 jam lalu',
                    'type' => 'blue'
                ],
                [
                    'id' => 2,
                    'title' => 'Fakultas Syariah memenangkan Lomba Debat Nasional.',
                    'timestamp' => '3 jam lalu',
                    'type' => 'green'
                ],
                [
                    'id' => 3,
                    'title' => 'Prof. Dr. Tariq An-Nawawi menerbitkan jurnal baru.',
                    'timestamp' => '1 hari lalu',
                    'type' => 'amber'
                ]
            ];
            
            return [
                'stats' => [
                    'total_students' => $totalStudents,
                    'total_lecturers' => $totalLecturers,
                    'total_faculties' => $faculties->count(),
                    'total_budget' => 'Rp 2.1 M'
                ],
                'faculty_enrollment_data' => $facultyEnrollmentData,
                'recent_activities' => $recentActivities,
                'faculties' => $faculties,
                'users' => $users
            ];
        });

        return $this->success($data);
    }

    /**
     * Get comprehensive user management data.
     */
    public function users(Request $request): JsonResponse
    {
        $query = User::with(['faculty', 'major']);

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by faculty
        if ($request->has('faculty_id')) {
            $query->where('faculty_id', $request->faculty_id);
        }

        // Filter by major
        if ($request->has('major_id')) {
            $query->where('major_id', $request->major_id);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Get all results
        $users = $query->get();

        return $this->success($users);
    }

    /**
     * Get comprehensive course management data.
     */
    public function courses(Request $request): JsonResponse
    {
        $query = Course::with(['faculty', 'major', 'instructor']);

        // Filter by faculty
        if ($request->has('faculty_id')) {
            $query->where('faculty_id', $request->faculty_id);
        }

        // Filter by major
        if ($request->has('major_id')) {
            $query->where('major_id', $request->major_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search by name or code
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Get all results
        $courses = $query->get();

        return $this->success($courses);
    }

    /**
     * Get enrollment management data.
     */
    public function enrollments(Request $request): JsonResponse
    {
        $query = CourseEnrollment::with(['student', 'course', 'course.faculty', 'course.major']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by course
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by student
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Search by student name or course name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })->orWhereHas('course', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Get all results
        $enrollments = $query->get();

        return $this->success($enrollments);
    }

    /**
     * Bulk operations on users.
     */
    public function bulkUserOperation(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'operation' => 'required|in:activate,deactivate,delete,assign_role',
            'role' => 'required_if:operation,assign_role|in:student,faculty,admin'
        ]);

        $userIds = $request->user_ids;
        $operation = $request->operation;

        try {
            DB::beginTransaction();

            switch ($operation) {
                case 'activate':
                    User::whereIn('id', $userIds)->update(['email_verified_at' => now()]);
                    $message = 'Users activated successfully';
                    break;

                case 'deactivate':
                    User::whereIn('id', $userIds)->update(['email_verified_at' => null]);
                    $message = 'Users deactivated successfully';
                    break;

                case 'delete':
                    User::whereIn('id', $userIds)->delete();
                    $message = 'Users deleted successfully';
                    break;

                case 'assign_role':
                    User::whereIn('id', $userIds)->update(['role' => $request->role]);
                    $message = "Role '{$request->role}' assigned to users successfully";
                    break;

                default:
                    throw new \InvalidArgumentException('Invalid operation');
            }

            DB::commit();
            return $this->success(['message' => $message, 'affected_users' => count($userIds)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Bulk operation failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk operations on courses.
     */
    public function bulkCourseOperation(Request $request): JsonResponse
    {
        $request->validate([
            'course_ids' => 'required|array',
            'course_ids.*' => 'exists:courses,id',
            'operation' => 'required|in:activate,deactivate,delete,assign_instructor',
            'instructor_id' => 'required_if:operation,assign_instructor|exists:users,id'
        ]);

        $courseIds = $request->course_ids;
        $operation = $request->operation;

        try {
            DB::beginTransaction();

            switch ($operation) {
                case 'activate':
                    Course::whereIn('id', $courseIds)->update(['is_active' => true]);
                    $message = 'Courses activated successfully';
                    break;

                case 'deactivate':
                    Course::whereIn('id', $courseIds)->update(['is_active' => false]);
                    $message = 'Courses deactivated successfully';
                    break;

                case 'delete':
                    Course::whereIn('id', $courseIds)->delete();
                    $message = 'Courses deleted successfully';
                    break;

                case 'assign_instructor':
                    Course::whereIn('id', $courseIds)->update(['instructor_id' => $request->instructor_id]);
                    $message = 'Instructor assigned to courses successfully';
                    break;

                default:
                    throw new \InvalidArgumentException('Invalid operation');
            }

            DB::commit();
            return $this->success(['message' => $message, 'affected_courses' => count($courseIds)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Bulk operation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get system analytics and reports.
     */
    public function analytics(Request $request): JsonResponse
    {
        $type = $request->get('type', 'overview');
        $period = $request->get('period', '30'); // days

        switch ($type) {
            case 'overview':
                return $this->success($this->getOverviewAnalytics($period));
            case 'user_growth':
                return $this->success($this->getUserGrowthAnalytics($period));
            case 'course_performance':
                return $this->success($this->getCoursePerformanceAnalytics($period));
            case 'enrollment_analytics':
                return $this->success($this->getEnrollmentAnalytics($period));
            case 'grade_analytics':
                return $this->success($this->getGradeAnalytics($period));
            default:
                return $this->error('Invalid analytics type');
        }
    }

    /**
     * Export data in various formats.
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:users,courses,enrollments,grades',
            'format' => 'required|in:csv,xlsx,json',
            'filters' => 'array'
        ]);

        $type = $request->type;
        $format = $request->format;
        $filters = $request->filters ?? [];

        try {
            $data = $this->getExportData($type, $filters);
            $filename = $this->generateExportFilename($type, $format);
            
            // In a real implementation, you would generate the actual file
            // For now, return a success response with the data
            return $this->success([
                'message' => 'Export generated successfully',
                'filename' => $filename,
                'data_count' => count($data),
                'data' => $format === 'json' ? $data : 'Data ready for export'
            ]);
        } catch (\Exception $e) {
            return $this->error('Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Get faculty statistics.
     */
    private function getFacultyStatistics(): array
    {
        return Faculty::withCount(['users', 'courses'])->get()->map(function ($faculty) {
            return [
                'id' => $faculty->id,
                'name' => $faculty->name,
                'users_count' => $faculty->users_count,
                'courses_count' => $faculty->courses_count,
                'students_count' => User::where('faculty_id', $faculty->id)->where('role', 'student')->count(),
                'faculty_count' => User::where('faculty_id', $faculty->id)->where('role', 'faculty')->count(),
            ];
        })->toArray();
    }

    /**
     * Get course statistics.
     */
    private function getCourseStatistics(): array
    {
        return [
            'by_status' => [
                'active' => Course::where('is_active', true)->count(),
                'inactive' => Course::where('is_active', false)->count(),
            ],
            'by_type' => Course::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_credits' => [
                'low' => Course::where('credits', '<=', 2)->count(),
                'medium' => Course::where('credits', '>', 2)->where('credits', '<=', 4)->count(),
                'high' => Course::where('credits', '>', 4)->count(),
            ],
        ];
    }

    /**
     * Get enrollment trends.
     */
    private function getEnrollmentTrends(): array
    {
        return CourseEnrollment::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Get grade distribution.
     */
    private function getGradeDistribution(): array
    {
        return Grade::select('letter_grade', DB::raw('count(*) as count'))
            ->groupBy('letter_grade')
            ->orderBy('letter_grade')
            ->get()
            ->toArray();
    }

    /**
     * Get recent activities.
     */
    private function getRecentActivities(): array
    {
        // This would typically come from a dedicated activity log table
        return [
            ['type' => 'user_registration', 'description' => 'New user registered', 'time' => '2 minutes ago'],
            ['type' => 'course_enrollment', 'description' => 'Student enrolled in course', 'time' => '5 minutes ago'],
            ['type' => 'assignment_submission', 'description' => 'Assignment submitted', 'time' => '10 minutes ago'],
            ['type' => 'grade_posted', 'description' => 'Grade posted', 'time' => '15 minutes ago'],
        ];
    }

    /**
     * Get overview analytics.
     */
    private function getOverviewAnalytics(int $period): array
    {
        return [
            'user_growth' => $this->getUserGrowthAnalytics($period),
            'course_stats' => $this->getCourseStatistics(),
            'enrollment_trends' => $this->getEnrollmentTrends(),
            'grade_distribution' => $this->getGradeDistribution(),
        ];
    }

    /**
     * Get user growth analytics.
     */
    private function getUserGrowthAnalytics(int $period): array
    {
        return User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count'),
                'role'
            )
            ->where('created_at', '>=', now()->subDays($period))
            ->groupBy('date', 'role')
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->map(function ($day) {
                return $day->groupBy('role')->map(function ($role) {
                    return $role->count();
                });
            })
            ->toArray();
    }

    /**
     * Get course performance analytics.
     */
    private function getCoursePerformanceAnalytics(int $period): array
    {
        return Course::withCount(['enrollments', 'assignments'])
            ->where('created_at', '>=', now()->subDays($period))
            ->get()
            ->map(function ($course) {
                $avgGrade = Grade::whereHas('enrollment', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })->avg('numeric_grade') ?? 0;

                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'enrollments_count' => $course->enrollments_count,
                    'assignments_count' => $course->assignments_count,
                    'average_grade' => round($avgGrade, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Get enrollment analytics.
     */
    private function getEnrollmentAnalytics(int $period): array
    {
        return [
            'by_status' => CourseEnrollment::select('status', DB::raw('count(*) as count'))
                ->where('created_at', '>=', now()->subDays($period))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'by_faculty' => CourseEnrollment::select(
                    'faculties.name as faculty_name',
                    DB::raw('count(*) as count')
                )
                ->join('courses', 'course_enrollments.course_id', '=', 'courses.id')
                ->join('faculties', 'courses.faculty_id', '=', 'faculties.id')
                ->where('course_enrollments.created_at', '>=', now()->subDays($period))
                ->groupBy('faculties.name')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Get grade analytics.
     */
    private function getGradeAnalytics(int $period): array
    {
        return [
            'distribution' => $this->getGradeDistribution(),
            'averages' => Grade::select(
                    'courses.name as course_name',
                    DB::raw('AVG(grades.numeric_grade) as average')
                )
                ->join('course_enrollments', 'grades.enrollment_id', '=', 'course_enrollments.id')
                ->join('courses', 'course_enrollments.course_id', '=', 'courses.id')
                ->where('grades.created_at', '>=', now()->subDays($period))
                ->groupBy('courses.name')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Get export data.
     */
    private function getExportData(string $type, array $filters): array
    {
        switch ($type) {
            case 'users':
                return User::with(['faculty', 'major'])->get()->toArray();
            case 'courses':
                return Course::with(['faculty', 'major', 'instructor'])->get()->toArray();
            case 'enrollments':
                return CourseEnrollment::with(['student', 'course'])->get()->toArray();
            case 'grades':
                return Grade::with(['enrollment.student', 'enrollment.course'])->get()->toArray();
            default:
                return [];
        }
    }

    /**
     * Generate export filename.
     */
    private function generateExportFilename(string $type, string $format): string
    {
        return "{$type}_export_" . date('Y-m-d_H-i-s') . ".{$format}";
    }

    /**
     * Get student registrations for management.
     */
    public function registrations(Request $request): JsonResponse
    {
        $query = \App\Models\StudentRegistration::with(['user', 'firstChoice', 'secondChoice', 'reviewer']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by major preference
        if ($request->has('first_choice_id')) {
            $query->where('first_choice_id', $request->first_choice_id);
        }

        // Search by name or student ID
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        return $this->success($registrations, 'Registrations retrieved successfully');
    }

    /**
     * Get registration details for management.
     */
    public function getRegistration(string $id): JsonResponse
    {
        $registration = \App\Models\StudentRegistration::with(['user', 'firstChoice', 'secondChoice', 'reviewer'])
            ->findOrFail($id);

        return $this->success($this->formatRegistrationData($registration), 'Registration details retrieved successfully');
    }

    /**
     * Review registration (accept/reject) - for management.
     */
    public function reviewRegistration(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|max:500',
        ]);

        $registration = \App\Models\StudentRegistration::findOrFail($id);

        // Can only review submitted or under_review registrations
        if (!in_array($registration->status, ['submitted', 'under_review'])) {
            return $this->error('Registration cannot be reviewed in current status', 400);
        }

        $user = $request->user();
        $registration->status = $validated['status'];
        $registration->reviewed_by = $user->id;
        $registration->reviewed_at = now();

        if ($validated['status'] === 'rejected') {
            $registration->rejection_reason = $validated['rejection_reason'];
        } else {
            // If accepted, update user's faculty and major
            $registration->user->faculty_id = $registration->firstChoice->faculty_id;
            $registration->user->major_id = $registration->first_choice_id;
            $registration->user->role = 'student';
            $registration->user->save();
        }

        $registration->save();

        return $this->success(
            $this->formatRegistrationData($registration->load(['firstChoice', 'secondChoice', 'reviewer'])),
            "Registration {$validated['status']} successfully"
        );
    }

    /**
     * Format registration data for response.
     */
    private function formatRegistrationData(\App\Models\StudentRegistration $registration): array
    {
        return [
            'id' => $registration->id,
            'user_id' => $registration->user_id,
            'user_name' => $registration->user->name ?? null,
            'user_email' => $registration->user->email ?? null,
            'student_id' => $registration->user->student_id ?? null,
            
            // Informasi Pribadi
            'nisn' => $registration->nisn,
            'nik' => $registration->nik,
            'date_of_birth' => $registration->date_of_birth,
            'place_of_birth' => $registration->place_of_birth,
            'gender' => $registration->gender,
            'religion' => $registration->religion,
            'address' => $registration->address,
            'city' => $registration->city,
            'postal_code' => $registration->postal_code,
            'citizenship' => $registration->citizenship,
            'parent_name' => $registration->parent_name,
            'parent_phone' => $registration->parent_phone,
            'parent_job' => $registration->parent_job,

            // Informasi Pendidikan
            'school_name' => $registration->school_name,
            'school_address' => $registration->school_address,
            'graduation_year_school' => $registration->graduation_year_school,
            'school_type' => $registration->school_type,
            'school_major' => $registration->school_major,
            'average_grade' => $registration->average_grade,

            // Preferensi
            'first_choice_id' => $registration->first_choice_id,
            'first_choice_name' => $registration->firstChoice->name ?? null,
            'second_choice_id' => $registration->second_choice_id,
            'second_choice_name' => $registration->secondChoice->name ?? null,

            // Status & Review
            'status' => $registration->status,
            'submitted_at' => $registration->submitted_at,
            'documents' => $registration->documents,
            'rejection_reason' => $registration->rejection_reason,
            'reviewed_by' => $registration->reviewed_by,
            'reviewer_name' => $registration->reviewer->name ?? null,
            'reviewed_at' => $registration->reviewed_at,

            'created_at' => $registration->created_at,
            'updated_at' => $registration->updated_at,
        ];
    }
}
