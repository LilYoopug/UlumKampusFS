<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\LibraryResourceRequest;
use App\Http\Resources\LibraryResourceResource;
use App\Models\LibraryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LibraryResourceController extends ApiController
{
    /**
     * Display a listing of library resources.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LibraryResource::query();

        // Filter by resource type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
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

        // Filter by publication year (support both 'publication_year' and 'year')
        $year = $request->input('publication_year') ?? $request->input('year');
        if ($year) {
            $query->where('publication_year', $year);
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

        // Only show published resources for non-admin/faculty/dosen/prodi_admin
        $user = auth()->user();
        $allowedRoles = ['admin', 'faculty', 'dosen', 'prodi_admin', 'super_admin'];
        if (!$user || !in_array($user->role, $allowedRoles)) {
            $query->published();
        }

        $resources = $query->with(['course', 'faculty', 'creator'])
            ->ordered()
            ->latest('created_at')
            ->get();

        return $this->success(LibraryResourceResource::collection($resources));
    }

    /**
     * Store a newly created library resource.
     */
    public function store(LibraryResourceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Handle camelCase to snake_case field mappings from frontend
        $allInput = $request->all();
        $camelCaseFields = [
            'sourceUrl' => 'source_url',
            'coverUrl' => 'cover_url',
            'sourceType' => 'source_type',
            'fileUrl' => 'file_url',
        ];
        
        foreach ($camelCaseFields as $camelCase => $snakeCase) {
            if (isset($allInput[$camelCase]) && $allInput[$camelCase] !== null) {
                $validated[$snakeCase] = $allInput[$camelCase];
            } elseif (isset($allInput[$snakeCase]) && $allInput[$snakeCase] !== null && !isset($validated[$snakeCase])) {
                $validated[$snakeCase] = $allInput[$snakeCase];
            }
        }
        
        // Set created_by if not already set
        if (!isset($validated['created_by'])) {
            $validated['created_by'] = auth()->id();
        }
        
        // Set published_at if is_published is true
        if (isset($validated['is_published']) && $validated['is_published']) {
            $validated['published_at'] = now();
        }

        // Generate ID - this is required since the model has $incrementing = false
        $count = LibraryResource::withTrashed()->count();
        $validated['id'] = 'lib' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

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
        
        // Get all validated data
        $validated = $request->validated();
        
        // Get the raw input for camelCase fields that may have been sent from frontend
        // These need to be explicitly handled since validated() may not include them
        $camelCaseFields = [
            'sourceUrl' => 'source_url',
            'coverUrl' => 'cover_url',
            'sourceType' => 'source_type',
            'fileUrl' => 'file_url',
        ];
        
        // Get all request data for debugging and handling camelCase fields
        $allInput = $request->all();
        
        foreach ($camelCaseFields as $camelCase => $snakeCase) {
            // Check if camelCase version exists in raw input
            if (isset($allInput[$camelCase]) && $allInput[$camelCase] !== null) {
                $validated[$snakeCase] = $allInput[$camelCase];
            }
            // Also check if snake_case version exists in raw input (from prepareForValidation merge)
            elseif (isset($allInput[$snakeCase]) && $allInput[$snakeCase] !== null && !isset($validated[$snakeCase])) {
                $validated[$snakeCase] = $allInput[$snakeCase];
            }
        }

        if (isset($validated['is_published']) && $validated['is_published'] && !$resource->is_published) {
            $validated['published_at'] = now();
        }

        // Filter out null values to prevent overwriting with null
        $updateData = array_filter($validated, function($value) {
            return $value !== null;
        });

        $resource->update($updateData);
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
        $resource = LibraryResource::with(['course', 'faculty', 'creator'])->findOrFail($id);
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
