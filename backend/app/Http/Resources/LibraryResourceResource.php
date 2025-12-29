<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int|null $course_id
 * @property int|null $faculty_id
 * @property int $created_by
 * @property string $title
 * @property string|null $description
 * @property string|null $resource_type
 * @property string|null $access_level
 * @property string|null $file_url
 * @property string|null $file_type
 * @property int|null $file_size
 * @property string|null $external_link
 * @property string|null $author
 * @property string|null $publisher
 * @property string|null $isbn
 * @property string|null $doi
 * @property int|null $publication_year
 * @property string|null $tags
 * @property int $download_count
 * @property int $view_count
 * @property bool $is_published
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int|null $order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class LibraryResourceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'faculty_id' => $this->faculty_id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'description' => $this->description,
            'resource_type' => $this->resource_type,
            'access_level' => $this->access_level,
            'file_url' => $this->file_url,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'external_link' => $this->external_link,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'isbn' => $this->isbn,
            'doi' => $this->doi,
            'publication_year' => $this->publication_year,
            'tags' => $this->tags,
            'download_count' => $this->download_count,
            'view_count' => $this->view_count,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toIso8601String(),
            'order' => $this->order,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Relationships
            'course' => new CourseResource($this->whenLoaded('course')),
            'faculty' => new FacultyResource($this->whenLoaded('faculty')),
            'creator' => new UserResource($this->whenLoaded('creator')),

            // Computed properties
            'tags_array' => $this->getTagsArray(),
            'is_file' => $this->isFile(),
            'is_external_link' => $this->isExternalLink(),
            'file_size_human' => $this->formatFileSize($this->file_size),
        ];
    }

    /**
     * Get tags as an array
     */
    public function getTagsArray(): array
    {
        if (!$this->tags) {
            return [];
        }

        // Assuming tags are stored as JSON or comma-separated string
        $decoded = json_decode($this->tags, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // If it's a comma-separated string, split it
        return array_map('trim', explode(',', $this->tags));
    }

    /**
     * Check if this is a file resource
     */
    public function isFile(): bool
    {
        return !empty($this->file_url) && empty($this->external_link);
    }

    /**
     * Check if this is an external link resource
     */
    public function isExternalLink(): bool
    {
        return !empty($this->external_link);
    }

    /**
     * Format file size to human-readable format.
     */
    private function formatFileSize(?int $bytes): ?string
    {
        if ($bytes === null) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}