<?php

namespace App\Services\Payments;

use App\Models\Order;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePaymentService
{
    public function createCheckoutSession(Order $order): string
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'mode' => 'payment',

            'customer_email' => $order->customer_email,

            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'myr',
                        'product_data' => [
                            'name' => $order->product_name,
                            'description' => 'Size ' . $order->size . ' Qty ' . $order->quantity,
                        ],
                        'unit_amount' => $order->unit_price,
                    ],
                    'quantity' => $order->quantity,
                ],
            ],

            'metadata' => [
                'order_id' => (string) $order->id,
                'order_number' => $order->order_number,
            ],

            'success_url' => route('payment.stripe.success', [], true) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.stripe.cancel', ['order' => $order->id], true),
        ]);

        $order->update([
            'stripe_session_id' => $session->id,
        ]);

        return $session->url;
    }
}