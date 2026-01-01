<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faculty extends Model
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
        'id',
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($faculty) {
            if (empty($faculty->id) && !empty($faculty->code)) {
                $faculty->id = strtolower(str_replace(' ', '-', trim($faculty->code)));
            }
        });
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

}
