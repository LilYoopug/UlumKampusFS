<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Major extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'faculty_id',
        'name',
        'code',
        'description',
        'head_of_program',
        'email',
        'phone',
        'duration_years',
        'credit_hours',
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
            'duration_years' => 'integer',
            'credit_hours' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the faculty that owns this major.
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the users (students) that belong to this major.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the courses that belong to this major.
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Scope a query to only include active majors.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}