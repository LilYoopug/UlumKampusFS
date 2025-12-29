<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PaymentItemResource;
use App\Models\PaymentItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
}