<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Order;

class StripeCancelController extends Controller
{
    public function __invoke(Order $order)
    {
        if ($order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'cancelled',
            ]);
        }

        return redirect()->route('thank-you', $order);
    }
}