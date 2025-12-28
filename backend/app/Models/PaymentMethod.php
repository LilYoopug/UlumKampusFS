<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'method_id',
        'name_key',
        'icon',
        'is_active',
    ];

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class, 'payment_method_id', 'method_id');
    }
}
