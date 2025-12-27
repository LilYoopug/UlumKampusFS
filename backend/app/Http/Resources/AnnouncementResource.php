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
 * @property string $content
 * @property string|null $category
 * @property string|null $target_audience
 * @property string|null $priority
 * @property bool $is_published
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property bool $allow_comments
 * @property int $view_count
 * @property string|null $attachment_url
 * @property string|null $attachment_type
 * @property int|null $order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class AnnouncementResource extends JsonResource
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
            'content' => $this->content,
            'category' => $this->category,
            'target_audience' => $this->target_audience,
            'priority' => $this->priority,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'allow_comments' => $this->allow_comments,
            'view_count' => $this->view_count,
            'attachment_url' => $this->attachment_url,
            'attachment_type' => $this->attachment_type,
            'order' => $this->order,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Relationships
            'course' => new CourseResource($this->whenLoaded('course')),
            'faculty' => new FacultyResource($this->whenLoaded('faculty')),
            'creator' => new UserResource($this->whenLoaded('creator')),

            // Computed properties
            'is_expired' => $this->isExpired(),
            'is_active' => $this->isActive(),
        ];
    }
}