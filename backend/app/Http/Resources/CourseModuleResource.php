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
            'title' => $this->title,
            'type' => $this->type ?? 'video',
            'description' => $this->description,
            'duration' => $this->duration,
            'resourceUrl' => $this->video_url ?? $this->document_url,
            'captionsUrl' => $this->captions_url,
            'attachmentUrl' => $this->attachment_url,
            'startTime' => $this->start_time?->toIso8601String(),
            'liveUrl' => $this->live_url,
            'order' => $this->order,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toIso8601String(),
            'course' => new CourseResource($this->whenLoaded('course')),
            'assignments_count' => $this->whenCounted('assignments'),
        ];
    }
}
