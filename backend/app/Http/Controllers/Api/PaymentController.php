<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Midtrans\Notification;

class PaymentController extends Controller
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
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Memeriksa status transaksi dari Midtrans (Polling).
     *
     * @param string $order_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTransactionStatus(Request $request, $order_id)
    {
        // TODO: Sebaiknya ada validasi untuk memastikan user yang sedang login
        // adalah pemilik dari order_id yang diminta.
        // Contoh:
        // $order = Order::where('id', $order_id)->where('user_id', $request->user()->id)->firstOrFail();

        try {
            $status = Transaction::status($order_id);

            // Kirimkan status yang relevan ke frontend
            return response()->json([
                'order_id' => $status->order_id,
                'gross_amount' => $status->gross_amount,
                'transaction_status' => $status->transaction_status,
                'payment_type' => $status->payment_type ?? null,
                'transaction_time' => $status->transaction_time,
                'expiry_time' => $status->expiry_time ?? null,
            ]);
        } catch (\Exception $e) {
            // Handle jika transaksi tidak ditemukan atau error lainnya
            return response()->json(['error' => 'Transaction not found or an error occurred.'], 404);
        }
    }

    /**
     * Menangani notifikasi dari Midtrans (webhook).
     */
    public function notificationHandler(Request $request)
    {
        try {
            $notification = new Notification($request->getContent());

            $transactionStatus = $notification->transaction_status;
            $orderId = $notification->order_id;
            $fraudStatus = $notification->fraud_status;

            // Logika untuk memproses status transaksi
            // Contoh: Cari order berdasarkan $orderId di database Anda
            // $order = Order::find($orderId);
            // if (!$order) {
            //     return response()->json(['error' => 'Order not found'], 404);
            // }

            // Lakukan verifikasi signature key untuk keamanan
            $signatureKey = hash('sha512', $orderId . $notification->status_code . $notification->gross_amount . config('midtrans.server_key'));
            if ($notification->signature_key != $signatureKey) {
                return response()->json(['error' => 'Invalid signature'], 403);
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
            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            // Tangani jika ada error saat memproses notifikasi
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
