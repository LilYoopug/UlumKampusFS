<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Dashboard Statistics Resource
 *
 * Transforms dashboard statistics data for different user roles
 * (student, faculty, prodi, management) into a consistent JSON format.
 */
class DashboardStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return is_array($this->resource) ? $this->resource : $this->resource->toArray();
    }
}