<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\CourseModuleResource;
use App\Models\CourseModule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseModuleController extends ApiController {
    /**
     * Display a listing of course modules.
     */
    public function index(): JsonResponse
    {
        $modules = CourseModule::with('course')->ordered()->get();
        return $this->success(
            CourseModuleResource::collection($modules),
            'Course modules retrieved successfully'
        );
    }

    /**
     * Store a newly created course module.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'document_url' => 'nullable|url|max:500',
            'order' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]);

        $validated['published_at'] = $validated['is_published'] ?? false ? now() : null;
        $module = CourseModule::create($validated);
        return $this->created(
            new CourseModuleResource($module->load('course')),
            'Course module created successfully'
        );
    }

    /**
     * Display the specified course module.
     */
    public function show(string $id): JsonResponse
    {
        $module = CourseModule::with('course')->findOrFail($id);
        return $this->success(
            new CourseModuleResource($module),
            'Course module retrieved successfully'
        );
    }

    /**
     * Update the specified course module.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $module = CourseModule::findOrFail($id);

        $validated = $request->validate([
            'course_id' => 'sometimes|exists:courses,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'document_url' => 'nullable|url|max:500',
            'order' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]);

        if (isset($validated['is_published']) && $validated['is_published'] && !$module->is_published) {
            $validated['published_at'] = now();
        }

        $module->update($validated);
        return $this->success(
            new CourseModuleResource($module->load('course')),
            'Course module updated successfully'
        );
    }

    /**
     * Remove the specified course module.
     */
    public function destroy(string $id): JsonResponse
    {
        $module = CourseModule::findOrFail($id);
        $module->delete();
        return $this->noContent();
    }

    /**
     * Get assignments for this module.
     */
    public function assignments(string $id): JsonResponse
    {
        $module = CourseModule::findOrFail($id);
        $assignments = $module->assignments()->ordered()->get();
        return $this->success(
            AssignmentResource::collection($assignments),
            'Module assignments retrieved successfully'
        );
    }

    /**
     * Get discussion threads for this module.
     */
    public function discussions(string $id): JsonResponse
    {
        $module = CourseModule::findOrFail($id);
        $threads = $module->discussionThreads()->recent()->get();
        return $this->success(
            $threads,
            'Module discussions retrieved successfully'
        );
    }
}