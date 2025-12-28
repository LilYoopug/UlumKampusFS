<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $fillable = [
        'history_id',
        'title',
        'amount',
        'payment_date',
        'status',
        'payment_method_id',
        'user_id',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'method_id');
    }
}
