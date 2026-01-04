<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property int $faculty_id
 * @property string $name
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
            'id' => $this->code,
            'code' => $this->code,
            'name' => $this->name,
        ];
    }
}