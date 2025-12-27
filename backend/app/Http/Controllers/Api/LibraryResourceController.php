<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LibraryResourceRequest;
use App\Http\Resources\LibraryResourceResource;
use App\Models\LibraryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LibraryResourceController extends Controller
{
    /**
     * Display a listing of library resources.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LibraryResource::query();

        // Filter by resource type
        if ($request->has('resource_type') && $request->resource_type) {
            $query->where('resource_type', $request->resource_type);
        }

        // Filter by access level
        if ($request->has('access_level') && $request->access_level) {
            $query->where('access_level', $request->access_level);
        }

        // Filter by course
        if ($request->has('course_id') && $request->course_id) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by faculty
        if ($request->has('faculty_id') && $request->faculty_id) {
            $query->where('faculty_id', $request->faculty_id);
        }

        // Filter by publication year
        if ($request->has('publication_year') && $request->publication_year) {
            $query->where('publication_year', $request->publication_year);
        }

        // Search in title, description, author, publisher
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('author', 'like', '%' . $request->search . '%')
                  ->orWhere('publisher', 'like', '%' . $request->search . '%')
                  ->orWhere('tags', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by tags
        if ($request->has('tag') && $request->tag) {
            $query->where('tags', 'like', '%' . $request->tag . '%');
        }

        // Only show published resources for non-admin/faculty
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'faculty'])) {
            $query->published();
        }

        $resources = $query->with(['course', 'faculty', 'creator'])
            ->ordered()
            ->latest('created_at')
            ->get();

        return $this->success($resources);
    }

    /**
     * Store a newly created library resource.
     */
    public function store(LibraryResourceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $validated['published_at'] = $validated['is_published'] ?? false ? now() : null;

        $resource = LibraryResource::create($validated);
        return $this->created(new LibraryResourceResource($resource), 'Library resource created successfully');
    }

    /**
     * Display the specified library resource.
     */
    public function show(string $id): JsonResponse
    {
        $resource = LibraryResource::with(['course', 'faculty', 'creator'])->findOrFail($id);
        $resource->increment('view_count');
        return $this->success(new LibraryResourceResource($resource));
    }

    /**
     * Update the specified library resource.
     */
    public function update(LibraryResourceRequest $request, string $id): JsonResponse
    {
        $resource = LibraryResource::findOrFail($id);
        $validated = $request->validated();

        if (isset($validated['is_published']) && $validated['is_published'] && !$resource->is_published) {
            $validated['published_at'] = now();
        }

        $resource->update($validated);
        return $this->success(new LibraryResourceResource($resource), 'Library resource updated successfully');
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
        return $this->success(new LibraryResourceResource($resource), 'Library resource published successfully');
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
        return $this->success(new LibraryResourceResource($resource), 'Library resource unpublished');
    }
}