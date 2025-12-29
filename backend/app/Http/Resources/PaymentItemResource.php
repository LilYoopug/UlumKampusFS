<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $item_id
 * @property string $title_key
 * @property float $amount
 * @property string $due_date
 * @property string $status
 * @property string $description_key
 */
class PaymentItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->item_id,
            'title' => $this->title_key,
            'amount' => $this->amount,
            'dueDate' => $this->due_date,
            'status' => $this->status,
            'description' => $this->description_key,
        ];
    }
}