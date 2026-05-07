<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ToyyibPayWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        Log::info('ToyyibPay webhook received', [
            'payload' => $request->all(),
        ]);

        $orderNumber = $request->input('order_id')
            ?? $request->input('billExternalReferenceNo')
            ?? $request->input('refno')
            ?? $request->input('order_number');

        $billCode = $request->input('billcode')
            ?? $request->input('billCode')
            ?? $request->input('BillCode');

        $statusId = $request->input('status_id')
            ?? $request->input('status')
            ?? $request->input('payment_status');

        $transactionId = $request->input('transaction_id')
            ?? $request->input('transactionId')
            ?? $request->input('refno');

        $order = Order::query()
            ->when($orderNumber, fn ($query) => $query->where('order_number', $orderNumber))
            ->when(! $orderNumber && $billCode, fn ($query) => $query->where('toyyibpay_bill_code', $billCode))
            ->first();

        if (! $order) {
            Log::warning('ToyyibPay webhook order not found', [
                'payload' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        if ($this->isSuccessfulPayment($statusId)) {
            if ($order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'toyyibpay_transaction_id' => $transactionId,
                    'paid_at' => now(),
                ]);
            }

            return response()->json([
                'message' => 'Order marked as paid',
            ]);
        }

        if ($order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'failed',
                'toyyibpay_transaction_id' => $transactionId,
            ]);
        }

        return response()->json([
            'message' => 'Order marked as failed',
        ]);
    }

    private function isSuccessfulPayment(mixed $statusId): bool
    {
        return (string) $statusId === '1';
    }
}