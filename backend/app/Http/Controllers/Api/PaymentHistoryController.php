<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PaymentHistoryResource;
use App\Models\PaymentHistory;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentHistoryController extends ApiController
{
    /**
     * Display a listing of payment histories.
     */
    public function index(): JsonResponse
    {
        $paymentHistories = PaymentHistory::with(['user', 'paymentMethod'])->get();
        return $this->success(PaymentHistoryResource::collection($paymentHistories), 'Payment histories retrieved successfully');
    }

    /**
     * Display the specified payment history.
     */
    public function show(string $id): JsonResponse
    {
        $paymentHistory = PaymentHistory::with(['user', 'paymentMethod'])->findOrFail($id);
        return $this->success(new PaymentHistoryResource($paymentHistory), 'Payment history retrieved successfully');
    }

    /**
     * Store a newly created payment history.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'history_id' => 'required|string|unique:payment_histories,history_id',
            'title' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'status' => 'required|in:pending,paid,failed,refunded',
            'payment_method_id' => 'required|exists:payment_methods,method_id',
            'user_id' => 'required|exists:users,id',
        ]);

        $paymentHistory = PaymentHistory::create($validated);
        return $this->created(new PaymentHistoryResource($paymentHistory), 'Payment history created successfully');
    }

    /**
     * Update the specified payment history.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $paymentHistory = PaymentHistory::findOrFail($id);

        $validated = $request->validate([
            'history_id' => 'sometimes|string|unique:payment_histories,history_id,' . $id,
            'title' => 'sometimes|string',
            'amount' => 'sometimes|numeric|min:0',
            'payment_date' => 'sometimes|date',
            'status' => 'sometimes|in:pending,paid,failed,refunded',
            'payment_method_id' => 'sometimes|exists:payment_methods,method_id',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        $paymentHistory->update($validated);
        return $this->success(new PaymentHistoryResource($paymentHistory), 'Payment history updated successfully');
    }

    /**
     * Remove the specified payment history.
     */
    public function destroy(string $id): JsonResponse
    {
        $paymentHistory = PaymentHistory::findOrFail($id);
        $paymentHistory->delete();
        return $this->noContent();
    }

    /**
     * Get payment histories for a specific user.
     */
    public function byUser(string $userId): JsonResponse
    {
        $paymentHistories = PaymentHistory::where('user_id', $userId)
            ->with(['paymentMethod'])
            ->get();
        return $this->success(PaymentHistoryResource::collection($paymentHistories), 'User payment histories retrieved successfully');
    }

    /**
     * Get payment histories by status.
     */
    public function byStatus(string $status): JsonResponse
    {
        $paymentHistories = PaymentHistory::where('status', $status)
            ->with(['user', 'paymentMethod'])
            ->get();
        return $this->success(PaymentHistoryResource::collection($paymentHistories), 'Payment histories by status retrieved successfully');
    }

    /**
     * Get payment histories by payment method.
     */
    public function byPaymentMethod(string $paymentMethodId): JsonResponse
    {
        $paymentHistories = PaymentHistory::where('payment_method_id', $paymentMethodId)
            ->with(['user'])
            ->get();
        return $this->success(PaymentHistoryResource::collection($paymentHistories), 'Payment histories by payment method retrieved successfully');
    }
}