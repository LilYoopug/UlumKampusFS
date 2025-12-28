<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MajorRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\FacultyResource;
use App\Http\Resources\MajorResource;
use App\Models\Course;
use App\Models\Major;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MajorController extends ApiController
{
    /**
     * Display a listing of majors.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $majors = Major::active()->with('faculty')->paginate($perPage);
        return $this->paginated(MajorResource::collection($majors));
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
    public function show(string $code): JsonResponse
    {
        $major = Major::with('faculty')->findOrFail($code);
        return $this->success(new MajorResource($major));
    }

    /**
     * Update the specified major.
     */
    public function update(MajorRequest $request, string $code): JsonResponse
    {
        $major = Major::findOrFail($code);
        $major->update($request->validated());
        return $this->success(new MajorResource($major), 'Major updated successfully');
    }

    /**
     * Remove the specified major.
     */
    public function destroy(string $code): JsonResponse
    {
        $major = Major::findOrFail($code);
        $major->delete();
        return $this->noContent();
    }

    /**
     * Get the faculty for this major.
     */
    public function faculty(string $code): JsonResponse
    {
        $major = Major::findOrFail($code);
        return $this->success(new FacultyResource($major->faculty));
    }

    /**
     * Get courses for this major.
     */
    public function courses(string $code): JsonResponse
    {
        $major = Major::findOrFail($code);
        $courses = $major->courses()->active()->with('instructor')->get();
        return $this->success(CourseResource::collection($courses));
    }
}