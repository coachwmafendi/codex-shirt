<?php

use App\Models\Order;
use App\Services\Payments\StripePaymentService;
use App\Services\Payments\ToyyibPayService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
    public string $productKey = 'shirt';

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

    public string $productName = '';
    public string $productSku = '';
    public string $productImage = '';
    public string $productDescription = '';
    public int $unitPrice = 0;
    public bool $requiresSize = true;
    public array $sizeOptions = [];
    public string $sizeLabel = 'Size';

    public function mount(): void
    {
        $this->selectProduct((string) request()->query('product', 'shirt'));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function products(): array
    {
        return [
            'shirt' => [
                'name' => 'I love Codex T-Shirt',
                'sku' => 'CODEX-TSHIRT-BLACK',
                'image' => asset('images/products/codex-shirt.png'),
                'description' => 'Black tee with a clean white print.',
                'price' => 4900,
                'requires_size' => true,
                'size_options' => ['S', 'M', 'L', 'XL', 'XXL'],
                'default_size' => 'M',
                'size_label' => 'Size',
            ],
            'sticker' => [
                'name' => 'Codex Sticker',
                'sku' => 'CODEX-STICKER',
                'image' => asset('images/products/codex-sticker.jpg'),
                'description' => 'A small sticker for laptops, notebooks, and gear.',
                'price' => 500,
                'requires_size' => false,
                'size_options' => [],
                'default_size' => 'Single sticker',
                'size_label' => 'Variant',
            ],
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

    public function getSelectedProductProperty(): array
    {
        return $this->products()[$this->productKey] ?? $this->products()['shirt'];
    }

    public function getProductSelectionLabelProperty(): string
    {
        if ($this->requiresSize) {
            return $this->sizeLabel . ' ' . $this->size;
        }

        return $this->size;
    }

    protected function selectProduct(string $productKey): void
    {
        $product = $this->products()[$productKey] ?? $this->products()['shirt'];

        $this->productKey = array_key_exists($productKey, $this->products()) ? $productKey : 'shirt';
        $this->productName = $product['name'];
        $this->productSku = $product['sku'];
        $this->productImage = $product['image'];
        $this->productDescription = $product['description'];
        $this->unitPrice = $product['price'];
        $this->requiresSize = $product['requires_size'];
        $this->sizeOptions = $product['size_options'];
        $this->sizeLabel = $product['size_label'];
        $this->size = $product['default_size'];
    }

    public function updatedProductKey(string $value): void
    {
        $this->selectProduct($value);
    }

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

    public function submit()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'addressLine1' => ['required', 'string', 'max:255'],
            'addressLine2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', Rule::in($this->malaysiaStates())],
            'postcode' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'gateway' => ['required', 'in:toyyibpay,stripe'],
        ];

        if ($this->requiresSize) {
            $rules['size'] = ['required', 'string', Rule::in($this->sizeOptions)];
        } else {
            $this->size = $this->selectedProduct['default_size'];
        }

        $this->validate($rules);

        $order = Order::create([
            'order_number' => 'CODX-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'customer_name' => $this->name,
            'customer_email' => $this->email,
            'customer_phone' => $this->phone,
            'shipping_address_line_1' => $this->addressLine1,
            'shipping_address_line_2' => $this->addressLine2,
            'shipping_city' => $this->city,
            'shipping_state' => $this->state,
            'shipping_postcode' => $this->postcode,
            'shipping_country' => $this->country,
            'product_name' => $this->productName,
            'product_sku' => $this->productSku,
            'size' => $this->size,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'total_amount' => $this->totalAmount,
            'payment_gateway' => $this->gateway,
            'payment_status' => 'pending',
        ]);

        try {
            if ($this->gateway === 'toyyibpay') {
                $paymentUrl = app(ToyyibPayService::class)->createBill($order);

                return redirect()->away($paymentUrl);
            }

            if ($this->gateway === 'stripe') {
                $paymentUrl = app(StripePaymentService::class)->createCheckoutSession($order);

                return redirect()->away($paymentUrl);
            }
        } catch (\Throwable $throwable) {
            Log::error('Checkout payment failed', [
                'order_id' => $order->id,
                'gateway' => $this->gateway,
                'message' => $throwable->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'gateway' => 'Unable to start payment right now. Please try again.',
            ]);
        }

        return redirect()->route('thank-you', $order);
    }
};
?>

