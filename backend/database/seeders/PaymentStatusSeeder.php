<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentItem;
use App\Models\UserPaymentStatus;
use App\Models\User;
use Illuminate\Support\Facades\Date;

class PaymentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates diverse payment records for multiple users for management view
     */
    public function run(): void
    {
        $currentYear = Date::now()->year;
        $currentMonth = Date::now()->month;
        
        // Get all students (MABA and Mahasiswa)
        $students = User::whereIn('role', ['MABA', 'Mahasiswa'])->get();
        
        // Get all payment items
        $paymentItems = PaymentItem::all();
        
        // Set due dates based on current date
        $registrationDueDate = Date::create($currentYear, $currentMonth, 15)->format('Y-m-d');
        $semesterDueDate = Date::create($currentYear, $currentMonth + 1, 1)->format('Y-m-d');

        // Create payment status for each student with diverse statuses
        foreach ($students as $student) {
            foreach ($paymentItems as $item) {
                // Set appropriate due date based on payment type
                $dueDate = null;
                if (strpos($item->item_id, 'registration') !== false) {
                    $dueDate = $registrationDueDate;
                } elseif (strpos($item->item_id, 'semester') !== false) {
                    $dueDate = $semesterDueDate;
                }

                // Determine status based on student and item for demo diversity
                $status = 'unpaid';
                $paidAt = null;
                
                // Create diverse payment scenarios
                if ($student->email === 'ahmad.faris@student.ulumcampus.com') {
                    // Ahmad Faris - mostly paid
                    if ($item->item_id === 'registration') {
                        $status = 'paid';
                        $paidAt = Date::now()->subMonths(3);
                    } elseif ($item->item_id === 'semester') {
                        $status = 'paid';
                        $paidAt = Date::now()->subMonths(1);
                    } elseif ($item->item_id === 'exam') {
                        $status = 'paid';
                        $paidAt = Date::now()->subDays(20);
                    }
                } elseif ($student->email === 'siti.m@student.ulumcampus.com') {
                    // Siti Maryam - mixed status
                    if ($item->item_id === 'registration') {
                        $status = 'paid';
                        $paidAt = Date::now()->subMonths(3);
                    } elseif ($item->item_id === 'semester') {
                        $status = 'pending';
                        $paidAt = null;
                    } elseif ($item->item_id === 'exam') {
                        $status = 'unpaid';
                        $paidAt = null;
                    }
                } elseif ($student->email === 'abdullah@student.ulumcampus.com') {
                    // Abdullah - mostly unpaid
                    if ($item->item_id === 'registration') {
                        $status = 'paid';
                        $paidAt = Date::now()->subMonths(4);
                    } elseif ($item->item_id === 'semester') {
                        $status = 'unpaid';
                        $paidAt = null;
                    } elseif ($item->item_id === 'exam') {
                        $status = 'unpaid';
                        $paidAt = null;
                    }
                } elseif ($student->email === 'budi.santoso@maba.ulumcampus.com') {
                    // Budi (MABA) - new student
                    if ($item->item_id === 'registration') {
                        $status = 'pending';
                        $paidAt = null;
                    } elseif ($item->item_id === 'semester') {
                        $status = 'unpaid';
                        $paidAt = null;
                    } elseif ($item->item_id === 'exam') {
                        $status = 'paid';
                        $paidAt = Date::now()->subDays(5);
                    }
                } else {
                    // Other students - random status for variety
                    $random = rand(1, 10);
                    if ($random <= 4) {
                        $status = 'paid';
                        $paidAt = Date::now()->subDays(rand(1, 60));
                    } elseif ($random <= 6) {
                        $status = 'pending';
                        $paidAt = null;
                    } else {
                        $status = 'unpaid';
                        $paidAt = null;
                    }
                }

                UserPaymentStatus::updateOrCreate(
                    [
                        'user_id' => $student->id,
                        'payment_item_id' => $item->id,
                    ],
                    [
                        'status' => $status,
                        'due_date' => $dueDate,
                        'paid_at' => $paidAt,
                    ]
                );
            }
        }

        $this->command->info('Created payment statuses for ' . $students->count() . ' students with ' . $paymentItems->count() . ' payment items each.');
    }
}
