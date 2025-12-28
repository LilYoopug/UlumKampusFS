<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faculty extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'dean_name',
        'email',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the majors that belong to this faculty.
     */
    public function majors()
    {
        return $this->hasMany(Major::class);
    }

    /**
     * Get the users (students and faculty) that belong to this faculty.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the courses that belong to this faculty.
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Get the announcements for this faculty.
     */
    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * Get the library resources for this faculty.
     */
    public function libraryResources()
    {
        return $this->hasMany(LibraryResource::class);
    }

    /**
     * Scope a query to only include active faculties.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the createdAt value (alias for created_at for frontend compatibility).
     */
    protected function getCreatedAtAttribute(): ?string
    {
        return $this->attributes['created_at'] ?? null;
    }
}