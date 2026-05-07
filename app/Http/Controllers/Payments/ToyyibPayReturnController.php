<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class ToyyibPayReturnController extends Controller
{
    public function __invoke(Request $request)
    {
        $orderNumber = $request->input('order_id')
            ?? $request->input('billExternalReferenceNo')
            ?? $request->input('refno')
            ?? $request->input('order_number');

        $billCode = $request->input('billcode')
            ?? $request->input('billCode')
            ?? $request->input('BillCode');

        $order = Order::query()
            ->when($orderNumber, fn ($query) => $query->where('order_number', $orderNumber))
            ->when(! $orderNumber && $billCode, fn ($query) => $query->where('toyyibpay_bill_code', $billCode))
            ->latest()
            ->first();

        if (! $order) {
            return redirect()->route('landing')
                ->with('error', 'Order not found.');
        }

        return redirect()->route('thank-you', $order);
    }
}