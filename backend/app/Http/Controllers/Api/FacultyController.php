<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FacultyController extends Controller
{
    /**
     * Display a listing of faculties.
     */
    public function index(): JsonResponse
    {
        $faculties = Faculty::active()->get();
        return $this->success($faculties);
    }

    /**
     * Store a newly created faculty.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:faculties',
            'description' => 'nullable|string',
            'dean_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $faculty = Faculty::create($validated);
        return $this->created($faculty, 'Faculty created successfully');
    }

    /**
     * Display the specified faculty.
     */
    public function show(string $id): JsonResponse
    {
        $faculty = Faculty::with('majors')->findOrFail($id);
        return $this->success($faculty);
    }

    /**
     * Update the specified faculty.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:faculties,code,' . $id,
            'description' => 'nullable|string',
            'dean_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $faculty->update($validated);
        return $this->success($faculty, 'Faculty updated successfully');
    }

    /**
     * Remove the specified faculty.
     */
    public function destroy(string $id): JsonResponse
    {
        $faculty = Faculty::findOrFail($id);
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