<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPaymentStatus extends Model
{
    protected $fillable = [
        'user_id',
        'payment_item_id',
        'status',
        'due_date',
        'paid_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentItem()
    {
        return $this->belongsTo(PaymentItem::class);
    }
}
