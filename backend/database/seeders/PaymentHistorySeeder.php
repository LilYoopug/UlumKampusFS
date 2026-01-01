<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserPaymentStatus;
use App\Models\User;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

class PaymentHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates payment history records for paid transactions
     */
    public function run(): void
    {
        // Get all paid payment statuses
        $paidStatuses = UserPaymentStatus::where('status', 'paid')->get();
        
        // Get payment methods
        $bankTransfer = PaymentMethod::where('method_id', 'bank_transfer')->first();
        $creditCard = PaymentMethod::where('method_id', 'credit_card')->first();
        $eWallet = PaymentMethod::where('method_id', 'e_wallet')->first();
        $virtualAccount = PaymentMethod::where('method_id', 'virtual_account')->first();
        
        $paymentMethods = [$bankTransfer, $creditCard, $eWallet, $virtualAccount];
        
        // Create payment history for each paid status
        foreach ($paidStatuses as $status) {
            // Skip if history already exists for this payment
            $existing = DB::table('payment_histories')
                ->where('user_id', $status->user_id)
                ->where('title', 'like', '%' . $status->paymentItem->item_id . '%')
                ->where('amount', $status->paymentItem->amount)
                ->first();
            
            if ($existing) {
                continue;
            }
            
            // Random payment method
            $method = $paymentMethods[array_rand($paymentMethods)];
            
            // Generate a unique transaction ID
            $transactionId = 'PAY-' . strtoupper(uniqid()) . '-' . rand(1000, 9999);
            
            // Get payment item title from database
            $title = $status->paymentItem->title ?? 'Pembayaran';
            
            // Insert payment history
            DB::table('payment_histories')->insert([
                'history_id' => $transactionId,
                'user_id' => $status->user_id,
                'title' => $title,
                'payment_method_id' => $method->method_id,
                'amount' => $status->paymentItem->amount,
                'status' => 'completed',
                'payment_date' => $status->paid_at,
                'created_at' => $status->paid_at,
                'updated_at' => now(),
            ]);
        }

        // Add some additional payment history for demo purposes
        $this->addDemoPaymentHistory();

        $this->command->info('Created payment history records for ' . $paidStatuses->count() . ' paid transactions.');
    }

    /**
     * Add additional demo payment history records
     */
    private function addDemoPaymentHistory(): void
    {
        $ahmad = User::where('email', 'ahmad.faris@student.ulumcampus.com')->first();
        $siti = User::where('email', 'siti.m@student.ulumcampus.com')->first();
        
        if (!$ahmad || !$siti) {
            return;
        }

        // Get payment methods
        $bankTransfer = PaymentMethod::where('method_id', 'bank_transfer')->first();
        $creditCard = PaymentMethod::where('method_id', 'credit_card')->first();
        $eWallet = PaymentMethod::where('method_id', 'e_wallet')->first();
        
        // Additional payment history for Ahmad (past semesters)
        $additionalPayments = [
            [
                'user' => $ahmad,
                'title' => 'Pembayaran Semester Ganjil 2024',
                'amount' => 3500000,
                'method' => $bankTransfer,
                'date' => Date::now()->subMonths(6),
            ],
            [
                'user' => $ahmad,
                'title' => 'Pembayaran Ujian Tengah Semester',
                'amount' => 250000,
                'method' => $eWallet,
                'date' => Date::now()->subMonths(4),
            ],
            [
                'user' => $siti,
                'title' => 'Pembayaran Semester Ganjil 2024',
                'amount' => 3500000,
                'method' => $creditCard,
                'date' => Date::now()->subMonths(6),
            ],
        ];

        foreach ($additionalPayments as $payment) {
            $transactionId = 'PAY-' . strtoupper(uniqid()) . '-' . rand(1000, 9999);
            
            DB::table('payment_histories')->insert([
                'history_id' => $transactionId,
                'user_id' => $payment['user']->id,
                'payment_method_id' => $payment['method']->method_id,
                'title' => $payment['title'],
                'amount' => $payment['amount'],
                'status' => 'completed',
                'payment_date' => $payment['date'],
                'created_at' => $payment['date'],
                'updated_at' => now(),
            ]);
        }
    }
}
