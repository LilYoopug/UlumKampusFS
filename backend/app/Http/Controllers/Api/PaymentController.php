<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Notification;

class PaymentController extends ApiController
{
    public function __construct()
    {
        // Set konfigurasi Midtrans saat controller diinisialisasi
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Membuat transaksi dan mendapatkan Snap Token.
     */
    public function createTransaction(Request $request)
    {
        // Validasi request (contoh sederhana)
        $request->validate([
            'order_id' => 'required|string|unique:orders,id', // Pastikan order_id unik
            'amount' => 'required|numeric|min:1000',
        ]);

        $user = $request->user();

        $params = [
            'transaction_details' => [
                'order_id' => $request->order_id,
                'gross_amount' => $request->amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return $this->success(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Memeriksa status transaksi dari Midtrans (Polling).
     */
    public function checkTransactionStatus(Request $request, string $order_id): \Illuminate\Http\JsonResponse
    {
        // TODO: Sebaiknya ada validasi untuk memastikan user yang sedang login
        // adalah pemilik dari order_id yang diminta.
        // Contoh:
        // $order = Order::where('id', $order_id)->where('user_id', $request->user()->id)->firstOrFail();

        try {
            $status = Transaction::status($order_id);

            // Kirimkan status yang relevan ke frontend
            $data = [
                'order_id' => $status['order_id'] ?? $status->order_id,
                'gross_amount' => $status['gross_amount'] ?? $status->gross_amount,
                'transaction_status' => $status['transaction_status'] ?? $status->transaction_status,
                'payment_type' => $status['payment_type'] ?? $status->payment_type ?? null,
                'transaction_time' => $status['transaction_time'] ?? $status->transaction_time,
                'expiry_time' => $status['expiry_time'] ?? $status->expiry_time ?? null,
            ];

            return $this->success($data);
        } catch (\Exception $e) {
            // Handle jika transaksi tidak ditemukan atau error lainnya
            return $this->error('Transaction not found or an error occurred.', 404);
        }
    }

    /**
     * Menangani notifikasi dari Midtrans (webhook).
     */
    public function notificationHandler(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Get raw POST data for Midtrans notification
            $input = file_get_contents('php://input');
            $notification = new Notification($input);

            $transactionStatus = $notification->transaction_status ?? $notification['transaction_status'];
            $orderId = $notification->order_id ?? $notification['order_id'];
            $fraudStatus = $notification->fraud_status ?? $notification['fraud_status'];

            // Logika untuk memproses status transaksi
            // Contoh: Cari order berdasarkan $orderId di database Anda
            // $order = Order::find($orderId);
            // if (!$order) {
            //     return $this->error('Order not found', 404);
            // }

            // Lakukan verifikasi signature key untuk keamanan
            $statusCode = $notification->status_code ?? $notification['status_code'];
            $grossAmount = $notification->gross_amount ?? $notification['gross_amount'];
            $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . config('midtrans.server_key'));
            $notificationSignature = $notification->signature_key ?? $notification['signature_key'];
            if ($notificationSignature != $signatureKey) {
                return $this->error('Invalid signature', 403);
            }

            // Gunakan switch case untuk penanganan status yang lebih bersih
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    // TODO: Update status order menjadi 'paid' atau 'processing'
                    // $order->update(['payment_status' => 'paid']);
                }
            } elseif ($transactionStatus == 'settlement') {
                // TODO: Update status order menjadi 'paid' atau 'completed'
                // $order->update(['payment_status' => 'paid']);
            } elseif ($transactionStatus == 'pending') {
                // TODO: Update status order menjadi 'pending'
                // $order->update(['payment_status' => 'pending']);
            } elseif ($transactionStatus == 'deny') {
                // TODO: Update status order menjadi 'denied'
                // $order->update(['payment_status' => 'denied']);
            } elseif ($transactionStatus == 'expire') {
                // TODO: Update status order menjadi 'expired'
                // $order->update(['payment_status' => 'expired']);
            } elseif ($transactionStatus == 'cancel') {
                // TODO: Update status order menjadi 'cancelled'
                // $order->update(['payment_status' => 'cancelled']);
            }

            // Beri respons OK ke Midtrans agar tidak mengirim notifikasi berulang
            return $this->success(['status' => 'ok']);

        } catch (\Exception $e) {
            // Tangani jika ada error saat memproses notifikasi
            return $this->error($e->getMessage(), 500);
        }
    }
}
