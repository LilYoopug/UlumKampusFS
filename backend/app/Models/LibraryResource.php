<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryResource extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'faculty_id',
        'created_by',
        'title',
        'description',
        'type',
        'access_level',
        'file_url',
        'file_type',
        'file_size',
        'external_link',
        'cover_url',
        'source_type',
        'source_url',
        'author',
        'publisher',
        'isbn',
        'doi',
        'publication_year',
        'tags',
        'download_count',
        'view_count',
        'is_published',
        'published_at',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'publication_year' => 'integer',
            'download_count' => 'integer',
            'view_count' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'order' => 'integer',
        ];
    }

    /**
     * Get the course that owns this resource.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the faculty that owns this resource.
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the user who created this resource.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include published resources.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to filter by resource type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by access level.
     */
    public function scopeByAccessLevel($query, $level)
    {
        return $query->where('access_level', $level);
    }

    /**
     * Scope a query to order by order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get tags as an array.
     */
    public function getTagsArray(): array
    {
        return $this->tags ? array_map('trim', explode(',', $this->tags)) : [];
    }

    /**
     * Set tags from an array.
     */
    public function setTagsArray(array $tags): void
    {
        $this->tags = implode(',', array_filter(array_map('trim', $tags)));
    }

    /**
     * Increment the download count.
     */
    public function incrementDownloadCount(): int
    {
        $this->increment('download_count');
        return $this->download_count;
    }

    /**
     * Increment the view count.
     */
    public function incrementViewCount(): int
    {
        $this->increment('view_count');
        return $this->view_count;
    }

    /**
     * Check if the resource is a file (not external link).
     */
    public function isFile(): bool
    {
        return !empty($this->file_url);
    }

    /**
     * Check if the resource is an external link.
     */
    public function isExternalLink(): bool
    {
        return !empty($this->external_link);
    }

    /**
     * Get the coverUrl value (alias for file_url for frontend compatibility).
     */
    protected function getCoverUrlAttribute(): ?string
    {
        return $this->attributes['file_url'] ?? null;
    }

    /**
     * Set the coverUrl value (alias for file_url for frontend compatibility).
     */
    protected function setCoverUrlAttribute(?string $value): void
    {
        $this->attributes['file_url'] = $value;
    }

    /**
     * Get the sourceType value (alias for type for frontend compatibility).
     */
    protected function getSourceTypeAttribute(): ?string
    {
        // Map type to sourceType values: 'upload' | 'link' | 'embed'
        if ($this->isFile()) {
            return 'upload';
        } elseif ($this->isExternalLink()) {
            return 'link';
        }
        return $this->attributes['type'] ?? null;
    }

    /**
     * Get the sourceUrl value (alias for external_link for frontend compatibility).
     */
    protected function getSourceUrlAttribute(): ?string
    {
        return $this->attributes['external_link'] ?? null;
    }

    /**
     * Set the sourceUrl value (alias for external_link for frontend compatibility).
     */
    protected function setSourceUrlAttribute(?string $value): void
    {
        $this->attributes['external_link'] = $value;
    }

    /**
     * Get the year value (alias for publication_year for frontend compatibility).
     */
    protected function getYearAttribute(): ?int
    {
        return $this->attributes['publication_year'] ?? null;
    }

    /**
     * Set the year value (alias for publication_year for frontend compatibility).
     */
    protected function setYearAttribute(?int $value): void
    {
        $this->attributes['publication_year'] = $value;
    }
}