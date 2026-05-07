<?php

use Livewire\Component;
use App\Models\Order;

new class extends Component {
    public Order $order;
};
?>

<div class="min-h-screen bg-zinc-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-2xl">

        {{-- Status callout --}}
        @if ($order->payment_status === 'paid')
            <flux:callout color="green" icon="check-circle" class="mb-6">
                <flux:callout.heading>Payment received</flux:callout.heading>
                <flux:callout.text>Thank you for your order. We'll process your T-shirt shipment soon.</flux:callout.text>
            </flux:callout>
        @elseif ($order->payment_status === 'pending')
            <flux:callout color="yellow" icon="clock" class="mb-6">
                <flux:callout.heading>Payment pending</flux:callout.heading>
                <flux:callout.text>Your order has been created. Payment confirmation is still pending. Please refresh in a moment.</flux:callout.text>
            </flux:callout>
        @elseif ($order->payment_status === 'cancelled')
            <flux:callout color="zinc" icon="x-circle" class="mb-6">
                <flux:callout.heading>Payment cancelled</flux:callout.heading>
                <flux:callout.text>Your payment was cancelled. You may try again below.</flux:callout.text>
            </flux:callout>
        @else
            <flux:callout color="red" icon="exclamation-circle" class="mb-6">
                <flux:callout.heading>Payment failed</flux:callout.heading>
                <flux:callout.text>Your payment was not successful. Please try again.</flux:callout.text>
            </flux:callout>
        @endif

        <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-zinc-100">

            {{-- Order header --}}
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:text size="sm" class="uppercase tracking-wide">Order Confirmation</flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $order->order_number }}</flux:heading>
                </div>

                @php
                    $statusColor = match($order->payment_status) {
                        'paid'      => 'green',
                        'pending'   => 'yellow',
                        'cancelled' => 'zinc',
                        default     => 'red',
                    };
                @endphp

                <flux:badge color="{{ $statusColor }}" size="lg">
                    {{ ucfirst($order->payment_status) }}
                </flux:badge>
            </div>

            <flux:separator class="my-6" />

            {{-- Order details grid --}}
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <flux:text size="sm">Product</flux:text>
                    <p class="mt-0.5 font-semibold">{{ $order->product_name }}</p>
                </div>

                <div>
                    <flux:text size="sm">Total Paid</flux:text>
                    <p class="mt-0.5 font-semibold">{{ $order->formatted_total }}</p>
                </div>

                <div>
                    <flux:text size="sm">Size</flux:text>
                    <p class="mt-0.5 font-semibold">{{ $order->size }}</p>
                </div>

                <div>
                    <flux:text size="sm">Quantity</flux:text>
                    <p class="mt-0.5 font-semibold">{{ $order->quantity }}</p>
                </div>

                <div>
                    <flux:text size="sm">Payment Method</flux:text>
                    <p class="mt-0.5 font-semibold">{{ strtoupper($order->payment_gateway) }}</p>
                </div>
            </div>

            <flux:separator class="my-6" />

            {{-- Customer info --}}
            <flux:heading size="sm" class="mb-3">Customer</flux:heading>

            <div class="space-y-1">
                <flux:text>{{ $order->customer_name }}</flux:text>
                <flux:text>{{ $order->masked_email }}</flux:text>
                <flux:text>Phone {{ $order->customer_phone }}</flux:text>
            </div>

            <flux:separator class="my-6" />

            {{-- Shipping --}}
            <flux:heading size="sm" class="mb-3">Shipping Address</flux:heading>

            <div class="space-y-1">
                <flux:text>{{ $order->shipping_address_line_1 }}</flux:text>

                @if ($order->shipping_address_line_2)
                    <flux:text>{{ $order->shipping_address_line_2 }}</flux:text>
                @endif

                <flux:text>
                    {{ $order->shipping_postcode }} {{ $order->shipping_city }}, {{ $order->shipping_state }}
                </flux:text>

                <flux:text>{{ $order->shipping_country }}</flux:text>
            </div>

            <flux:separator class="my-6" />

            {{-- Actions --}}
            <div class="flex flex-wrap gap-3">
                <flux:button href="{{ route('landing') }}" wire:navigate variant="ghost" icon="arrow-left">
                    Back to Product
                </flux:button>

                @if ($order->payment_status !== 'paid')
                    <flux:button href="{{ route('checkout') }}" wire:navigate variant="primary" icon="arrow-path">
                        Try Checkout Again
                    </flux:button>
                @endif
            </div>

        </div>
    </div>
</div>
