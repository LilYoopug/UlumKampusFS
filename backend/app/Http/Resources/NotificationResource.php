<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property string $message
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property string $priority
 * @property string|null $action_url
 * @property string|null $related_entity_type
 * @property int|null $related_entity_id
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property bool $is_sent
 * @property \Illuminate\Support\Carbon|null $sent_at
 */
class NotificationResource extends JsonResource
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
            'user_id' => $this->user_id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at?->toIso8601String(),
            'priority' => $this->priority,
            'action_url' => $this->action_url,
            'related_entity_type' => $this->related_entity_type,
            'related_entity_id' => $this->related_entity_id,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'is_sent' => $this->is_sent,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'user' => new UserResource($this->whenLoaded('user')),
            'is_expired' => $this->when(isset($this->expires_at), fn () => $this->isExpired()),
            'is_active' => $this->when(isset($this->expires_at), fn () => $this->isActive()),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}