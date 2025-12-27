<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MajorController extends Controller
{
    /**
     * Display a listing of majors.
     */
    public function index(): JsonResponse
    {
        $majors = Major::active()->with('faculty')->get();
        return $this->success($majors);
    }

    /**
     * Store a newly created major.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:majors',
            'description' => 'nullable|string',
            'head_of_program' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'duration_years' => 'nullable|integer|min:1|max:10',
            'credit_hours' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $major = Major::create($validated);
        return $this->created($major, 'Major created successfully');
    }

    /**
     * Display the specified major.
     */
    public function show(string $id): JsonResponse
    {
        $major = Major::with('faculty')->findOrFail($id);
        return $this->success($major);
    }

    /**
     * Update the specified major.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $major = Major::findOrFail($id);

        $validated = $request->validate([
            'faculty_id' => 'sometimes|exists:faculties,id',
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:majors,code,' . $id,
            'description' => 'nullable|string',
            'head_of_program' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'duration_years' => 'nullable|integer|min:1|max:10',
            'credit_hours' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $major->update($validated);
        return $this->success($major, 'Major updated successfully');
    }

    /**
     * Remove the specified major.
     */
    public function destroy(string $id): JsonResponse
    {
        $major = Major::findOrFail($id);
        $major->delete();
        return $this->noContent();
    }

    /**
     * Get the faculty for this major.
     */
    public function faculty(string $id): JsonResponse
    {
        $major = Major::findOrFail($id);
        $faculty = $major->faculty;
        return $this->success($faculty);
    }

    /**
     * Get courses for this major.
     */
    public function courses(string $id): JsonResponse
    {
        $major = Major::findOrFail($id);
        $courses = $major->courses()->active()->with('instructor')->get();
        return $this->success($courses);
    }
}