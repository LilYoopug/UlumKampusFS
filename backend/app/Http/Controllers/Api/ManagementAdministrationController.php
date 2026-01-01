<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\PaymentHistory;
use App\Models\PaymentItem;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\UserPaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ManagementAdministrationController extends ApiController
{
    /**
     * Get overview statistics for administration dashboard.
     */
    public function overview(): JsonResponse
    {
        $totalStudents = User::where('role', 'student')->count();
        
        $totalPayments = PaymentHistory::where('status', 'completed')->sum('amount');
        $totalPaid = PaymentHistory::where('status', 'completed')->sum('amount');
        $totalUnpaid = PaymentItem::where('status', 'unpaid')->sum('amount');
        $pendingPayments = PaymentHistory::where('status', 'pending')->count();

        return $this->success([
            'total_students' => $totalStudents,
            'total_payments' => $totalPayments,
            'total_paid' => $totalPaid,
            'total_unpaid' => $totalUnpaid,
            'pending_payments' => $pendingPayments,
        ]);
    }

    /**
     * Get recent payments.
     */
    public function recentPayments(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        
        $payments = PaymentHistory::with(['user', 'paymentMethod'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($payment) {
                // Get the payment item title from database
                $paymentItem = PaymentItem::where('user_id', $payment->user_id)
                    ->where(function($query) use ($payment) {
                        $query->where('title', $payment->title)
                              ->orWhere('title_key', $payment->title);
                    })
                    ->first();
                
                return [
                    'id' => $payment->id,
                    'student' => $payment->user ? $payment->user->name : 'Unknown',
                    'type' => $paymentItem ? ($paymentItem->title ?? $paymentItem->title_key) : $payment->title,
                    'amount' => $payment->amount,
                    'date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : $payment->created_at->format('Y-m-d'),
                    'status' => $payment->status,
                ];
            });

        return $this->success($payments);
    }

    /**
     * Get payment methods with transaction counts.
     */
    public function paymentMethods(): JsonResponse
    {
        $methods = PaymentMethod::where('is_active', true)
            ->withCount(['paymentHistories' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->get()
            ->map(function ($method) {
                return [
                    'id' => $method->method_id,
                    'name' => $method->name_key,
                    'icon' => $method->icon,
                    'count' => $method->payment_histories_count,
                ];
            });

        return $this->success($methods);
    }

    /**
     * Get all students with payment status for payment management.
     */
    public function studentsPaymentStatus(Request $request): JsonResponse
    {
        $search = $request->input('search', '');

        $query = User::where('role', 'student');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $students = $query->get();

        $studentsData = $students->map(function ($student) {
            $paymentItems = PaymentItem::where('user_id', $student->id)->get();
            $paymentHistories = PaymentHistory::where('user_id', $student->id)->get();
            
            $totalAmount = $paymentItems->sum('amount');
            $unpaidAmount = $paymentItems->where('status', 'unpaid')->sum('amount');
            $latestTransaction = $paymentHistories->sortByDesc('created_at')->first();
            
            $allPaid = $paymentItems->every(function ($item) {
                return $item->status === 'paid';
            });
            
            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'total_amount' => $totalAmount,
                'unpaid_amount' => $unpaidAmount,
                'latest_transaction' => $latestTransaction ? $latestTransaction->payment_date?->format('Y-m-d') : null,
                'status' => $allPaid ? 'paid' : 'unpaid',
            ];
        });

        return $this->success($studentsData);
    }

    /**
     * Get detailed payment information for a specific student.
     */
    public function studentPaymentDetails(string $studentId): JsonResponse
    {
        $student = User::with(['paymentItems', 'paymentHistories.paymentMethod'])
            ->where('role', 'student')
            ->findOrFail($studentId);

        $totalAmount = $student->paymentItems->sum('amount');
        $latestTransaction = $student->paymentHistories->sortByDesc('created_at')->first();

        $paymentList = $student->paymentItems->map(function ($item) use ($student) {
            $history = $student->paymentHistories
                ->where('title', $item->title ?? $item->title_key)
                ->where('status', 'completed')
                ->first();
            
            return [
                'id' => $item->id,
                'type' => $item->title ?? $item->title_key,
                'description' => $item->description ?? $item->description_key,
                'amount' => $item->amount,
                'status' => $item->status,
                'date' => $history ? $history->payment_date?->format('Y-m-d') : null,
                'has_receipt' => $history !== null,
            ];
        });

        return $this->success([
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'total_amount' => $totalAmount,
            'latest_transaction' => $latestTransaction ? $latestTransaction->payment_date?->format('Y-m-d') : null,
            'status' => $student->paymentItems->every(fn($item) => $item->status === 'paid') ? 'paid' : 'unpaid',
            'payment_list' => $paymentList,
        ]);
    }

    /**
     * Update payment status for a specific payment item.
     */
    public function updatePaymentStatus(Request $request, string $studentId, int $paymentItemId): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:paid,unpaid,pending',
        ]);

        $student = User::where('role', 'student')->findOrFail($studentId);
        $paymentItem = PaymentItem::where('user_id', $studentId)->findOrFail($paymentItemId);

        $paymentItem->status = $request->status;
        $paymentItem->save();

        // If status is paid, create a payment history record
        if ($request->status === 'paid') {
            PaymentHistory::create([
                'history_id' => 'HIS-' . time() . '-' . $student->id,
                'title' => $paymentItem->title ?? $paymentItem->title_key ?? 'Payment',
                'amount' => $paymentItem->amount,
                'payment_date' => now(),
                'status' => 'completed',
                'payment_method_id' => 'bank_transfer', // Default method
                'user_id' => $student->id,
            ]);
        }

        return $this->success($paymentItem, 'Payment status updated successfully');
    }

    /**
     * Get all payment item types (fee types).
     */
    public function feeTypes(): JsonResponse
    {
        // Get unique payment item types (group by base item_id)
        $feeTypes = PaymentItem::selectRaw('
                SUBSTR(item_id, 1, INSTR(item_id, "-") - 1) as base_item_id,
                COALESCE(title, title_key) as title,
                COALESCE(description, description_key) as description,
                MAX(amount) as amount
            ')
            ->groupBy('base_item_id', 'title', 'description')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->base_item_id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'amount' => $item->amount,
                ];
            });

        return $this->success($feeTypes);
    }

    /**
     * Create a new fee type.
     */
    public function createFeeType(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        // Create payment items for all active students
        $students = User::where('role', 'student')->get();
        
        foreach ($students as $student) {
            PaymentItem::create([
                'item_id' => $request->item_id . '-' . $student->id,
                'title' => $request->title,
                'title_key' => $request->title,
                'description' => $request->description,
                'description_key' => $request->description,
                'amount' => $request->amount,
                'status' => 'unpaid',
                'user_id' => $student->id,
            ]);
        }

        return $this->success([
            'item_id' => $request->item_id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
        ], 'Fee type created successfully');
    }

    /**
     * Update a fee type.
     */
    public function updateFeeType(Request $request, string $itemId): JsonResponse
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        // Update all payment items that start with this item_id (item_id + '-' + user_id pattern)
        $updated = PaymentItem::where('item_id', 'like', $itemId . '-%')->update([
            'title' => $request->title,
            'title_key' => $request->title,
            'description' => $request->description,
            'description_key' => $request->description,
            'amount' => $request->amount,
        ]);

        return $this->success([
            'item_id' => $itemId,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
        ], 'Fee type updated successfully');
    }

    /**
     * Delete a fee type.
     */
    public function deleteFeeType(string $itemId): JsonResponse
    {
        // Delete all payment items that start with this item_id
        $deleted = PaymentItem::where('item_id', 'like', $itemId . '-%')->delete();

        if ($deleted === 0) {
            return $this->error('Fee type not found', 404);
        }

        return $this->noContent();
    }

    /**
     * Get payment types statistics.
     */
    public function paymentTypes(): JsonResponse
    {
        // Get all payment items and group by title (extract base item_id)
        $paymentItems = PaymentItem::selectRaw('
                SUBSTR(item_id, 1, INSTR(item_id, "-") - 1) as base_item_id,
                COALESCE(title, title_key) as title,
                SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as total_paid,
                SUM(CASE WHEN status = "unpaid" THEN amount ELSE 0 END) as total_unpaid,
                SUM(amount) as total
            ')
            ->groupBy('base_item_id', 'title')
            ->get();

        $paymentTypes = $paymentItems->map(function ($item) {
            return [
                'id' => $item->base_item_id,
                'title' => $item->title,
                'total' => $item->total,
                'paid' => $item->total_paid,
                'unpaid' => $item->total_unpaid,
            ];
        });

        return $this->success($paymentTypes);
    }

    /**
     * Get receipt details for a specific payment.
     */
    public function getReceipt(string $historyId): JsonResponse
    {
        $history = PaymentHistory::with('user', 'paymentMethod')
            ->where('history_id', $historyId)
            ->firstOrFail();

        // Get the payment item title from database
        $paymentItem = PaymentItem::where('user_id', $history->user_id)
            ->where(function($query) use ($history) {
                $query->where('title', $history->title)
                      ->orWhere('title_key', $history->title);
            })
            ->first();

        return $this->success([
            'id' => $history->history_id,
            'title' => $paymentItem ? ($paymentItem->title ?? $paymentItem->title_key) : $history->title,
            'amount' => $history->amount,
            'date' => $history->payment_date?->format('Y-m-d'),
            'student_name' => $history->user->name,
            'student_id' => $history->user->id,
            'method' => $history->paymentMethod ? $history->paymentMethod->name_key : 'bank_transfer',
        ]);
    }
}
