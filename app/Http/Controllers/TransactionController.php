<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionStoreRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class TransactionController extends Controller
{
    public function store(TransactionStoreRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (!in_array($user->role, ['customer', 'admin'])) {
            return ApiResponse::error(
                'Only customers and admins can create transactions',
                null,
                403
            );
        }

        try {
            DB::beginTransaction();

            $customerId = $user->id;
            $totalAmount = 0;
            $items = [];

            foreach ($request->items as $itemData) {
                $product = \App\Models\Product::findOrFail($itemData['product_id']);
                $quantity = $itemData['quantity'];
                $subtotal = $product->price * $quantity;
                $totalAmount += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price_at_purchase' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $transaction = Transaction::create([
                'customer_id' => $customerId,
                'seller_id' => $request->seller_id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    ...$item,
                ]);
            }

            DB::commit();

            $transaction->load(['items.product', 'customer', 'seller']);

            return ApiResponse::success(
                'Transaction created successfully',
                $transaction,
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(
                'Failed to create transaction',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $transactions = Transaction::with(['items.product', 'customer', 'seller', 'payment'])
            ->where('customer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->latest()
            ->paginate(10);

        return ApiResponse::success(
            'Transactions retrieved successfully',
            $transactions,
            200
        );
    }

    public function show(string $id): JsonResponse
    {
        try {
            $transaction = Transaction::with(['items.product', 'customer', 'seller', 'payment'])
                ->findOrFail($id);

            if (
                $transaction->customer_id !== auth()->id() &&
                $transaction->seller_id !== auth()->id()
            ) {
                return ApiResponse::error(
                    'Unauthorized',
                    null,
                    403
                );
            }

            return ApiResponse::success(
                'Transaction retrieved successfully',
                $transaction,
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error(
                'Transaction not found',
                null,
                404
            );
        }
    }
}
