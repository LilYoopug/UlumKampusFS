<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $faculty_id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property string|null $head_of_program
 * @property string|null $email
 * @property string|null $phone
 * @property int|null $duration_years
 * @property int|null $credit_hours
 * @property bool $is_active
 */
class MajorResource extends JsonResource
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
            'faculty_id' => $this->faculty_id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'head_of_program' => $this->head_of_program,
            'email' => $this->email,
            'phone' => $this->phone,
            'duration_years' => $this->duration_years,
            'credit_hours' => $this->credit_hours,
            'is_active' => $this->is_active,
            'faculty' => new FacultyResource($this->whenLoaded('faculty')),
            'courses' => CourseResource::collection($this->whenLoaded('courses')),
        ];
    }
}