<div class="min-h-screen bg-gray-50 px-4 py-10 text-gray-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">
        <div class="mb-8">
            <flux:button href="{{ route('landing') }}" wire:navigate variant="ghost" icon="arrow-left" size="sm" class="text-gray-600 hover:bg-gray-100">
                Back to product
            </flux:button>

            <flux:heading size="xl" class="mt-4 text-gray-900">Checkout</flux:heading>
            <flux:text class="mt-1 text-gray-500">Complete your order for {{ $productName }}.</flux:text>
        </div>

        <form wire:submit="submit" class="grid gap-8 lg:grid-cols-[1fr_380px]">
            <div class="space-y-6">
                <section class="overflow-hidden rounded-[2.5rem] border border-gray-200 bg-white shadow-sm">
                    <div class="grid gap-5 p-6 lg:grid-cols-[1fr_auto] lg:items-center lg:p-8">
                        <div>
                            <flux:text size="sm" class="uppercase tracking-[0.18em] text-gray-400">Checkout</flux:text>
                            <flux:heading size="lg" class="mt-1 text-gray-900">{{ $productName }}</flux:heading>
                            <p class="mt-2 max-w-xl text-sm leading-6 text-gray-500">
                                {{ $productDescription }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-right">
                            <div class="text-xs uppercase tracking-[0.2em] text-gray-400">Total</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $this->formattedTotal }}</div>
                        </div>
                    </div>
                </section>

                <section class="rounded-[2.5rem] border border-gray-200 bg-white p-6 shadow-sm sm:p-7">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <flux:heading size="lg" class="!text-gray-900">Customer Details</flux:heading>
                        <flux:text size="sm" class="text-gray-400">Required</flux:text>
                    </div>

                    <div class="grid gap-4">
                        <flux:field>
                            <flux:label class="!text-gray-700">Full Name</flux:label>
                            <flux:input wire:model="name" type="text" placeholder="Ahmad bin Ali" class="border-gray-300 bg-white !text-gray-900 placeholder:text-gray-400" />
                            <flux:error name="name" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="!text-gray-700">Email Address</flux:label>
                            <flux:input wire:model="email" type="email" placeholder="ahmad@example.com" class="border-gray-300 bg-white !text-gray-900 placeholder:text-gray-400" />
                            <flux:error name="email" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="!text-gray-700">Phone Number</flux:label>
                            <flux:input wire:model="phone" type="tel" placeholder="+60 12-345 6789" class="border-gray-300 bg-white !text-gray-900 placeholder:text-gray-400" />
                            <flux:error name="phone" />
                        </flux:field>
                    </div>
                </section>

                <section class="rounded-[2.5rem] border border-gray-200 bg-white p-6 shadow-sm sm:p-7">
                    <flux:heading size="lg" class="mb-5 !text-gray-900">Shipping Address</flux:heading>

                    <div class="grid gap-4">
                        <flux:field>
                            <flux:label class="!text-gray-700">Address Line 1</flux:label>
                            <flux:input wire:model="addressLine1" type="text" class="border-gray-300 bg-white !text-gray-900 placeholder:text-gray-400" />
                            <flux:error name="addressLine1" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="!text-gray-700">
                                Address Line 2
                                <flux:badge size="sm" variant="pill" class="ml-2 border border-gray-200 bg-gray-100 !text-gray-500">Optional</flux:badge>
                            </flux:label>
                            <flux:input wire:model="addressLine2" type="text" class="border-gray-300 bg-white !text-gray-900 placeholder:text-gray-400" />
                        </flux:field>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <flux:field>
                                <flux:label class="!text-gray-700">City</flux:label>
                                <flux:input wire:model="city" type="text" class="border-gray-300 bg-white !text-gray-900 placeholder:text-gray-400" />
                                <flux:error name="city" />
                            </flux:field>

                            <flux:field>
                                <flux:label class="!text-gray-700">State</flux:label>
                                <flux:select wire:model="state" placeholder="Select state" class="border-gray-300 bg-white !text-gray-900">
                                    @foreach ($this->malaysiaStates() as $malaysiaState)
                                        <flux:select.option :value="$malaysiaState">
                                            {{ $malaysiaState }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="state" />
                            </flux:field>

                            <flux:field>
                                <flux:label class="!text-gray-700">Postcode</flux:label>
                                <flux:input wire:model="postcode" type="text" class="border-gray-300 bg-white !text-gray-900 placeholder:text-gray-400" />
                                <flux:error name="postcode" />
                            </flux:field>
                        </div>
                    </div>
                </section>

                @if ($requiresSize)
                    <section class="rounded-[2.5rem] border border-gray-200 bg-white p-6 shadow-sm sm:p-7">
                        <flux:heading size="lg" class="mb-5 !text-gray-900">Product Options</flux:heading>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:field>
                                <flux:label class="!text-gray-700">{{ $sizeLabel }}</flux:label>
                                <flux:select wire:model.live="size" class="border-gray-300 bg-white !text-gray-900">
                                    @foreach ($sizeOptions as $sizeOption)
                                        <flux:select.option value="{{ $sizeOption }}">
                                            {{ $sizeOption }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="size" />
                            </flux:field>

                            <flux:field>
                                <flux:label class="!text-gray-700">Quantity</flux:label>
                                <flux:input wire:model.live="quantity" type="number" min="1" max="10" class="border-gray-300 bg-white !text-gray-900 placeholder:text-gray-400" />
                                <flux:error name="quantity" />
                            </flux:field>
                        </div>
                    </section>
                @else
                    <section class="rounded-[2.5rem] border border-gray-200 bg-white p-6 shadow-sm sm:p-7">
                        <flux:heading size="lg" class="mb-2 !text-gray-900">Product Options</flux:heading>
                        <flux:text class="text-gray-500">{{ $sizeLabel }}: {{ $size }}</flux:text>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <flux:field>
                                <flux:label class="!text-gray-700">Quantity</flux:label>
                                <flux:input wire:model.live="quantity" type="number" min="1" max="10" class="border-gray-300 bg-white !text-gray-900 placeholder:text-gray-400" />
                                <flux:error name="quantity" />
                            </flux:field>
                        </div>
                    </section>
                @endif

                <section class="rounded-[2.5rem] border border-gray-200 bg-white p-6 shadow-sm sm:p-7">
                    <flux:heading size="lg" class="mb-5 !text-gray-900">Payment Method</flux:heading>

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

            <aside class="h-fit rounded-[2.5rem] border border-gray-200 bg-white p-6 text-gray-900 shadow-sm lg:sticky lg:top-8">
                <flux:heading size="lg" class="mb-5 !text-gray-900">Order Summary</flux:heading>

                <div class="flex gap-4">
                    <img
                        src="{{ $productImage }}"
                        alt="{{ $productName }}"
                        class="h-20 w-20 rounded-xl border border-gray-200 object-cover"
                    >
                    <div>
                        <p class="font-semibold text-gray-900">{{ $productName }}</p>
                        <flux:text size="sm" class="text-gray-500">{{ $this->productSelectionLabel }}</flux:text>
                        <p class="mt-1 font-medium text-gray-900">{{ $this->formattedTotal }}</p>
                    </div>
                </div>

                <flux:separator class="my-5" />

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <flux:text class="!text-gray-600">Quantity</flux:text>
                        <span class="text-gray-900">{{ $quantity }}</span>
                    </div>
                    <div class="flex justify-between">
                        <flux:text class="!text-gray-600">Subtotal</flux:text>
                        <span class="text-gray-900">{{ $this->formattedTotal }}</span>
                    </div>
                    <div class="flex justify-between">
                        <flux:text class="!text-gray-600">Shipping</flux:text>
                        <span class="text-gray-900">Included</span>
                    </div>
                </div>

                <flux:separator class="my-5" />

                <div class="flex justify-between font-bold">
                    <span class="text-gray-900">Total</span>
                    <span class="text-gray-900">{{ $this->formattedTotal }}</span>
                </div>

                <flux:button type="submit" variant="primary" class="mt-6 w-full">
                    Place Order
                </flux:button>
            </aside>
        </form>
    </div>
</div>
