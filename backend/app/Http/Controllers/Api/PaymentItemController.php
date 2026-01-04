<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PaymentItemResource;
use App\Models\PaymentItem;
use App\Models\UserPaymentStatus;
use App\Models\PaymentHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class PaymentItemController extends ApiController
{
    /**
     * Display a listing of payment items.
     */
    public function index(): JsonResponse
    {
        $paymentItems = PaymentItem::all();
        return $this->success(PaymentItemResource::collection($paymentItems), 'Payment items retrieved successfully');
    }

    /**
     * Display the specified payment item.
     */
    public function show(string $id): JsonResponse
    {
        $paymentItem = PaymentItem::findOrFail($id);
        return $this->success(new PaymentItemResource($paymentItem), 'Payment item retrieved successfully');
    }

    /**
     * Store a newly created payment item.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|string|unique:payment_items,item_id',
            'title_key' => 'required|string',
            'description_key' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'due_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
        ]);

        $paymentItem = PaymentItem::create($validated);
        return $this->created(new PaymentItemResource($paymentItem), 'Payment item created successfully');
    }

    /**
     * Update the specified payment item.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $paymentItem = PaymentItem::findOrFail($id);

        $validated = $request->validate([
            'item_id' => 'sometimes|string|unique:payment_items,item_id,' . $id,
            'title_key' => 'sometimes|string',
            'description_key' => 'nullable|string',
            'amount' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:pending,paid,overdue,cancelled',
            'due_date' => 'sometimes|date',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        $paymentItem->update($validated);
        return $this->success(new PaymentItemResource($paymentItem), 'Payment item updated successfully');
    }

    /**
     * Remove the specified payment item.
     */
    public function destroy(string $id): JsonResponse
    {
        $paymentItem = PaymentItem::findOrFail($id);
        $paymentItem->delete();
        return $this->noContent();
    }

    /**
     * Get payment items for a specific user.
     */
    public function byUser(string $userId): JsonResponse
    {
        $paymentItems = PaymentItem::where('user_id', $userId)->get();
        return $this->success(PaymentItemResource::collection($paymentItems), 'User payment items retrieved successfully');
    }

    /**
     * Get payment items by status.
     */
    public function byStatus(string $status): JsonResponse
    {
        $paymentItems = PaymentItem::where('status', $status)->get();
        return $this->success(PaymentItemResource::collection($paymentItems), 'Payment items by status retrieved successfully');
    }

    /**
     * Get payment items with status for the authenticated user.
     */
    public function myPayments(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->unauthorized('User not authenticated');
        }

        // Get all payment items with their status for this user
        $paymentItemsWithStatus = $user->getPaymentItemsWithStatus();
        
        return $this->success($paymentItemsWithStatus, 'User payment items with status retrieved successfully');
    }

    /**
     * Process a payment for a specific payment item.
     */
    public function makePayment(Request $request, string $paymentItemId): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->unauthorized('User not authenticated');
        }

        $validated = $request->validate([
            'payment_method' => 'required|string',
        ]);

        $paymentItem = PaymentItem::findOrFail($paymentItemId);
        
        // Check if payment already exists
        $existingPayment = UserPaymentStatus::where('user_id', $user->id)
            ->where('payment_item_id', $paymentItemId)
            ->first();

        if ($existingPayment && $existingPayment->status === 'paid') {
            return $this->error('Payment already completed', 400);
        }

        // Create or update payment status
        $paymentStatus = UserPaymentStatus::updateOrCreate(
            [
                'user_id' => $user->id,
                'payment_item_id' => $paymentItemId,
            ],
            [
                'status' => 'paid',
                'paid_at' => Date::now(),
            ]
        );

        // Create payment history record
        // Use title if available, fallback to title_key
        $paymentTitle = $paymentItem->title ?? $paymentItem->title_key ?? 'Payment';

        PaymentHistory::create([
            'history_id' => 'PAY-' . Date::now()->format('YmdHis') . '-' . $user->id,
            'user_id' => $user->id,
            'payment_method_id' => $validated['payment_method'],
            'amount' => $paymentItem->amount,
            'status' => 'completed',
            'payment_date' => Date::now(),
            'title' => $paymentTitle,
        ]);

        return $this->success([
            'payment_status' => $paymentStatus,
            'payment_item' => $paymentItem,
        ], 'Payment processed successfully');
    }
}
