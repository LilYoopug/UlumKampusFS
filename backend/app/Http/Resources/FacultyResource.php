<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property string|null $dean_name
 * @property string|null $email
 * @property string|null $phone
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Major[] $majors
 */
class FacultyResource extends JsonResource
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
            'code' => $this->code,
            'description' => $this->description,
            'majors' => MajorResource::collection($this->whenLoaded('majors')),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}