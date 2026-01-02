<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for transforming Notification model to frontend format
 * 
 * Frontend expects:
 * - id: string
 * - type: 'forum' | 'grade' | 'assignment' | 'announcement'
 * - messageKey: string (translation key)
 * - context: string
 * - timestamp: ISO date string
 * - isRead: boolean
 * - link: { page: string, params: object }
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
        // The 'context' field in database is stored as JSON array, but frontend expects string
        // We need to handle both cases
        $context = $this->context;
        if (is_array($context)) {
            // If stored as array with 'text' key
            $context = $context['text'] ?? ($context[0] ?? '');
        }

        // The 'link' field is stored as JSON object with 'page' and 'params'
        $link = $this->link;
        if (!is_array($link)) {
            $link = ['page' => 'dashboard', 'params' => []];
        }

        // Get messageKey from title (we store messageKey in title field)
        $messageKey = $this->title;
        
        // If title is a regular title (not a translation key), convert it to a message key
        if ($messageKey && !str_starts_with($messageKey, 'notification_')) {
            // Fallback: use type-based message key
            $messageKey = 'notification_' . $this->type;
        }

        return [
            'id' => $this->id,
            'type' => $this->type,
            'messageKey' => $messageKey,
            'context' => $context,
            'timestamp' => $this->created_at?->toISOString(),
            'isRead' => (bool) $this->is_read,
            'link' => $link,
        ];
    }
}
