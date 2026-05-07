<?php

use App\Models\Order;
use App\Services\Payments\ToyyibPayService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';
    public string $phone = '60197488001';

    public string $addressLine1 = 'Lot 789';
    public string $addressLine2 = '';
    public string $city = 'Shah Alam';
    public string $state = 'Selangor';
    public string $postcode = '40150';
    public string $country = 'Malaysia';

    public string $size = 'M';
    public int $quantity = 1;
    public string $gateway = 'toyyibpay';

    public string $productName = 'I love Codex T-Shirt';
    public string $productSku = 'CODEX-TSHIRT-BLACK';
    public int $unitPrice = 4900;

    public function getTotalAmountProperty(): int
    {
        return $this->unitPrice * $this->quantity;
    }

    public function getFormattedTotalProperty(): string
    {
        return 'RM' . number_format($this->totalAmount / 100, 2);
    }

    public function submit()
    {
        $this->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255'],
            'phone'        => ['required', 'string', 'max:30'],
            'addressLine1' => ['required', 'string', 'max:255'],
            'addressLine2' => ['nullable', 'string', 'max:255'],
            'city'         => ['required', 'string', 'max:100'],
            'state'        => ['required', 'string', 'max:100'],
            'postcode'     => ['required', 'string', 'max:20'],
            'country'      => ['required', 'string', 'max:100'],
            'size'         => ['required', 'in:S,M,L,XL,XXL'],
            'quantity'     => ['required', 'integer', 'min:1', 'max:10'],
            'gateway'      => ['required', 'in:toyyibpay,stripe'],
        ]);

        $order = Order::create([
            'order_number'           => 'CODX-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'customer_name'          => $this->name,
            'customer_email'         => $this->email,
            'customer_phone'         => $this->phone,
            'shipping_address_line_1' => $this->addressLine1,
            'shipping_address_line_2' => $this->addressLine2,
            'shipping_city'          => $this->city,
            'shipping_state'         => $this->state,
            'shipping_postcode'      => $this->postcode,
            'shipping_country'       => $this->country,
            'product_name'           => $this->productName,
            'product_sku'            => $this->productSku,
            'size'                   => $this->size,
            'quantity'               => $this->quantity,
            'unit_price'             => $this->unitPrice,
            'total_amount'           => $this->totalAmount,
            'payment_gateway'        => $this->gateway,
            'payment_status'         => 'pending',
        ]);

        if ($this->gateway === 'toyyibpay') {
    $paymentUrl = app(\App\Services\Payments\ToyyibPayService::class)
        ->createBill($order);

    return redirect()->away($paymentUrl);
}

if ($this->gateway === 'stripe') {
    $paymentUrl = app(\App\Services\Payments\StripePaymentService::class)
        ->createCheckoutSession($order);

    return redirect()->away($paymentUrl);
}


        return redirect()->route('thank-you', $order);
    }
};
?>

