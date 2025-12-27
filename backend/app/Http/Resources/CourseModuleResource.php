<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $course_id
 * @property string $title
 * @property string|null $description
 * @property string|null $content
 * @property string|null $video_url
 * @property string|null $document_url
 * @property int $order
 * @property bool $is_published
 * @property \Illuminate\Support\Carbon|null $published_at
 */
class CourseModuleResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'video_url' => $this->video_url,
            'document_url' => $this->document_url,
            'order' => $this->order,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toIso8601String(),
            'course' => new CourseResource($this->whenLoaded('course')),
            'assignments_count' => $this->whenCounted('assignments'),
        ];
    }
}