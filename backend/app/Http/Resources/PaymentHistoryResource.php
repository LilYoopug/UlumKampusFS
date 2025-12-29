<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $history_id
 * @property string $title
 * @property float $amount
 * @property string $payment_date
 * @property string $status
 * @property-read \App\Models\PaymentMethod|null $paymentMethod
 */
class PaymentHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->history_id,
            'title' => $this->title,
            'amount' => $this->amount,
            'date' => $this->payment_date,
            'status' => $this->status,
            'paymentMethod' => $this->paymentMethod?->name ?? 'Unknown',
        ];
    }
}