<?php

use App\Models\Order;
use App\Services\Payments\ToyyibPayService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

    /**
     * @return array<int, string>
     */
    public function malaysiaStates(): array
    {
        return [
            'Johor',
            'Kedah',
            'Kelantan',
            'Melaka',
            'Negeri Sembilan',
            'Pahang',
            'Penang',
            'Perak',
            'Perlis',
            'Sabah',
            'Sarawak',
            'Selangor',
            'Terengganu',
            'Kuala Lumpur',
            'Labuan',
            'Putrajaya',
        ];
    }

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
            'state'        => ['required', 'string', Rule::in($this->malaysiaStates())],
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

<div class="min-h-screen bg-zinc-950 px-4 py-10 text-zinc-50 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">

        {{-- Header --}}
        <div class="mb-8">
            <flux:button href="{{ route('landing') }}" wire:navigate variant="ghost" icon="arrow-left" size="sm" class="text-zinc-200 hover:bg-white/5">
                Back to product
            </flux:button>

            <flux:heading size="xl" class="mt-4 text-white">Checkout</flux:heading>
            <flux:text class="mt-1 text-zinc-400">Complete your order for the I love Codex T-Shirt.</flux:text>
        </div>

        <form wire:submit="submit" class="grid gap-8 lg:grid-cols-[1fr_380px]">

            <div class="space-y-6">

                <section class="overflow-hidden rounded-[28px] border border-zinc-800 bg-zinc-900 shadow-[0_28px_70px_rgba(0,0,0,0.35)]">
                    <div class="grid gap-5 p-6 lg:grid-cols-[1fr_auto] lg:items-center lg:p-8">
                        <div>
                            <flux:text size="sm" class="uppercase tracking-[0.18em] text-zinc-500">Checkout</flux:text>
                            <flux:heading size="lg" class="mt-1 text-white">I love Codex T-Shirt</flux:heading>
                            <p class="mt-2 max-w-xl text-sm leading-6 text-zinc-400">
                                Black tee with a clean white print. Keep the flow simple, finish the order, and move on.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-right">
                            <div class="text-xs uppercase tracking-[0.2em] text-zinc-500">Total</div>
                            <div class="mt-1 text-2xl font-semibold text-white">{{ $this->formattedTotal }}</div>
                        </div>
                    </div>
                </section>

                <section class="rounded-[28px] border border-zinc-800 bg-zinc-900 p-6 shadow-sm sm:p-7">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <flux:heading size="lg" class="!text-white">Customer Details</flux:heading>
                        <flux:text size="sm" class="text-zinc-400">Required</flux:text>
                    </div>

                    <div class="grid gap-4">
                        <flux:field>
                            <flux:label class="!text-zinc-200">Full Name</flux:label>
                            <flux:input wire:model="name" type="text" placeholder="Ahmad bin Ali" class="border-zinc-700 bg-zinc-800 !text-zinc-50 placeholder:text-zinc-500" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="!text-zinc-200">Email Address</flux:label>
                            <flux:input wire:model="email" type="email" placeholder="ahmad@example.com" class="border-zinc-700 bg-zinc-800 !text-zinc-50 placeholder:text-zinc-500" />
                            <flux:error name="email" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="!text-zinc-200">Phone Number</flux:label>
                            <flux:input wire:model="phone" type="tel" value="60193831240" placeholder="+60 12-345 6789" class="border-zinc-700 bg-zinc-800 !text-zinc-50 placeholder:text-zinc-500" />
                            <flux:error name="phone" />
                        </flux:field>
                    </div>
                </section>

                <section class="rounded-[28px] border border-zinc-800 bg-zinc-900 p-6 shadow-sm sm:p-7">
                    <flux:heading size="lg" class="mb-5 !text-white">Shipping Address</flux:heading>

                    <div class="grid gap-4">
                        <flux:field>
                            <flux:label class="!text-zinc-200">Address Line 1</flux:label>
                            <flux:input wire:model="addressLine1" type="text" value="No. 12, Jalan Utama" class="border-zinc-700 bg-zinc-800 !text-zinc-50 placeholder:text-zinc-500" />
                            <flux:error name="addressLine1" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="!text-zinc-200">
                                Address Line 2
                                <flux:badge size="sm" variant="pill" class="ml-2 border border-zinc-700 bg-zinc-800 text-zinc-300">Optional</flux:badge>
                            </flux:label>
                            <flux:input wire:model="addressLine2" type="text" value="Taman Sri Muda" class="border-zinc-700 bg-zinc-800 !text-zinc-50 placeholder:text-zinc-500" />
                        </flux:field>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <flux:field>
                                <flux:label class="!text-zinc-200">City</flux:label>
                                <flux:input wire:model="city" type="text" value="Shah Alam" class="border-zinc-700 bg-zinc-800 !text-zinc-50 placeholder:text-zinc-500" />
                                <flux:error name="city" />
                            </flux:field>

                            <flux:field>
                                <flux:label class="!text-zinc-200">State</flux:label>
                                <flux:select wire:model="state" placeholder="Select state" class="border-zinc-700 bg-zinc-800 !text-zinc-50">
                                    @foreach ($this->malaysiaStates() as $malaysiaState)
                                        <flux:select.option :value="$malaysiaState">
                                            {{ $malaysiaState }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="state" />
                            </flux:field>

                            <flux:field>
                                <flux:label class="!text-zinc-200">Postcode</flux:label>
                                <flux:input wire:model="postcode" type="text" value="40150" class="border-zinc-700 bg-zinc-800 !text-zinc-50 placeholder:text-zinc-500" />
                                <flux:error name="postcode" />
                            </flux:field>
                        </div>
                    </div>
                </section>

                <section class="rounded-[28px] border border-zinc-800 bg-zinc-900 p-6 shadow-sm sm:p-7">
                    <flux:heading size="lg" class="mb-5 !text-white">Product Options</flux:heading>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label class="!text-zinc-200">Size</flux:label>
                            <flux:select wire:model.live="size" class="border-zinc-700 bg-zinc-800 !text-zinc-50">
                                <flux:select.option value="S">S</flux:select.option>
                                <flux:select.option value="M">M</flux:select.option>
                                <flux:select.option value="L">L</flux:select.option>
                                <flux:select.option value="XL">XL</flux:select.option>
                                <flux:select.option value="XXL">XXL</flux:select.option>
                            </flux:select>
                            <flux:error name="size" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="!text-zinc-200">Quantity</flux:label>
                            <flux:input wire:model.live="quantity" type="number" min="1" max="10" class="border-zinc-700 bg-zinc-800 !text-zinc-50 placeholder:text-zinc-500" />
                            <flux:error name="quantity" />
                        </flux:field>
                    </div>
                </section>

                <section class="rounded-[28px] border border-zinc-800 bg-zinc-900 p-6 shadow-sm sm:p-7">
                    <flux:heading size="lg" class="mb-5 !text-white">Payment Method</flux:heading>

                    <flux:radio.group wire:model="gateway" class="grid gap-3 sm:grid-cols-2">
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
                </section>

            </div>

            {{-- Order Summary --}}
            <aside class="h-fit rounded-[28px] border border-zinc-800 bg-zinc-900 p-6 shadow-sm lg:sticky lg:top-8 text-zinc-100">
                <flux:heading size="lg" class="mb-5 !text-white">Order Summary</flux:heading>

                <div class="flex gap-4">
                    <img
                        src="{{ asset('images/products/codex-shirt.png') }}"
                        alt="I love Codex T-Shirt"
                        class="h-20 w-20 rounded-xl border border-zinc-700 object-cover"
                    >
                    <div>
                        <p class="font-semibold text-white">{{ $productName }}</p>
                        <flux:text size="sm" class="text-zinc-400">Black / Size {{ $size }}</flux:text>
                        <p class="mt-1 font-medium text-white">{{ $this->formattedTotal }}</p>
                    </div>
                </div>

                <flux:separator class="my-5" />

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-100!">Quantity</flux:text>
                        <span class="text-white">{{ $quantity }}</span>
                    </div>
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-100!">Subtotal</flux:text>
                        <span class="text-white">{{ $this->formattedTotal }}</span>
                    </div>
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-100!">Shipping</flux:text>
                        <span class="text-white">Included</span>
                    </div>
                </div>

                <flux:separator class="my-5" />

                <div class="flex justify-between font-bold">
                    <span class="text-white">Total</span>
                    <span class="text-white">{{ $this->formattedTotal }}</span>
                </div>

                <flux:button type="submit" variant="primary" class="mt-6 w-full">
                    Place Order
                </flux:button>
            </aside>
        </form>
    </div>
</div>
