<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property int|null $faculty_id
 * @property int|null $major_id
 * @property string|null $student_id
 * @property float|null $gpa
 * @property int|null $enrollment_year
 * @property int|null $graduation_year
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $avatar_url
 * @property \Carbon\Carbon|null $created_at
 * @property string|null $bio
 * @property string|null $student_status
 * @property int $total_sks
 * @property array $badges
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon|null $updated_at
 */
class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'password' => null, // Don't expose password in response
            'avatarUrl' => $this->avatar_url,
            'role' => $this->role,
            'studentId' => $this->student_id,
            'joinDate' => $this->created_at?->toIso8601String(),
            'bio' => $this->bio,
            'studentStatus' => $this->student_status,
            'gpa' => $this->gpa,
            'totalSks' => $this->total_sks,
            'facultyId' => $this->faculty_id,
            'majorId' => $this->major_id,
            'badges' => $this->badges,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'remember_token' => null, // Don't expose remember token
            'phoneNumber' => $this->phone,
        ];
    }
}