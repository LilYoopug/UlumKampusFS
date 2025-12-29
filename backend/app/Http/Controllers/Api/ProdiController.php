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

class ProdiController extends ApiController
{
    /**
     * Get Prodi dashboard statistics.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $facultyId = $request->user()->faculty_id;
        if (!$facultyId) {
            return $this->error('User is not assigned to any faculty');
        }

        $cacheKey = "prodi_dashboard_{$facultyId}";
        $stats = Cache::remember($cacheKey, 300, function () use ($facultyId) {
            return [
                'overview' => [
                    'total_students' => User::where('faculty_id', $facultyId)->where('role', 'student')->count(),
                    'total_lecturers' => User::where('faculty_id', $facultyId)->where('role', 'faculty')->count(),
                    'total_courses' => Course::where('faculty_id', $facultyId)->count(),
                    'active_courses' => Course::where('faculty_id', $facultyId)->where('is_active', true)->count(),
                    'total_enrollments' => CourseEnrollment::whereHas('course', function ($query) use ($facultyId) {
                        $query->where('faculty_id', $facultyId);
                    })->count(),
                    'active_enrollments' => CourseEnrollment::whereHas('course', function ($query) use ($facultyId) {
                        $query->where('faculty_id', $facultyId);
                    })->where('status', 'enrolled')->count(),
                ],
                'major_stats' => $this->getMajorStatistics($facultyId),
                'course_stats' => $this->getProdiCourseStatistics($facultyId),
                'lecturer_performance' => $this->getLecturerPerformance($facultyId),
                'student_performance' => $this->getStudentPerformance($facultyId),
                'enrollment_trends' => $this->getProdiEnrollmentTrends($facultyId),
            ];
        });

        return $this->success($stats);
    }

    /**
     * Get courses managed by the Prodi.
     */
    public function courses(Request $request): JsonResponse
    {
        $facultyId = $request->user()->faculty_id;
        if (!$facultyId) {
            return $this->error('User is not assigned to any faculty');
        }

        $query = Course::with(['major', 'instructor', 'enrollments'])
            ->where('faculty_id', $facultyId);

        // Filter by major
        if ($request->has('major_id')) {
            $query->where('major_id', $request->major_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by instructor
        if ($request->has('instructor_id')) {
            $query->where('instructor_id', $request->instructor_id);
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

        // Paginate
        $perPage = $request->get('per_page', 15);
        $courses = $query->paginate($perPage);

        return $this->success($courses);
    }

    /**
     * Create a new course for the Prodi.
     */
    public function createCourse(Request $request): JsonResponse
    {
        $facultyId = $request->user()->faculty_id;
        if (!$facultyId) {
            return $this->error('User is not assigned to any faculty');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:courses,code',
            'description' => 'nullable|string',
            'credits' => 'required|integer|min:1|max:10',
            'type' => 'required|in:lecture,lab,seminar',
            'major_id' => 'required|exists:majors,id',
            'instructor_id' => 'required|exists:users,id',
            'semester' => 'required|integer|min:1|max:8',
            'academic_year' => 'required|string',
            'max_students' => 'nullable|integer|min:1',
        ]);

        // Verify instructor belongs to the same faculty
        $instructor = User::find($request->instructor_id);
        if (!$instructor || $instructor->faculty_id !== $facultyId) {
            return $this->error('Instructor must belong to the same faculty');
        }

        // Verify major belongs to the same faculty
        $major = Major::find($request->major_id);
        if (!$major || $major->faculty_id !== $facultyId) {
            return $this->error('Major must belong to the same faculty');
        }

        try {
            $courseData = array_merge($request->all(), [
                'faculty_id' => $facultyId,
                'is_active' => true,
            ]);

            $course = Course::create($courseData);
            return $this->success($course, 'Course created successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to create course: ' . $e->getMessage());
        }
    }

    /**
     * Get lecturers in the Prodi.
     */
    public function lecturers(Request $request): JsonResponse
    {
        $facultyId = $request->user()->faculty_id;
        if (!$facultyId) {
            return $this->error('User is not assigned to any faculty');
        }

        $query = User::with(['major'])
            ->where('faculty_id', $facultyId)
            ->where('role', 'faculty');

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
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $lecturers = $query->paginate($perPage);

        return $this->success($lecturers);
    }

    /**
     * Get students in the Prodi.
     */
    public function students(Request $request): JsonResponse
    {
        $facultyId = $request->user()->faculty_id;
        if (!$facultyId) {
            return $this->error('User is not assigned to any faculty');
        }

        $query = User::with(['major', 'enrollments.course'])
            ->where('faculty_id', $facultyId)
            ->where('role', 'student');

        // Filter by major
        if ($request->has('major_id')) {
            $query->where('major_id', $request->major_id);
        }

        // Filter by enrollment status
        if ($request->has('enrollment_status')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('status', $request->enrollment_status);
            });
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
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $students = $query->paginate($perPage);

        return $this->success($students);
    }

    /**
     * Get enrollments for the Prodi.
     */
    public function enrollments(Request $request): JsonResponse
    {
        $facultyId = $request->user()->faculty_id;
        if (!$facultyId) {
            return $this->error('User is not assigned to any faculty');
        }

        $query = CourseEnrollment::with(['student', 'course', 'course.major'])
            ->whereHas('course', function ($query) use ($facultyId) {
                $query->where('faculty_id', $facultyId);
            });

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by course
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by major
        if ($request->has('major_id')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('major_id', $request->major_id);
            });
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

        // Paginate
        $perPage = $request->get('per_page', 15);
        $enrollments = $query->paginate($perPage);

        return $this->success($enrollments);
    }

