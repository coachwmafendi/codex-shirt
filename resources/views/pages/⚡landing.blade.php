<?php

use Livewire\Component;

new class extends Component {
    /**
     * @return array<string, array<string, mixed>>
     */
    public function products(): array
    {
        return [
            'shirt' => [
                'name' => 'I love Codex T-Shirt',
                'description' => 'A clean black tee for builders, coders, and people who ship.',
                'price' => 4900,
                'image' => asset('images/products/codex-shirt.png'),
                'checkout' => route('checkout', ['product' => 'shirt']),
                'cta' => 'Shop Shirt',
                'highlight' => 'Launch price',
                'detail' => 'Black tee with a large white print.',
            ],
            'sticker' => [
                'name' => 'Codex Sticker',
                'description' => 'A compact sticker for laptops, notebooks, and gear.',
                'price' => 500,
                'image' => asset('images/products/codex-sticker.jpg'),
                'checkout' => route('checkout', ['product' => 'sticker']),
                'cta' => 'Shop Sticker',
                'highlight' => 'Single sticker',
                'detail' => 'Simple sticker for your daily setup.',
            ],
        ];
    }

    public function formatPrice(int $price): string
    {
        return 'RM' . number_format($price / 100, 2);
    }
};
?>

<div class="min-h-screen bg-white text-zinc-950">
    <section class="mx-auto grid min-h-screen max-w-7xl items-center gap-12 px-6 py-12 lg:grid-cols-2 lg:px-8">
        <div class="order-2 space-y-8 lg:order-1">
            <div class="space-y-4">
                <p class="inline-flex rounded-full bg-zinc-100 px-4 py-2 text-sm font-medium text-zinc-700">
                    Limited Drop
                </p>

                <h1 class="text-5xl font-black tracking-tight text-zinc-950 sm:text-6xl lg:text-7xl">
                    I love Codex.
                </h1>

                <p class="max-w-xl text-lg leading-8 text-zinc-600">
                    Two small pieces of merch for people who ship: the original tee and a new sticker drop.
                    Keep it simple, choose your item, and check out in one flow.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <a
                    href="{{ $this->products()['shirt']['checkout'] }}"
                    wire:navigate
                    class="rounded-2xl bg-zinc-950 px-6 py-4 text-base font-semibold text-white shadow-sm transition hover:bg-zinc-800"
                >
                    Buy Shirt — {{ $this->formatPrice($this->products()['shirt']['price']) }}
                </a>

                <a
                    href="{{ $this->products()['sticker']['checkout'] }}"
                    wire:navigate
                    class="rounded-2xl border border-zinc-200 px-6 py-4 text-base font-semibold text-zinc-900 transition hover:bg-zinc-50"
                >
                    Buy Sticker — {{ $this->formatPrice($this->products()['sticker']['price']) }}
                </a>
            </div>

            <div class="grid max-w-xl grid-cols-3 gap-4 border-t border-zinc-200 pt-8">
                <div>
                    <p class="text-2xl font-bold">RM49</p>
                    <p class="text-sm text-zinc-500">T-shirt</p>
                </div>

                <div>
                    <p class="text-2xl font-bold">RM5</p>
                    <p class="text-sm text-zinc-500">Sticker</p>
                </div>

                <div>
                    <p class="text-2xl font-bold">MY</p>
                    <p class="text-sm text-zinc-500">Ships locally</p>
                </div>
            </div>
        </div>

        <div class="order-1 lg:order-2">
            <div class="rounded-[2rem] bg-zinc-100 p-6 shadow-sm">
                <img
                    src="{{ asset('images/products/codex-shirt.png') }}"
                    alt="I love Codex T-Shirt"
                    class="w-full rounded-[1.5rem] object-cover"
                >
            </div>
        </div>
    </section>

    <section class="border-t border-zinc-200 bg-zinc-50 px-6 py-20 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-2">
            @foreach ($this->products() as $product)
                <article class="rounded-3xl bg-white p-8 shadow-sm">
                    <div class="grid gap-6 lg:grid-cols-[180px_1fr] lg:items-center">
                        <img
                            src="{{ $product['image'] }}"
                            alt="{{ $product['name'] }}"
                            class="w-full rounded-2xl border border-zinc-200 object-cover"
                        >

                        <div>
                            <p class="text-sm font-medium uppercase tracking-[0.18em] text-zinc-400">
                                {{ $product['highlight'] }}
                            </p>

                            <h2 class="mt-2 text-2xl font-bold text-zinc-950">
                                {{ $product['name'] }}
                            </h2>

                            <p class="mt-3 text-zinc-600">
                                {{ $product['description'] }}
                            </p>

                            <p class="mt-4 text-2xl font-bold text-zinc-950">
                                {{ $this->formatPrice($product['price']) }}
                            </p>

                            <p class="mt-1 text-sm text-zinc-500">
                                {{ $product['detail'] }}
                            </p>

                            <a
                                href="{{ $product['checkout'] }}"
                                wire:navigate
                                class="mt-6 inline-flex rounded-2xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800"
                            >
                                {{ $product['cta'] }}
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</div>
