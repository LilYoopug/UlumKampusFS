<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardStatsResource;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\Major;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard Analytics Controller
 *
 * Provides role-specific dashboard statistics and analytics
 * for students, faculty (dosen), prodi, and management (admin).
 */
class DashboardController extends ApiController
{
    /**
     * Get dashboard stats for the authenticated user.
     * Routes to appropriate stats based on user role.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        return match ($user->role) {
            'student' => $this->studentStats(),
            'faculty' => $this->facultyStats(),
            'admin' => $this->managementStats(),
            default => $this->error('Invalid user role', 403),
        };
    }

    /**
     * Get student dashboard statistics.
     *
     * Returns:
     * - Total enrolled courses
     * - Total SKS (credit hours)
     * - Current GPA
     * - Pending assignments count
     * - Upcoming assignments
     * - Recent grades
     */
    public function studentStats(): JsonResponse
    {
        $user = auth()->user();

        // Get enrolled courses
        $enrolledCourses = $user->enrolledCourses()
            ->wherePivot('status', 'enrolled')
            ->get();

        $totalCourses = $enrolledCourses->count();
        $totalSKS = $enrolledCourses->sum('credit_hours');

        // Calculate GPA from grades
        $grades = Grade::where('user_id', $user->id)->get();
        $gpa = $this->calculateGPA($grades);

        // Get pending assignments count
        $courseIds = $enrolledCourses->pluck('id');
        $pendingAssignments = Assignment::whereIn('course_id', $courseIds)
            ->published()
            ->where('due_date', '>', now())
            ->whereDoesntHave('submissions', function ($query) use ($user) {
                $query->where('student_id', $user->id);
            })
            ->count();

        // Get upcoming assignments (due within 7 days)
        $upcomingAssignments = Assignment::whereIn('course_id', $courseIds)
            ->published()
            ->where('due_date', '>', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->with(['course'])
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Get recent grades
        $recentGrades = Grade::where('user_id', $user->id)
            ->with(['course', 'assignment'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get assignment submission status
        $submittedAssignments = AssignmentSubmission::where('student_id', $user->id)
            ->where('status', '!=', 'draft')
            ->count();

        $gradedAssignments = AssignmentSubmission::where('student_id', $user->id)
            ->where('status', 'graded')
            ->count();

        return $this->success(new DashboardStatsResource([
            'role' => 'student',
            'total_courses' => $totalCourses,
            'total_sks' => $totalSKS,
            'gpa' => $gpa,
            'pending_assignments' => $pendingAssignments,
            'submitted_assignments' => $submittedAssignments,
            'graded_assignments' => $gradedAssignments,
            'upcoming_assignments' => $upcomingAssignments,
            'recent_grades' => $recentGrades,
        ]));
    }

    /**
     * Get faculty (dosen) dashboard statistics.
     *
     * Returns:
     * - Total courses taught
     * - Total students across all courses
     * - Assignments pending grading
     * - Upcoming classes
     * - Average grade per course
     */
    public function facultyStats(): JsonResponse
    {
        $user = auth()->user();

        // Get courses taught by this faculty
        $courses = Course::where('instructor_id', $user->id)->get();
        $totalCourses = $courses->count();
        $activeCourses = $courses->where('is_active', true)->count();

        // Get total students across all courses
        $totalStudents = 0;
        $courseIds = [];
        foreach ($courses as $course) {
            $courseIds[] = $course->id;
            $totalStudents += $course->students()->wherePivot('status', 'enrolled')->count();
        }

        // Get assignments pending grading
        $assignmentsPendingGrading = AssignmentSubmission::whereHas('assignment', function ($query) use ($courseIds) {
            $query->whereIn('course_id', $courseIds);
        })
            ->where('status', 'submitted')
            ->count();

        // Get upcoming classes (courses with active enrollments)
        $upcomingClasses = Course::where('instructor_id', $user->id)
            ->active()
            ->with(['faculty', 'major'])
            ->withCount('students')
            ->orderBy('year', 'desc')
            ->orderBy('semester', 'desc')
            ->take(5)
            ->get();

        // Get average grade per course
        $courseGrades = [];
        foreach ($courses as $course) {
            $grades = Grade::where('course_id', $course->id)->get();
            $courseGrades[] = [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'course_code' => $course->code,
                'average_grade' => $grades->isNotEmpty() ? round($grades->avg('grade'), 2) : null,
                'students_count' => $course->students()->wherePivot('status', 'enrolled')->count(),
            ];
        }

        // Get total assignments created
        $totalAssignments = Assignment::whereIn('course_id', $courseIds)->count();
        $publishedAssignments = Assignment::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->count();

        // Get total submissions received
        $totalSubmissions = AssignmentSubmission::whereHas('assignment', function ($query) use ($courseIds) {
            $query->whereIn('course_id', $courseIds);
        })->count();

        return $this->success(new DashboardStatsResource([
            'role' => 'faculty',
            'total_courses' => $totalCourses,
            'active_courses' => $activeCourses,
            'total_students' => $totalStudents,
            'assignments_pending_grading' => $assignmentsPendingGrading,
            'upcoming_classes' => $upcomingClasses,
            'course_grades' => $courseGrades,
            'total_assignments' => $totalAssignments,
            'published_assignments' => $publishedAssignments,
            'total_submissions' => $totalSubmissions,
        ]));
    }

    /**
     * Get prodi (program study) dashboard statistics.
     *
     * Returns:
     * - Faculty students count
     * - Courses in faculty
     * - Average GPA across faculty
     * - Majors in faculty
     */
    public function prodiStats(Request $request): JsonResponse
    {
        $user = auth()->user();
        $facultyId = $request->query('faculty_id', $user->faculty_id);

        if (!$facultyId) {
            return $this->error('Faculty ID is required', 400);
        }

        $faculty = Faculty::findOrFail($facultyId);

        // Get total students in faculty
        $totalStudents = User::where('faculty_id', $facultyId)
            ->where('role', 'student')
            ->count();

        // Get courses in faculty
        $totalCourses = Course::where('faculty_id', $facultyId)->count();
        $activeCourses = Course::where('faculty_id', $facultyId)
            ->where('is_active', true)
            ->count();

        // Calculate average GPA across faculty
        $studentsWithGPA = User::where('faculty_id', $facultyId)
            ->where('role', 'student')
            ->whereNotNull('gpa')
            ->get();

        $averageGPA = $studentsWithGPA->isNotEmpty()
            ? round($studentsWithGPA->avg('gpa'), 2)
            : null;

        // Get majors in faculty
        $majors = Major::where('faculty_id', $facultyId)->get();
        $totalMajors = $majors->count();

        // Get students per major
        $majorsData = $majors->map(function ($major) {
            return [
                'id' => $major->id,
                'name' => $major->name,
                'code' => $major->code,
                'student_count' => User::where('major_id', $major->id)
                    ->where('role', 'student')
                    ->count(),
            ];
        });

        // Get total enrollments
        $courseIds = Course::where('faculty_id', $facultyId)->pluck('id');
        $totalEnrollments = CourseEnrollment::whereIn('course_id', $courseIds)->count();
        $activeEnrollments = CourseEnrollment::whereIn('course_id', $courseIds)
            ->where('status', 'enrolled')
            ->count();

        return $this->success(new DashboardStatsResource([
            'role' => 'prodi',
            'faculty_id' => $facultyId,
            'faculty_name' => $faculty->name,
            'total_students' => $totalStudents,
            'total_courses' => $totalCourses,
            'active_courses' => $activeCourses,
            'average_gpa' => $averageGPA,
            'total_majors' => $totalMajors,
            'majors_data' => $majorsData,
            'total_enrollments' => $totalEnrollments,
            'active_enrollments' => $activeEnrollments,
        ]));
    }

    /**
     * Get management (admin) dashboard statistics.
     *
     * Returns:
     * - Total users by role
     * - Total courses
     * - Total faculties
     * - System-wide analytics
     */
    public function managementStats(): JsonResponse
    {
        // User statistics
        $totalUsers = User::count();
        $totalStudents = User::where('role', 'student')->count();
        $totalFaculty = User::where('role', 'faculty')->count();
        $totalAdmins = User::where('role', 'admin')->count();

        // Course statistics
        $totalCourses = Course::count();
        $activeCourses = Course::where('is_active', true)->count();

        // Enrollment statistics
        $totalEnrollments = CourseEnrollment::count();
        $activeEnrollments = CourseEnrollment::where('status', 'enrolled')->count();
        $completedEnrollments = CourseEnrollment::where('status', 'completed')->count();

        // Faculty and Major statistics
        $totalFaculties = Faculty::count();
        $activeFaculties = Faculty::where('is_active', true)->count();
        $totalMajors = Major::count();
        $activeMajors = Major::where('is_active', true)->count();

        // Assignments statistics
        $totalAssignments = Assignment::count();
        $totalSubmissions = AssignmentSubmission::count();
        $gradedSubmissions = AssignmentSubmission::where('status', 'graded')->count();

        // Grade statistics
        $totalGrades = Grade::count();
        $averageGrade = Grade::whereNotNull('grade')->avg('grade');

        // Get students by enrollment year
        $studentsByYear = User::where('role', 'student')
            ->whereNotNull('enrollment_year')
            ->select('enrollment_year', DB::raw('count(*) as count'))
            ->groupBy('enrollment_year')
            ->orderBy('enrollment_year')
            ->get();

        // Get courses by faculty
        $coursesByFaculty = Faculty::withCount('courses')
            ->orderBy('courses_count', 'desc')
            ->take(5)
            ->get();

        return $this->success(new DashboardStatsResource([
            'role' => 'management',
            'users' => [
                'total' => $totalUsers,
                'students' => $totalStudents,
                'faculty' => $totalFaculty,
                'admins' => $totalAdmins,
            ],
            'courses' => [
                'total' => $totalCourses,
                'active' => $activeCourses,
            ],
            'enrollments' => [
                'total' => $totalEnrollments,
                'active' => $activeEnrollments,
                'completed' => $completedEnrollments,
            ],
            'faculties' => [
                'total' => $totalFaculties,
                'active' => $activeFaculties,
            ],
            'majors' => [
                'total' => $totalMajors,
                'active' => $activeMajors,
            ],
            'assignments' => [
                'total' => $totalAssignments,
                'submissions' => $totalSubmissions,
                'graded' => $gradedSubmissions,
            ],
            'grades' => [
                'total' => $totalGrades,
                'average' => $averageGrade ? round($averageGrade, 2) : null,
            ],
            'students_by_year' => $studentsByYear,
            'courses_by_faculty' => $coursesByFaculty,
        ]));
    }

    /**
     * Get grade distribution analytics for a course.
     *
     * Returns distribution of grades (A, B, C, D, F)
     */
    public function gradeDistribution(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $courseId = $request->input('course_id');

        $grades = Grade::where('course_id', $courseId)
            ->whereNotNull('grade')
            ->get();

        $distribution = [
            'A' => 0,
            'B' => 0,
            'C' => 0,
            'D' => 0,
            'F' => 0,
        ];

        foreach ($grades as $grade) {
            $letter = $grade->getGradeLetter();
            if (array_key_exists($letter, $distribution)) {
                $distribution[$letter]++;
            }
        }

        $total = $grades->count();

        return $this->success([
            'course_id' => $courseId,
            'total_grades' => $total,
            'distribution' => $distribution,
            'percentages' => $total > 0 ? array_map(fn($count) => round(($count / $total) * 100, 2), $distribution) : $distribution,
            'average_grade' => $grades->isNotEmpty() ? round($grades->avg('grade'), 2) : null,
            'highest_grade' => $grades->max('grade'),
            'lowest_grade' => $grades->min('grade'),
        ]);
    }

    /**
     * Get enrollment trends analytics.
     *
     * Returns enrollment data grouped by month or semester
     */
    public function enrollmentTrends(Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'in:monthly,semesterly,yearly',
            'faculty_id' => 'nullable|exists:faculties,id',
            'major_id' => 'nullable|exists:majors,id',
        ]);

        $period = $request->input('period') ?? 'monthly';
        $facultyId = $request->input('faculty_id');
        $majorId = $request->input('major_id');

        $query = CourseEnrollment::query();

        if ($facultyId) {
            $courseIds = Course::where('faculty_id', $facultyId)->pluck('id');
            $query->whereIn('course_id', $courseIds);
        }

        if ($majorId) {
            $courseIds = Course::where('major_id', $majorId)->pluck('id');
            $query->whereIn('course_id', $courseIds);
        }

        $trends = match ($period) {
            'monthly' => $query->select(
                    DB::raw("strftime('%Y-%m', enrolled_at) as period"),
                    DB::raw('count(*) as count')
                )
                ->whereNotNull('enrolled_at')
                ->groupBy('period')
                ->orderBy('period')
                ->get(),
            'semesterly' => $query->select(
                    DB::raw("strftime('%Y', enrolled_at) as year"),
                    DB::raw("((strftime('%m', enrolled_at) - 1) / 3 + 1) as quarter"),
                    DB::raw('count(*) as count')
                )
                ->whereNotNull('enrolled_at')
                ->groupBy('year', 'quarter')
                ->orderBy('year')
                ->orderBy('quarter')
                ->get(),
            'yearly' => $query->select(
                    DB::raw("strftime('%Y', enrolled_at) as period"),
                    DB::raw('count(*) as count')
                )
                ->whereNotNull('enrolled_at')
                ->groupBy('period')
                ->orderBy('period')
                ->get(),
        };

        return $this->success([
            'period' => $period,
            'faculty_id' => $facultyId,
            'major_id' => $majorId,
            'trends' => $trends,
            'total_enrollments' => $query->count(),
        ]);
    }

    /**
     * Calculate GPA from a collection of grades.
     *
     * Uses 4.0 scale: A=4.0, B=3.0, C=2.0, D=1.0, F=0.0
     */
    private function calculateGPA($grades): float
    {
        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalPoints = 0;
        $count = 0;

        foreach ($grades as $grade) {
            $gradePoints = match (true) {
                $grade->grade >= 90 => 4.0,
                $grade->grade >= 80 => 3.0,
                $grade->grade >= 70 => 2.0,
                $grade->grade >= 60 => 1.0,
                default => 0.0,
            };
            $totalPoints += $gradePoints;
            $count++;
        }

        return $count > 0 ? round($totalPoints / $count, 2) : 0.0;
    }

    /**
     * Get dashboard stats for a specific instructor by name.
     * Used for dosen dashboard view.
     */
    public function dosenStats(string $instructorName): JsonResponse
    {
        // Find instructor by name
        $instructor = User::where('role', 'faculty')
            ->where('name', 'like', "%{$instructorName}%")
            ->firstOrFail();

        // Get courses taught by this instructor
        $courses = Course::where('instructor_id', $instructor->id)->get();
        $totalCourses = $courses->count();
        $activeCourses = $courses->where('is_active', true)->count();

        // Get total students across all courses
        $totalStudents = 0;
        $courseIds = [];
        foreach ($courses as $course) {
            $courseIds[] = $course->id;
            $totalStudents += $course->students()->wherePivot('status', 'enrolled')->count();
        }

        // Get assignments pending grading
        $assignmentsPendingGrading = AssignmentSubmission::whereHas('assignment', function ($query) use ($courseIds) {
            $query->whereIn('course_id', $courseIds);
        })
            ->where('status', 'submitted')
            ->count();

        // Get upcoming classes
        $upcomingClasses = Course::where('instructor_id', $instructor->id)
            ->where('is_active', true)
            ->with(['faculty', 'major'])
            ->withCount('students')
            ->orderBy('year', 'desc')
            ->orderBy('semester', 'desc')
            ->take(5)
            ->get();

        // Get average grade per course
        $courseGrades = [];
        foreach ($courses as $course) {
            $grades = Grade::where('course_id', $course->id)->get();
            $courseGrades[] = [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'course_code' => $course->code,
                'average_grade' => $grades->isNotEmpty() ? round($grades->avg('grade'), 2) : null,
                'students_count' => $course->students()->wherePivot('status', 'enrolled')->count(),
            ];
        }

        // Get total assignments created
        $totalAssignments = Assignment::whereIn('course_id', $courseIds)->count();
        $publishedAssignments = Assignment::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->count();

        // Get total submissions received
        $totalSubmissions = AssignmentSubmission::whereHas('assignment', function ($query) use ($courseIds) {
            $query->whereIn('course_id', $courseIds);
        })->count();

        return $this->success(new DashboardStatsResource([
            'role' => 'faculty',
            'instructor_id' => $instructor->id,
            'instructor_name' => $instructor->name,
            'total_courses' => $totalCourses,
            'active_courses' => $activeCourses,
            'total_students' => $totalStudents,
            'assignments_pending_grading' => $assignmentsPendingGrading,
            'upcoming_classes' => $upcomingClasses,
            'course_grades' => $courseGrades,
            'total_assignments' => $totalAssignments,
            'published_assignments' => $publishedAssignments,
            'total_submissions' => $totalSubmissions,
        ]));
    }

    /**
     * Get faculty enrollment statistics.
     * Returns enrollment data grouped by faculty.
     */
    public function facultyEnrollment(Request $request): JsonResponse
    {
        $faculties = Faculty::with(['courses'])
            ->withCount('courses')
            ->get();

        $enrollmentData = $faculties->map(function ($faculty) {
            $courseIds = $faculty->courses->pluck('id');

            $totalEnrollments = CourseEnrollment::whereIn('course_id', $courseIds)->count();
            $activeEnrollments = CourseEnrollment::whereIn('course_id', $courseIds)
                ->where('status', 'enrolled')
                ->count();
            $completedEnrollments = CourseEnrollment::whereIn('course_id', $courseIds)
                ->where('status', 'completed')
                ->count();
            $droppedEnrollments = CourseEnrollment::whereIn('course_id', $courseIds)
                ->where('status', 'dropped')
                ->count();

            // Get unique students
            $uniqueStudents = CourseEnrollment::whereIn('course_id', $courseIds)
                ->distinct('student_id')
                ->count('student_id');

            return [
                'faculty_id' => $faculty->id,
                'faculty_name' => $faculty->name,
                'faculty_code' => $faculty->code,
                'total_courses' => $faculty->courses_count,
                'total_enrollments' => $totalEnrollments,
                'active_enrollments' => $activeEnrollments,
                'completed_enrollments' => $completedEnrollments,
                'dropped_enrollments' => $droppedEnrollments,
                'unique_students' => $uniqueStudents,
            ];
        });

        return $this->success([
            'data' => $enrollmentData,
            'total_faculties' => $faculties->count(),
            'total_enrollments' => $enrollmentData->sum('total_enrollments'),
            'total_active_enrollments' => $enrollmentData->sum('active_enrollments'),
        ]);
    }
}