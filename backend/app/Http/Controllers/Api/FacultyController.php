<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\FacultyRequest;
use App\Models\Faculty;
use App\Models\Course;
use App\Models\Major;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * FacultyController
 *
 * Handles CRUD operations for faculties.
 * Admin and Faculty users can create, update, and delete faculties.
 * All authenticated users can view faculties.
 */
class FacultyController extends ApiController
{
    /**
     * Display a listing of faculties.
     *
     * Returns all active faculties. Supports filtering via query parameters.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $faculties = Faculty::active()->get();
        return $this->success($faculties);
    }

    /**
     * Store a newly created faculty.
     *
     * Only Admin and Faculty users can create faculties.
     *
     * @param FacultyRequest $request
     * @return JsonResponse
     */
    public function store(FacultyRequest $request): JsonResponse
    {
        $faculty = Faculty::create($request->validated());
        return $this->created($faculty, 'Faculty created successfully');
    }

    /**
     * Display the specified faculty.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $faculty = Faculty::with('majors')->findOrFail($id);
        return $this->success($faculty);
    }

    /**
     * Update the specified faculty.
     *
     * Only Admin and Faculty users can update faculties.
     *
     * @param FacultyRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(FacultyRequest $request, string $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);
        $faculty->update($request->validated());
        return $this->success($faculty, 'Faculty updated successfully');
    }

    /**
     * Remove the specified faculty.
     *
     * Only Admin and Faculty users can delete faculties.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);
        $faculty->delete();
        return $this->noContent();
    }

    /**
     * Get majors for this faculty.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function majors(string $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);
        $majors = $faculty->majors()->active()->get();
        return $this->success($majors);
    }

    /**
     * Get courses for this faculty.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function courses(string $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);
        $courses = $faculty->courses()->active()->get();
        return $this->success($courses);
    }

    /**
     * Get courses for the current faculty user.
     *
     * Only Faculty users can access this endpoint.
     *
     * @return JsonResponse
     */
    public function myCourses(): JsonResponse
    {
        $user = auth()->user();
        $courses = Course::where('instructor_id', $user->id)
            ->active()
            ->with('faculty', 'major', 'students')
            ->get();
        return $this->success($courses);
    }

    /**
     * Get faculty statistics for the current faculty user.
     *
     * Only Faculty users can access this endpoint.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $user = auth()->user();
        $facultyId = $user->faculty_id;

        return $this->success([
            'total_students' => User::where('faculty_id', $facultyId)
                ->where('role', 'student')
                ->count(),
            'total_courses' => Course::where('faculty_id', $facultyId)->count(),
            'active_courses' => Course::where('faculty_id', $facultyId)
                ->where('is_active', true)
                ->count(),
            'total_majors' => Major::where('faculty_id', $facultyId)->count(),
        ]);
    }
}