<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $thread_id
 * @property int|null $parent_id
 * @property int $user_id
 * @property string $content
 * @property bool $is_edited
 * @property \Illuminate\Support\Carbon|null $edited_at
 * @property int|null $edited_by
 * @property bool $is_solution
 * @property int|null $marked_as_solution_by
 * @property \Illuminate\Support\Carbon|null $marked_as_solution_at
 * @property int $likes_count
 * @property string|null $attachment_url
 * @property string|null $attachment_type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\User|null $editedBy
 * @property-read \App\Models\User|null $markedAsSolutionBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DiscussionPost[] $replies
 */
class DiscussionPostResource extends JsonResource
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
            'thread_id' => $this->thread_id,
            'parent_id' => $this->parent_id,
            'content' => $this->content,
            'is_edited' => $this->is_edited,
            'edited_at' => $this->edited_at?->toIso8601String(),
            'is_solution' => $this->is_solution,
            'marked_as_solution_at' => $this->marked_as_solution_at?->toIso8601String(),
            'likes_count' => $this->likes_count,
            'attachment_url' => $this->attachment_url,
            'attachment_type' => $this->attachment_type,
            'author' => new UserResource($this->whenLoaded('user')),
            'edited_by' => new UserResource($this->whenLoaded('editedBy')),
            'marked_as_solution_by' => new UserResource($this->whenLoaded('markedAsSolutionBy')),
            'replies' => DiscussionPostResource::collection($this->whenLoaded('replies')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}