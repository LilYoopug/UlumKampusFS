<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int|null $course_id
 * @property int|null $module_id
 * @property int $created_by
 * @property string $title
 * @property string $content
 * @property string $type
 * @property string $status
 * @property bool $is_pinned
 * @property bool $is_locked
 * @property int|null $locked_by
 * @property \Illuminate\Support\Carbon|null $locked_at
 * @property int|null $closed_by
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property int $view_count
 * @property int $reply_count
 * @property int|null $last_post_by
 * @property \Illuminate\Support\Carbon|null $last_post_at
 * @property string|null $attachment_url
 * @property string|null $attachment_type
 */
class DiscussionThreadResource extends JsonResource
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
            'module_id' => $this->module_id,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'status' => $this->status,
            'is_pinned' => $this->is_pinned,
            'is_locked' => $this->is_locked,
            'locked_at' => $this->locked_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'view_count' => $this->view_count,
            'reply_count' => $this->reply_count,
            'last_post_at' => $this->last_post_at?->toIso8601String(),
            'attachment_url' => $this->attachment_url,
            'attachment_type' => $this->attachment_type,
            'author' => new UserResource($this->whenLoaded('creator')),
            'course' => new CourseResource($this->whenLoaded('course')),
            'module' => new CourseModuleResource($this->whenLoaded('module')),
            'locked_by' => new UserResource($this->whenLoaded('lockedBy')),
            'closed_by' => new UserResource($this->whenLoaded('closedBy')),
            'last_post_by' => new UserResource($this->whenLoaded('lastPostBy')),
            'posts' => DiscussionPostResource::collection($this->whenLoaded('posts')),
            'solution' => new DiscussionPostResource($this->whenLoaded('solution')),
            'has_solution' => $this->when(isset($this->solution_id), fn () => $this->hasSolution()),
            'is_open_for_posting' => $this->isOpenForPosting(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}