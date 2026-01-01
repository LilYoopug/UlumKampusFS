<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentItem;
use App\Models\UserPaymentStatus;
use App\Models\User;
use Illuminate\Support\Facades\Date;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create global payment items (shown to everyone)
        $paymentItems = [
            [
                'item_id' => 'registration',
                'title_key' => 'administrasi_registration_title',
                'description_key' => 'administrasi_registration_desc',
                'amount' => 5000000,
            ],
            [
                'item_id' => 'semester',
                'title_key' => 'administrasi_semester_title',
                'description_key' => 'administrasi_semester_desc',
                'amount' => 3500000,
            ],
            [
                'item_id' => 'exam',
                'title_key' => 'administrasi_exam_title',
                'description_key' => 'administrasi_exam_desc',
                'amount' => 250000,
            ],
        ];

        foreach ($paymentItems as $item) {
            PaymentItem::updateOrCreate(
                ['item_id' => $item['item_id']],
                $item
            );
        }

        // Get all MABA and Mahasiswa users
        $students = User::whereIn('role', ['MABA', 'Mahasiswa'])->get();

        $currentYear = Date::now()->year;
        $currentMonth = Date::now()->month;
        
        // Set due dates based on current date
        $registrationDueDate = Date::create($currentYear, $currentMonth, 15)->format('Y-m-d');
        $semesterDueDate = Date::create($currentYear, $currentMonth + 1, 1)->format('Y-m-d');

        // Create payment status for each student
        foreach ($students as $student) {
            foreach ($paymentItems as $item) {
                $paymentItem = PaymentItem::where('item_id', $item['item_id'])->first();
                
                if ($paymentItem) {
                    // Set appropriate due date based on payment type
                    $dueDate = null;
                    if (strpos($paymentItem->item_id, 'registration') !== false) {
                        $dueDate = $registrationDueDate;
                    } elseif (strpos($paymentItem->item_id, 'semester') !== false) {
                        $dueDate = $semesterDueDate;
                    }

                    // Determine status (for demo purposes, MABA user has exam paid)
                    $status = 'unpaid';
                    $paidAt = null;
                    
                    if ($student->email === 'budi.santoso@example.com' && 
                        $paymentItem->item_id === 'exam') {
                        $status = 'paid';
                        $paidAt = Date::now()->subDays(5);
                    }

                    UserPaymentStatus::updateOrCreate(
                        [
                            'user_id' => $student->id,
                            'payment_item_id' => $paymentItem->id,
                        ],
                        [
                            'status' => $status,
                            'due_date' => $dueDate,
                            'paid_at' => $paidAt,
                        ]
                    );
                }
            }
        }
    }
}
