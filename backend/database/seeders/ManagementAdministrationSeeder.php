<?php

namespace Database\Seeders;

use App\Models\PaymentHistory;
use App\Models\PaymentItem;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class ManagementAdministrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure payment methods exist
        $this->seedPaymentMethods();

        // Then seed payment items for students
        $this->seedPaymentItems();

        // Finally seed some payment histories
        $this->seedPaymentHistories();
    }

    /**
     * Seed payment methods
     */
    private function seedPaymentMethods(): void
    {
        $methods = [
            [
                'method_id' => 'bank_transfer',
                'name_key' => 'Transfer Bank',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M3 10h18M12 3l9 7-9 7-9-7 9-7M6 10v11M9 10v11M12 10v11M15 10v11M18 10v11" /></svg>',
                'is_active' => true,
            ],
            [
                'method_id' => 'credit_card',
                'name_key' => 'Kartu Kredit',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="6" width="18" height="12" rx="2" ry="2" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18" /></svg>',
                'is_active' => true,
            ],
            [
                'method_id' => 'e_wallet',
                'name_key' => 'E-Wallet',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01" /></svg>',
                'is_active' => true,
            ],
            [
                'method_id' => 'virtual_account',
                'name_key' => 'Virtual Account',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>',
                'is_active' => true,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['method_id' => $method['method_id']],
                $method
            );
        }

        $this->command->info('Payment methods seeded successfully.');
    }

    /**
     * Seed payment items for all students
     */
    private function seedPaymentItems(): void
    {
        // Get all students
        $students = User::where('role', 'student')->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Skipping payment items seeding.');
            return;
        }

        // Payment types from dummy data
        $paymentTypes = [
            [
                'item_id' => 'registration',
                'title_key' => 'Biaya Pendaftaran',
                'description_key' => 'Biaya pendaftaran awal untuk mahasiswa baru',
                'amount' => 5000000,
            ],
            [
                'item_id' => 'semester',
                'title_key' => 'Biaya Semester',
                'description_key' => 'Biaya per semester untuk perkuliahan',
                'amount' => 3500000,
            ],
            [
                'item_id' => 'exam',
                'title_key' => 'Biaya Ujian',
                'description_key' => 'Biaya ujian semester',
                'amount' => 250000,
            ],
            [
                'item_id' => 'other',
                'title_key' => 'Biaya Lain-lain',
                'description_key' => 'Other academic fees',
                'amount' => 1000000,
            ],
        ];

        foreach ($students as $student) {
            foreach ($paymentTypes as $type) {
                // Check if payment item already exists for this student
                $existing = PaymentItem::where('user_id', $student->id)
                    ->where('title_key', $type['title_key'])
                    ->first();

                // Update or create payment item
                PaymentItem::updateOrCreate(
                    [
                        'item_id' => $type['item_id'] . '-' . $student->id,
                        'user_id' => $student->id,
                    ],
                    [
                        'title_key' => $type['title_key'],
                        'description_key' => $type['description_key'],
                        'amount' => $type['amount'],
                        'status' => 'unpaid',
                        'due_date' => now()->addMonths(2),
                    ]
                );
            }
        }

        $this->command->info('Payment items seeded successfully for ' . $students->count() . ' students.');
    }

    /**
     * Seed payment histories with some completed payments
     */
    private function seedPaymentHistories(): void
    {
        // Get some students and create payment history for them
        $students = User::where('role', 'student')->limit(10)->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Skipping payment histories seeding.');
            return;
        }

        $paymentNames = [
            'Biaya Pendaftaran',
            'Biaya Semester',
            'Biaya Ujian',
            'Biaya Lain-lain',
        ];

        $paymentMethods = ['bank_transfer', 'credit_card', 'e_wallet', 'virtual_account'];

        foreach ($students as $index => $student) {
            // Create 2-3 payment histories per student
            $numPayments = rand(2, 3);
            
            for ($i = 0; $i < $numPayments; $i++) {
                $paymentName = $paymentNames[array_rand($paymentNames)];
                $method = $paymentMethods[array_rand($paymentMethods)];
                
                // Find corresponding payment item to get amount
                $paymentItem = PaymentItem::where('user_id', $student->id)
                    ->where('title_key', $paymentName)
                    ->first();

                $amount = $paymentItem ? $paymentItem->amount : rand(250000, 5000000);
                
                PaymentHistory::create([
                    'history_id' => 'HIS-' . time() . '-' . $student->id . '-' . $i,
                    'title' => $paymentName,
                    'amount' => $amount,
                    'payment_date' => now()->subDays(rand(1, 30)),
                    'status' => 'completed',
                    'payment_method_id' => $method,
                    'user_id' => $student->id,
                ]);

                // Update payment item status to paid
                if ($paymentItem) {
                    $paymentItem->status = 'paid';
                    $paymentItem->save();
                }
            }
        }

        $this->command->info('Payment histories seeded successfully.');
    }
}
