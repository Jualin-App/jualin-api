<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Transaction;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function createPayment(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!in_array($user->role, ['customer', 'admin'])) {
            return ApiResponse::error(
                'Only customers and admins can create payments',
                null,
                403
            );
        }

        try {
            $transaction = Transaction::with(['items.product', 'customer'])->findOrFail($request->transaction_id);

            if ($transaction->customer_id !== $user->id && $user->role !== 'admin') {
                return ApiResponse::error(
                    'Unauthorized access to transaction',
                    null,
                    403
                );
            }

            $customerDetails = $request->customer_details;

            if (empty($customerDetails['email'])) {
                $customerDetails['email'] = $user->email;
            }
            if (empty($customerDetails['first_name'])) {
                $customerDetails['first_name'] = $user->username;
            }

            $result = $this->midtransService->createSnapToken(
                $transaction,
                $customerDetails
            );

            return ApiResponse::success(
                'Payment token created successfully',
                [
                    'snap_token' => $result['snap_token'],
                    'snap_url' => $result['snap_url'],
                    'order_id' => $result['order_id'],
                    'transaction_id' => $transaction->id,
                ],
                201
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error(
                'Transaction not found',
                null,
                404
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to create payment',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function handleNotification(Request $request): JsonResponse
    {
        try {
            $notification = $request->all();

            $payment = $this->midtransService->handleNotification($notification);

            return ApiResponse::success(
                'Notification processed',
                $payment,
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error(
                'Payment not found',
                null,
                404
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to process notification',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function checkStatus(Request $request, string $orderId): JsonResponse
    {
        try {
            $status = $this->midtransService->getTransactionStatus($orderId);

            return ApiResponse::success(
                'Payment status retrieved successfully',
                $status,
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to check status',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
    
    public function getPaymentsByUser(Request $request): JsonResponse
    {
        $user = Auth::user();

        $payments = Payment::whereHas('transaction', function ($query) use ($user) {
                $query->where('customer_id', $user->id);
            })
            ->get([
                'id as payment_id',
                'order_id',
                'snap_token',
                'snap_url',
                'transaction_status',
                'gross_amount'
            ])
            ->map(function ($payment) {
                if ($payment->transaction_status !== 'pending') {
                    $payment->snap_token = null;
                    $payment->snap_url = null;
                }
                return $payment;
            });

        if ($payments->isEmpty()) {
            return ApiResponse::error('No payments found', [], 404);
        }

        return ApiResponse::success(
            'Payments retrieved successfully',
            $payments,
            200
        );
    }
}