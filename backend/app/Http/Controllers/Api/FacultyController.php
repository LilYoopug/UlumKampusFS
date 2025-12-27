<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\FacultyRequest;
use App\Http\Resources\FacultyResource;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FacultyController extends ApiController
{
    /**
     * Display a listing of faculties.
     */
    public function index(): JsonResponse
    {
        $faculties = Faculty::active()->get();
        return $this->success(FacultyResource::collection($faculties));
    }

    /**
     * Store a newly created faculty.
     */
    public function store(FacultyRequest $request): JsonResponse
    {
        $faculty = Faculty::create($request->validated());
        return $this->created(new FacultyResource($faculty), 'Faculty created successfully');
    }

    /**
     * Display the specified faculty.
     */
    public function show(string $id): JsonResponse
    {
        $faculty = Faculty::with('majors')->findOrFail($id);
        return $this->success(new FacultyResource($faculty));
    }

    /**
     * Update the specified faculty.
     */
    public function update(FacultyRequest $request, string $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);
        $faculty->update($request->validated());
        return $this->success(new FacultyResource($faculty), 'Faculty updated successfully');
    }

    /**
     * Remove the specified faculty.
     *
     * Prevents deletion if the faculty has associated users.
     */
    public function destroy(string $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);

        // Check if faculty has any users (students or faculty members)
        if ($faculty->users()->exists()) {
            return $this->conflict(
                'Cannot delete faculty with associated users. Please reassign or remove all users first.'
            );
        }

        $faculty->delete();
        return $this->noContent();
    }

    /**
     * Get majors for this faculty.
     */
    public function majors(string $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);
        $majors = $faculty->majors()->active()->get();
        return $this->success($majors);
    }

    /**
     * Get courses for this faculty.
     */
    public function courses(string $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);
        $courses = $faculty->courses()->active()->get();
        return $this->success($courses);
    }

    /**
     * Get courses for the current faculty user.
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
     * Get faculty statistics.
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