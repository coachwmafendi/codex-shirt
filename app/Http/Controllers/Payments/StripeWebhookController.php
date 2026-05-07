<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $secret
            );
        } catch (UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload', [
                'message' => $e->getMessage(),
            ]);

            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook invalid signature', [
                'message' => $e->getMessage(),
            ]);

            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $order = Order::query()
                ->where('stripe_session_id', $session->id)
                ->first();

            if (! $order) {
                Log::warning('Stripe webhook order not found', [
                    'session_id' => $session->id,
                ]);

                return response('Order not found', 404);
            }

            if ($order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'stripe_payment_intent_id' => $session->payment_intent,
                    'paid_at' => now(),
                ]);
            }
        }

        return response('Webhook handled', 200);
    }
}