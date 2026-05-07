<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class StripeSuccessController extends Controller
{
    public function __invoke(Request $request)
    {
        $sessionId = $request->query('session_id');

        $order = Order::query()
            ->where('stripe_session_id', $sessionId)
            ->first();

        if (! $order) {
            return redirect()->route('landing')
                ->with('error', 'Order not found.');
        }

        return redirect()->route('thank-you', $order);
    }
}