<div class="min-h-screen bg-zinc-50 px-4 py-10 text-zinc-950 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">

        {{-- Header --}}
        <div class="mb-8">
            <flux:button href="{{ route('landing') }}" wire:navigate variant="ghost" icon="arrow-left" size="sm">
                Back to product
            </flux:button>

            <flux:heading size="xl" class="mt-4">Checkout</flux:heading>
            <flux:text class="mt-1">Complete your order for the I love Codex T-Shirt.</flux:text>
        </div>

        <form wire:submit="submit" class="grid gap-8 lg:grid-cols-[1fr_380px]">

            <div class="space-y-6">

                {{-- Customer Details --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-100">
                    <flux:heading size="lg" class="mb-5">Customer Details</flux:heading>

                    <div class="grid gap-4">
                        <flux:field>
                            <flux:label>Full Name</flux:label>
                            <flux:input wire:model="name" type="text" placeholder="Ahmad bin Ali" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Email Address</flux:label>
                            <flux:input wire:model="email" type="email" placeholder="ahmad@example.com" />
                            <flux:error name="email" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Phone Number</flux:label>
                            <flux:input wire:model="phone" type="tel" value="60193831240" placeholder="+60 12-345 6789" />
                            <flux:error name="phone" />
                        </flux:field>
                    </div>
                </div>

                {{-- Shipping Address --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-100">
                    <flux:heading size="lg" class="mb-5">Shipping Address</flux:heading>

                    <div class="grid gap-4">
                        <flux:field>
                            <flux:label>Address Line 1</flux:label>
                            <flux:input wire:model="addressLine1" type="text" value="No. 12, Jalan Utama" />
                            <flux:error name="addressLine1" />
                        </flux:field>

                        <flux:field>
                            <flux:label>
                                Address Line 2
                                <flux:badge size="sm" variant="pill" class="ml-2">Optional</flux:badge>
                            </flux:label>
                            <flux:input wire:model="addressLine2" type="text" value="Taman Sri Muda" />
                        </flux:field>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <flux:field>
                                <flux:label>City</flux:label>
                                <flux:input wire:model="city" type="text" value="Shah Alam" />
                                <flux:error name="city" />
                            </flux:field>

                            <flux:field>
                                <flux:label>State</flux:label>
                                <flux:input wire:model="state" type="text" value="Selangor" />
                                <flux:error name="state" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Postcode</flux:label>
                                <flux:input wire:model="postcode" type="text" value="40150" />
                                <flux:error name="postcode" />
                            </flux:field>
                        </div>
                    </div>
                </div>

                {{-- Product Options --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-100">
                    <flux:heading size="lg" class="mb-5">Product Options</flux:heading>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>Size</flux:label>
                            <flux:select wire:model.live="size">
                                <flux:select.option value="S">S</flux:select.option>
                                <flux:select.option value="M">M</flux:select.option>
                                <flux:select.option value="L">L</flux:select.option>
                                <flux:select.option value="XL">XL</flux:select.option>
                                <flux:select.option value="XXL">XXL</flux:select.option>
                            </flux:select>
                            <flux:error name="size" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Quantity</flux:label>
                            <flux:input wire:model.live="quantity" type="number" min="1" max="10" />
                            <flux:error name="quantity" />
                        </flux:field>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-100">
                    <flux:heading size="lg" class="mb-5">Payment Method</flux:heading>

                    <flux:radio.group wire:model="gateway" variant="cards" class="grid sm:grid-cols-2">
                        <flux:radio
                            value="toyyibpay"
                            label="ToyyibPay"
                            description="FPX, debit &amp; credit card. Best for Malaysia."
                        />
                        <flux:radio
                            value="stripe"
                            label="Stripe"
                            description="International card payments."
                        />
                    </flux:radio.group>

                    <flux:error name="gateway" />
                </div>

            </div>

            {{-- Order Summary --}}
            <aside class="h-fit rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-100 lg:sticky lg:top-8">
                <flux:heading size="lg" class="mb-5">Order Summary</flux:heading>

                <div class="flex gap-4">
                    <img
                        src="{{ asset('images/products/codex-shirt.png') }}"
                        alt="I love Codex T-Shirt"
                        class="h-20 w-20 rounded-xl object-cover"
                    >
                    <div>
                        <p class="font-semibold">{{ $productName }}</p>
                        <flux:text size="sm">Black / Size {{ $size }}</flux:text>
                        <p class="mt-1 font-medium">RM49.00</p>
                    </div>
                </div>

                <flux:separator class="my-5" />

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <flux:text>Quantity</flux:text>
                        <span>{{ $quantity }}</span>
                    </div>
                    <div class="flex justify-between">
                        <flux:text>Subtotal</flux:text>
                        <span>{{ $this->formattedTotal }}</span>
                    </div>
                    <div class="flex justify-between">
                        <flux:text>Shipping</flux:text>
                        <span>Included</span>
                    </div>
                </div>

                <flux:separator class="my-5" />

                <div class="flex justify-between font-bold">
                    <span>Total</span>
                    <span>{{ $this->formattedTotal }}</span>
                </div>

                <flux:button type="submit" variant="primary" class="mt-6 w-full">
                    Place Order
                </flux:button>

                <flux:text size="sm" class="mt-3 text-center">
                    Payment integration coming next step.
                </flux:text>
            </aside>
        </form>
    </div>
</div>
