<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LibraryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LibraryResourceController extends Controller
{
    /**
     * Display a listing of library resources.
     */
    public function index(): JsonResponse
    {
        $resources = LibraryResource::published()
            ->with(['course', 'faculty', 'creator'])
            ->ordered()
            ->get();
        return $this->success($resources);
    }

    /**
     * Store a newly created library resource.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'faculty_id' => 'nullable|exists:faculties,id',
            'created_by' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'resource_type' => 'nullable|in:document,video,audio,link,book,article,other',
            'access_level' => 'nullable|in:public,faculty,course',
            'file_url' => 'nullable|url|max:500',
            'file_type' => 'nullable|string|max:50',
            'file_size' => 'nullable|integer',
            'external_link' => 'nullable|url|max:500',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:50',
            'doi' => 'nullable|string|max:100',
            'publication_year' => 'nullable|integer|min:1000|max:2100',
            'tags' => 'nullable|string',
            'is_published' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['published_at'] = $validated['is_published'] ? now() : null;

        $resource = LibraryResource::create($validated);
        return $this->created($resource, 'Library resource created successfully');
    }

    /**
     * Display the specified library resource.
     */
    public function show(string $id): JsonResponse
    {
        $resource = LibraryResource::with(['course', 'faculty', 'creator'])->findOrFail($id);
        $resource->increment('view_count');
        return $this->success($resource);
    }

    /**
     * Update the specified library resource.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $resource = LibraryResource::findOrFail($id);

        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'faculty_id' => 'nullable|exists:faculties,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'resource_type' => 'nullable|in:document,video,audio,link,book,article,other',
            'access_level' => 'nullable|in:public,faculty,course',
            'file_url' => 'nullable|url|max:500',
            'file_type' => 'nullable|string|max:50',
            'file_size' => 'nullable|integer',
            'external_link' => 'nullable|url|max:500',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:50',
            'doi' => 'nullable|string|max:100',
            'publication_year' => 'nullable|integer|min:1000|max:2100',
            'tags' => 'nullable|string',
            'is_published' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        if (isset($validated['is_published']) && $validated['is_published'] && !$resource->is_published) {
            $validated['published_at'] = now();
        }

        $resource->update($validated);
        return $this->success($resource, 'Library resource updated successfully');
    }

    /**
     * Remove the specified library resource.
     */
    public function destroy(string $id): JsonResponse
    {
        $resource = LibraryResource::findOrFail($id);
        $resource->delete();
        return $this->noContent();
    }

    /**
     * Download a library resource.
     */
    public function download(string $id): JsonResponse
    {
        $resource = LibraryResource::findOrFail($id);
        $resource->increment('download_count');

        if ($resource->isFile()) {
            return $this->success([
                'url' => $resource->file_url,
                'file_name' => $resource->title,
                'file_type' => $resource->file_type,
            ], 'Download link generated');
        } elseif ($resource->isExternalLink()) {
            return $this->success([
                'url' => $resource->external_link,
            ], 'External link provided');
        }

        return $this->error('No downloadable resource available');
    }

    /**
     * Publish the library resource.
     */
    public function publish(string $id): JsonResponse
    {
        $resource = LibraryResource::findOrFail($id);
        $resource->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
        return $this->success($resource, 'Library resource published successfully');
    }

    /**
     * Unpublish the library resource.
     */
    public function unpublish(string $id): JsonResponse
    {
        $resource = LibraryResource::findOrFail($id);
        $resource->update([
            'is_published' => false,
            'published_at' => null,
        ]);
        return $this->success($resource, 'Library resource unpublished');
    }
}