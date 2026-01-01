<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    protected $fillable = [
        'item_id',
        'title_key',
        'description_key',
        'title',
        'description',
        'amount',
        'status',
        'user_id',
        'due_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function userPaymentStatuses()
    {
        return $this->hasMany(UserPaymentStatus::class);
    }
}
