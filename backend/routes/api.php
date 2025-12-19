<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Di sini Anda dapat mendaftarkan rute API untuk aplikasi Anda. Rute-rute
| ini dimuat oleh RouteServiceProvider dan semuanya akan
| ditugaskan ke grup middleware "api".
|
*/

// Rute publik (tidak memerlukan otentikasi)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Rute webhook untuk notifikasi dari Midtrans (tidak perlu otentikasi)
Route::post('/payment/notification', [PaymentController::class, 'notificationHandler']);

// Rute yang dilindungi (memerlukan otentikasi via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);


    // Payment Routes
    Route::post('/payment/create-transaction', [PaymentController::class, 'createTransaction']);
    Route::get('/payment/status/{order_id}', [PaymentController::class, 'checkTransactionStatus']);

    // Rute khusus Admin
    Route::middleware('role:admin')->group(function() {
        Route::get('/admin/dashboard', fn() => response()->json(['message' => 'Welcome Admin!']));

        // Masukan Route Lainnya Disini 
    });
});
