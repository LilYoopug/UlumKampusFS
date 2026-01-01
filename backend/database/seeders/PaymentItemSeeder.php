<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentItem;

class PaymentItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create global payment items (available to everyone)
        $paymentItems = [
            [
                'item_id' => 'registration',
                'title' => 'Biaya Pendaftaran',
                'description' => 'Biaya pendaftaran awal untuk mahasiswa baru',
                'amount' => 5000000,
            ],
            [
                'item_id' => 'semester',
                'title' => 'Biaya Semester',
                'description' => 'Biaya per semester untuk perkuliahan',
                'amount' => 3500000,
            ],
            [
                'item_id' => 'exam',
                'title' => 'Biaya Ujian',
                'description' => 'Biaya ujian semester',
                'amount' => 250000,
            ],
        ];

        foreach ($paymentItems as $item) {
            PaymentItem::updateOrCreate(
                ['item_id' => $item['item_id']],
                $item
            );
        }
    }
}
