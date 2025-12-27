<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MajorRequest;
use App\Http\Resources\FacultyResource;
use App\Http\Resources\MajorResource;
use App\Models\Course;
use App\Models\Major;
use Illuminate\Http\JsonResponse;

class MajorController extends Controller
{
    /**
     * Display a listing of majors.
     */
    public function index(): JsonResponse
    {
        $majors = Major::active()->with('faculty')->get();
        return $this->success(MajorResource::collection($majors));
    }

    /**
     * Store a newly created major.
     */
    public function store(MajorRequest $request): JsonResponse
    {
        $major = Major::create($request->validated());
        return $this->created(new MajorResource($major), 'Major created successfully');
    }

    /**
     * Display the specified major.
     */
    public function show(string $id): JsonResponse
    {
        $major = Major::with('faculty')->findOrFail($id);
        return $this->success(new MajorResource($major));
    }

    /**
     * Update the specified major.
     */
    public function update(MajorRequest $request, string $id): JsonResponse
    {
        $major = Major::findOrFail($id);
        $major->update($request->validated());
        return $this->success(new MajorResource($major), 'Major updated successfully');
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
        return $this->success(new FacultyResource($major->faculty));
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