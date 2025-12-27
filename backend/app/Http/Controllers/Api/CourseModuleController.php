<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseModuleController extends Controller
{
    /**
     * Display a listing of course modules.
     */
    public function index(): JsonResponse
    {
        $modules = CourseModule::with('course')->ordered()->get();
        return $this->success($modules);
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
            'is_published' => 'boolean',
        ]);

        $module = CourseModule::create($validated);
        return $this->created($module, 'Course module created successfully');
    }

    /**
     * Display the specified course module.
     */
    public function show(string $id): JsonResponse
    {
        $module = CourseModule::with('course')->findOrFail($id);
        return $this->success($module);
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
            'is_published' => 'boolean',
        ]);

        $module->update($validated);
        return $this->success($module, 'Course module updated successfully');
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
        return $this->success($assignments);
    }

    /**
     * Get discussion threads for this module.
     */
    public function discussions(string $id): JsonResponse
    {
        $module = CourseModule::findOrFail($id);
        $threads = $module->discussionThreads()->recent()->get();
        return $this->success($threads);
    }
}