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
            'title' => $this->title,
            'content' => $this->content,
            'authorName' => $this->creator?->name ?? 'System',
            'timestamp' => $this->created_at->toIso8601String(),
            'category' => $this->category,
            'author_id' => $this->created_by,
            'course_id' => $this->course_id,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}