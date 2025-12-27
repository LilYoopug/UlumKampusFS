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
            'role' => $this->role,
            'faculty_id' => $this->faculty_id,
            'major_id' => $this->major_id,
            'student_id' => $this->student_id,
            'gpa' => $this->gpa,
            'enrollment_year' => $this->enrollment_year,
            'graduation_year' => $this->graduation_year,
            'phone' => $this->phone,
            'address' => $this->address,
            'faculty' => new FacultyResource($this->whenLoaded('faculty')),
            'major' => new MajorResource($this->whenLoaded('major')),
        ];
    }
}