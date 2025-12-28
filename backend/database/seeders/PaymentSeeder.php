<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
use App\Models\PaymentItem;
use App\Models\PaymentHistory;
use App\Models\User;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Create payment methods based on frontend constants
        $paymentMethods = [
            [
                'method_id' => 'bank_transfer',
                'name_key' => 'administrasi_payment_method_bank_transfer',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M3 21l18 0M12 3v18m-9-9l9-9 9 9" /></svg>',
                'is_active' => true,
            ],
            [
                'method_id' => 'credit_card',
                'name_key' => 'administrasi_payment_method_credit_card',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>',
                'is_active' => true,
            ],
            [
                'method_id' => 'e_wallet',
                'name_key' => 'administrasi_payment_method_e_wallet',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" /></svg>',
                'is_active' => true,
            ],
            [
                'method_id' => 'virtual_account',
                'name_key' => 'administrasi_payment_method_virtual_account',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>',
                'is_active' => true,
            ],
        ];

        foreach ($paymentMethods as $methodData) {
            PaymentMethod::updateOrCreate(
                ['method_id' => $methodData['method_id']],
                $methodData
            );
        }

        // Create payment items based on frontend constants
        $paymentItems = [
            [
                'item_id' => 'registration',
                'title_key' => 'administrasi_registration_title',
                'description_key' => 'administrasi_registration_desc',
                'amount' => 5000000,
                'status' => 'unpaid',
                'due_date' => '2024-09-15',
                'user_id' => User::where('email', 'ahmad.faris@student.ulumcampus.com')->first()?->id,
            ],
            [
                'item_id' => 'semester',
                'title_key' => 'administrasi_semester_title',
                'description_key' => 'administrasi_semester_desc',
                'amount' => 3500000,
                'status' => 'unpaid',
                'due_date' => '2024-09-30',
                'user_id' => User::where('email', 'ahmad.faris@student.ulumcampus.com')->first()?->id,
            ],
            [
                'item_id' => 'exam',
                'title_key' => 'administrasi_exam_title',
                'description_key' => 'administrasi_exam_desc',
                'amount' => 250000,
                'status' => 'paid',
                'user_id' => User::where('email', 'ahmad.faris@student.ulumcampus.com')->first()?->id,
            ],
        ];

        foreach ($paymentItems as $itemData) {
            PaymentItem::updateOrCreate(
                ['item_id' => $itemData['item_id']],
                $itemData
            );
        }

        // Create payment history based on frontend constants
        $paymentHistory = [
            [
                'history_id' => '1',
                'title' => 'Pembayaran Semester',
                'amount' => 3500000,
                'payment_date' => '2024-08-15 10:30:00',
                'status' => 'completed',
                'user_id' => User::where('email', 'ahmad.faris@student.ulumcampus.com')->first()?->id,
                'payment_method_id' => 'bank_transfer',
            ],
            [
                'history_id' => '2',
                'title' => 'Pembayaran Registrasi',
                'amount' => 5000000,
                'payment_date' => '2024-07-20 14:45:00',
                'status' => 'completed',
                'user_id' => User::where('email', 'ahmad.faris@student.ulumcampus.com')->first()?->id,
                'payment_method_id' => 'virtual_account',
            ],
        ];

        foreach ($paymentHistory as $historyData) {
            PaymentHistory::updateOrCreate(
                ['history_id' => $historyData['history_id']],
                $historyData
            );
        }
    }
}