    /**
     * Get performance analytics for the Prodi.
     */
    public function analytics(Request $request): JsonResponse
    {
        $facultyId = $request->user()->faculty_id;
        if (!$facultyId) {
            return $this->error('User is not assigned to any faculty');
        }

        $type = $request->get('type', 'overview');
        $period = $request->get('period', '30'); // days

        switch ($type) {
            case 'overview':
                return $this->success($this->getProdiOverviewAnalytics($facultyId, $period));
            case 'course_performance':
                return $this->success($this->getProdiCoursePerformanceAnalytics($facultyId, $period));
            case 'lecturer_performance':
                return $this->success($this->getProdiLecturerPerformanceAnalytics($facultyId, $period));
            case 'student_performance':
                return $this->success($this->getProdiStudentPerformanceAnalytics($facultyId, $period));
            case 'enrollment_analytics':
                return $this->success($this->getProdiEnrollmentAnalytics($facultyId, $period));
            default:
                return $this->error('Invalid analytics type');
        }
    }

    /**
     * Bulk operations for Prodi management.
     */
    public function bulkOperation(Request $request): JsonResponse
    {
        $facultyId = $request->user()->faculty_id;
        if (!$facultyId) {
            return $this->error('User is not assigned to any faculty');
        }

        $request->validate([
            'type' => 'required|in:courses,students,lecturers',
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'operation' => 'required|in:activate,deactivate,delete,assign',
            'target_id' => 'required_if:operation,assign|integer'
        ]);

        $type = $request->type;
        $ids = $request->ids;
        $operation = $request->operation;

        try {
            DB::beginTransaction();

            switch ($type) {
                case 'courses':
                    $result = $this->bulkCourseOperation($facultyId, $ids, $operation, $request->target_id);
                    break;
                case 'students':
                    $result = $this->bulkStudentOperation($facultyId, $ids, $operation, $request->target_id);
                    break;
                case 'lecturers':
                    $result = $this->bulkLecturerOperation($facultyId, $ids, $operation, $request->target_id);
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid type');
            }

            DB::commit();
            return $this->success($result);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Bulk operation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get major statistics for the Prodi.
     */
    private function getMajorStatistics(int $facultyId): array
    {
        return Major::where('faculty_id', $facultyId)
            ->withCount(['users', 'courses'])
            ->get()
            ->map(function ($major) {
                return [
                    'id' => $major->id,
                    'name' => $major->name,
                    'code' => $major->code,
                    'users_count' => $major->users_count,
                    'courses_count' => $major->courses_count,
                    'students_count' => User::where('major_id', $major->id)->where('role', 'student')->count(),
                    'lecturers_count' => User::where('major_id', $major->id)->where('role', 'faculty')->count(),
                ];
            })
            ->toArray();
    }

    /**
     * Get course statistics for the Prodi.
     */
    private function getProdiCourseStatistics(int $facultyId): array
    {
        $courses = Course::where('faculty_id', $facultyId);

        return [
            'total' => $courses->count(),
            'active' => $courses->where('is_active', true)->count(),
            'inactive' => $courses->where('is_active', false)->count(),
            'by_type' => $courses->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_semester' => $courses->select('semester', DB::raw('count(*) as count'))
                ->groupBy('semester')
                ->orderBy('semester')
                ->pluck('count', 'semester')
                ->toArray(),
        ];
    }

    /**
     * Get lecturer performance for the Prodi.
     */
    private function getLecturerPerformance(int $facultyId): array
    {
        return User::where('faculty_id', $facultyId)
            ->where('role', 'faculty')
            ->with(['courses' => function ($query) {
                $query->withCount('enrollments');
            }])
            ->get()
            ->map(function ($lecturer) {
                $totalCourses = $lecturer->courses->count();
                $totalEnrollments = $lecturer->courses->sum('enrollments_count');
                $avgEnrollment = $totalCourses > 0 ? $totalEnrollments / $totalCourses : 0;

                return [
                    'id' => $lecturer->id,
                    'name' => $lecturer->name,
                    'email' => $lecturer->email,
                    'courses_count' => $totalCourses,
                    'total_enrollments' => $totalEnrollments,
                    'average_enrollment_per_course' => round($avgEnrollment, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Get student performance for the Prodi.
     */
    private function getStudentPerformance(int $facultyId): array
    {
        $students = User::where('faculty_id', $facultyId)
            ->where('role', 'student')
            ->with(['enrollments.course', 'grades']);

        $totalStudents = $students->count();
        $activeStudents = $students->whereHas('enrollments', function ($query) {
            $query->where('status', 'enrolled');
        })->count();

        $averageGPA = Grade::whereHas('enrollment', function ($query) use ($facultyId) {
            $query->whereHas('course', function ($subQuery) use ($facultyId) {
                $subQuery->where('faculty_id', $facultyId);
            });
        })->avg('gpa') ?? 0;

        return [
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'inactive_students' => $totalStudents - $activeStudents,
            'average_gpa' => round($averageGPA, 2),
            'by_major' => Major::where('faculty_id', $facultyId)
                ->withCount(['users' => function ($query) {
                    $query->where('role', 'student');
                }])
                ->pluck('users_count', 'name')
                ->toArray(),
        ];
    }

    /**
     * Get enrollment trends for the Prodi.
     */
    private function getProdiEnrollmentTrends(int $facultyId): array
    {
        return CourseEnrollment::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->whereHas('course', function ($query) use ($facultyId) {
                $query->where('faculty_id', $facultyId);
            })
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Get Prodi overview analytics.
     */
    private function getProdiOverviewAnalytics(int $facultyId, int $period): array
    {
        return [
            'overview' => $this->getProdiOverviewStats($facultyId),
            'enrollment_trends' => $this->getProdiEnrollmentTrends($facultyId),
            'major_distribution' => $this->getMajorStatistics($facultyId),
            'course_types' => $this->getProdiCourseStatistics($facultyId),
        ];
    }

    /**
     * Get Prodi course performance analytics.
     */
    private function getProdiCoursePerformanceAnalytics(int $facultyId, int $period): array
    {
        return [
            'course_performance' => Course::where('faculty_id', $facultyId)
                ->withCount(['enrollments', 'assignments'])
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
                ->toArray(),
        ];
    }

    /**
     * Get Prodi lecturer performance analytics.
     */
    private function getProdiLecturerPerformanceAnalytics(int $facultyId, int $period): array
    {
        return [
            'lecturer_performance' => $this->getLecturerPerformance($facultyId),
        ];
    }

    /**
     * Get Prodi student performance analytics.
     */
    private function getProdiStudentPerformanceAnalytics(int $facultyId, int $period): array
    {
        return [
            'student_performance' => $this->getStudentPerformance($facultyId),
            'grade_distribution' => Grade::select('letter_grade', DB::raw('count(*) as count'))
                ->whereHas('enrollment', function ($query) use ($facultyId) {
                    $query->whereHas('course', function ($subQuery) use ($facultyId) {
                        $subQuery->where('faculty_id', $facultyId);
                    });
                })
                ->groupBy('letter_grade')
                ->orderBy('letter_grade')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Get Prodi enrollment analytics.
     */
    private function getProdiEnrollmentAnalytics(int $facultyId, int $period): array
    {
        return [
            'by_status' => CourseEnrollment::select('status', DB::raw('count(*) as count'))
                ->whereHas('course', function ($query) use ($facultyId) {
                    $query->where('faculty_id', $facultyId);
                })
                ->where('created_at', '>=', now()->subDays($period))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'by_major' => CourseEnrollment::select(
                    'majors.name as major_name',
                    DB::raw('count(*) as count')
                )
                ->join('courses', 'course_enrollments.course_id', '=', 'courses.id')
                ->join('majors', 'courses.major_id', '=', 'majors.id')
                ->where('courses.faculty_id', $facultyId)
                ->where('course_enrollments.created_at', '>=', now()->subDays($period))
                ->groupBy('majors.name')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Get Prodi overview stats.
     */
    private function getProdiOverviewStats(int $facultyId): array
    {
        return [
            'total_students' => User::where('faculty_id', $facultyId)->where('role', 'student')->count(),
            'total_lecturers' => User::where('faculty_id', $facultyId)->where('role', 'faculty')->count(),
            'total_courses' => Course::where('faculty_id', $facultyId)->count(),
            'total_majors' => Major::where('faculty_id', $facultyId)->count(),
            'total_enrollments' => CourseEnrollment::whereHas('course', function ($query) use ($facultyId) {
                $query->where('faculty_id', $facultyId);
            })->count(),
        ];
    }

    /**
     * Bulk course operation for Prodi.
     */
    private function bulkCourseOperation(int $facultyId, array $ids, string $operation, ?int $targetId): array
    {
        $query = Course::where('faculty_id', $facultyId)->whereIn('id', $ids);

        switch ($operation) {
            case 'activate':
                $query->update(['is_active' => true]);
                return ['message' => 'Courses activated successfully', 'affected' => $query->count()];
            case 'deactivate':
                $query->update(['is_active' => false]);
                return ['message' => 'Courses deactivated successfully', 'affected' => $query->count()];
            case 'delete':
                $count = $query->count();
                $query->delete();
                return ['message' => 'Courses deleted successfully', 'affected' => $count];
            case 'assign':
                if (!$targetId) {
                    throw new \InvalidArgumentException('Target ID is required for assign operation');
                }
                $query->update(['instructor_id' => $targetId]);
                return ['message' => 'Instructor assigned to courses successfully', 'affected' => $query->count()];
            default:
                throw new \InvalidArgumentException('Invalid operation');
        }
    }

    /**
     * Bulk student operation for Prodi.
     */
    private function bulkStudentOperation(int $facultyId, array $ids, string $operation, ?int $targetId): array
    {
        $query = User::where('faculty_id', $facultyId)->where('role', 'student')->whereIn('id', $ids);

        switch ($operation) {
            case 'activate':
                $query->update(['email_verified_at' => now()]);
                return ['message' => 'Students activated successfully', 'affected' => $query->count()];
            case 'deactivate':
                $query->update(['email_verified_at' => null]);
                return ['message' => 'Students deactivated successfully', 'affected' => $query->count()];
            case 'assign':
                if (!$targetId) {
                    throw new \InvalidArgumentException('Target ID is required for assign operation');
                }
                $query->update(['major_id' => $targetId]);
                return ['message' => 'Students assigned to major successfully', 'affected' => $query->count()];
            default:
                throw new \InvalidArgumentException('Invalid operation');
        }
    }

    /**
     * Bulk lecturer operation for Prodi.
     */
    private function bulkLecturerOperation(int $facultyId, array $ids, string $operation, ?int $targetId): array
    {
        $query = User::where('faculty_id', $facultyId)->where('role', 'faculty')->whereIn('id', $ids);

        switch ($operation) {
            case 'activate':
                $query->update(['email_verified_at' => now()]);
                return ['message' => 'Lecturers activated successfully', 'affected' => $query->count()];
            case 'deactivate':
                $query->update(['email_verified_at' => null]);
                return ['message' => 'Lecturers deactivated successfully', 'affected' => $query->count()];
            case 'assign':
                if (!$targetId) {
                    throw new \InvalidArgumentException('Target ID is required for assign operation');
                }
                $query->update(['major_id' => $targetId]);
                return ['message' => 'Lecturers assigned to major successfully', 'affected' => $query->count()];
            default:
                throw new \InvalidArgumentException('Invalid operation');
        }
    }
}
