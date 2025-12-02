<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;
use Midtrans\Notification;
use App\Models\Transaction;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Create Snap payment token
     */
    public function createSnapToken(Transaction $transaction, array $customerDetails): array
    {
        $orderId = 'ORDER-' . Str::upper(Str::random(10)) . '-' . $transaction->id;


        $payment = Payment::create([
            'order_id' => $orderId,
            'transaction_id' => $transaction->id,
            'gross_amount' => $transaction->total_amount,
            'transaction_status' => 'pending',
        ]);

        $items = [];
        foreach ($transaction->items as $item) {
            $items[] = [
                'id' => $item->product_id,
                'price' => $item->price_at_purchase,
                'quantity' => $item->quantity,
                'name' => $item->product->name,
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $transaction->total_amount,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $customerDetails['first_name'] ?? $transaction->customer->name,
                'last_name' => $customerDetails['last_name'] ?? '',
                'email' => $customerDetails['email'] ?? $transaction->customer->email,
                'phone' => $customerDetails['phone'] ?? '',
            ],
        ];

        try {
            $snapResponse = Snap::createTransaction($params);
            $snapToken = $snapResponse->token;
            $snapUrl = $snapResponse->redirect_url;


            $payment->update([
                'snap_token' => $snapToken,
                'snap_url' => $snapUrl,
            ]);

            return [
                'snap_token' => $snapToken,
                'snap_url' => $snapUrl,
                'order_id' => $orderId,
                'payment_id' => $payment->id,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to create payment token: ' . $e->getMessage());
        }
    }

    /**
     * Handle Midtrans webhook/callback
     */
    public function handleNotification(array $notificationData): Payment
    {
        try {
            $notification = new Notification();

            $orderId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status ?? null;

            $payment = Payment::where('order_id', $orderId)->firstOrFail();

            $payment->update([
                'midtrans_transaction_id' => $notification->transaction_id,
                'payment_type' => $notification->payment_type,
                'bank_or_channel' => $this->getBankOrChannel($notification),
                'transaction_status' => $transactionStatus,
                'transaction_time' => isset($notification->transaction_time)
                    ? date('Y-m-d H:i:s', strtotime($notification->transaction_time))
                    : now(),
            ]);

            $this->updateTransactionStatus($payment, $transactionStatus, $fraudStatus);

            return $payment->fresh();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update status transaction berdasarkan payment status
     * FUNCTION BARU - LEBIH ROBUST
     */
    private function updateTransactionStatus(Payment $payment, string $transactionStatus, ?string $fraudStatus): void
    {
        $transaction = $payment->transaction;
        $oldStatus = $transaction->status;
        $newStatus = null;

        switch ($transactionStatus) {
            case 'capture':
                if ($fraudStatus == 'accept') {
                    $transaction->update(['status' => 'paid']);
                } elseif ($fraudStatus == 'challenge') {
                    $transaction->update(['status' => 'pending']);
                }
                break;

            case 'settlement':
                $transaction->update(['status' => 'paid']);
                break;

            case 'pending':
                $transaction->update(['status' => 'pending']);
                break;

            case 'deny':
                $transaction->update(['status' => 'failed']);
                break;

            case 'expire':
                $transaction->update(['status' => 'expired']);
                break;

            case 'cancel':
                $transaction->update(['status' => 'cancelled']);
                break;

            case 'refund':
                $transaction->update(['status' => 'refunded']);
                break;

            default:
                break;
        }

        if ($newStatus) {
            $transaction->update(['status' => $newStatus]);

            $failedStatuses = ['failed', 'expired', 'cancelled', 'refunded'];
            if (in_array($newStatus, $failedStatuses) && !in_array($oldStatus, $failedStatuses)) {
                $this->restoreStock($transaction);
            }
        }
    }

    private function restoreStock(Transaction $transaction): void
    {
        try {
            DB::beginTransaction();

            $transaction->load('items.product');

            foreach ($transaction->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to restore stock for transaction: ' . $transaction->id, [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get bank or channel dari notification
     * FUNCTION BARU - HELPER
     */
    private function getBankOrChannel($notification): ?string
    {
        if (isset($notification->va_numbers) && !empty($notification->va_numbers)) {
            return $notification->va_numbers[0]->bank;
        }

        if (isset($notification->payment_type)) {
            if (in_array($notification->payment_type, ['gopay', 'shopeepay', 'qris'])) {
                return $notification->payment_type;
            }
        }

        if (isset($notification->bank)) {
            return $notification->bank;
        }

        return $notification->payment_type ?? null;
    }

    /**
     * Get transaction status from Midtrans
     */
    public function getTransactionStatus(string $orderId): array
    {
        try {
            $status = MidtransTransaction::status($orderId);
            return (array) $status;
        } catch (\Exception $e) {
            throw new \Exception('Failed to check transaction status: ' . $e->getMessage());
        }
    }
}