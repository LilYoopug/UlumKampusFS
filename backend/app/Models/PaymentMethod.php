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

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class, 'payment_method_id', 'method_id');
    }
}
