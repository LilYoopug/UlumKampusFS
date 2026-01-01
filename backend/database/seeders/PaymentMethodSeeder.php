<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'id' => 1,
                'method_id' => 'bank_transfer',
                'name_key' => 'Transfer Bank',
                'icon' => '🏦',
                'is_active' => true,
            ],
            [
                'id' => 2,
                'method_id' => 'credit_card',
                'name_key' => 'Kartu Kredit',
                'icon' => '💳',
                'is_active' => true,
            ],
            [
                'id' => 3,
                'method_id' => 'e_wallet',
                'name_key' => 'E-Wallet',
                'icon' => '📱',
                'is_active' => true,
            ],
            [
                'id' => 4,
                'method_id' => 'virtual_account',
                'name_key' => 'Virtual Account',
                'icon' => '📋',
                'is_active' => true,
            ],
        ];

        // Update or insert payment methods
        foreach ($paymentMethods as $method) {
            DB::table('payment_methods')
                ->updateOrInsert(
                    ['id' => $method['id']],
                    array_merge($method, ['updated_at' => now()])
                );
        }
    }
}
