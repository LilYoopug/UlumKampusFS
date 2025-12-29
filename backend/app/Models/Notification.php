<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Carbon\Carbon|null $expires_at
 */
class Notification extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'is_read',
        'read_at',
        'priority',
        'action_url',
        'link',
        'context',
        'related_entity_type',
        'related_entity_id',
        'expires_at',
        'is_sent',
        'sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_sent' => 'boolean',
            'sent_at' => 'datetime',
            'link' => 'array',
            'context' => 'array',
        ];
    }

    /**
     * Get the user that owns this notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include active (not expired) notifications.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope a query to get urgent notifications.
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Scope a query to order by newest first.
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Check if the notification is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if the notification is active (not expired).
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Mark the notification as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'is_sent' => true,
            'sent_at' => now(),
        ]);
    }

    /**
     * Get the isRead value (alias for is_read for frontend compatibility).
     */
    protected function getIsReadAttribute(): bool
    {
        return (bool) ($this->attributes['is_read'] ?? false);
    }

    /**
     * Set the isRead value (alias for is_read for frontend compatibility).
     */
    protected function setIsReadAttribute(bool $value): void
    {
        $this->attributes['is_read'] = $value;
    }

    /**
     * Get the timestamp value (alias for created_at for frontend compatibility).
     */
    protected function getTimestampAttribute(): ?string
    {
        return $this->attributes['created_at'] ?? null;
    }

    /**
     * Set the timestamp value (alias for created_at for frontend compatibility).
     */
    protected function setTimestampAttribute(?string $value): void
    {
        $this->attributes['created_at'] = $value;
    }

    /**
     * Get the userId value (alias for user_id for frontend compatibility).
     */
    protected function getUserIdAttribute(): ?string
    {
        return $this->attributes['user_id'] ?? null;
    }

    /**
     * Set the userId value (alias for user_id for frontend compatibility).
     */
    protected function setUserIdAttribute(?string $value): void
    {
        $this->attributes['user_id'] = $value;
    }
}