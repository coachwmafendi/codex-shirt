<?php

use Livewire\Component;

new class extends Component {
    public int $price = 4900;

    public function getFormattedPriceProperty(): string
    {
        return 'RM' . number_format($this->price / 100, 2);
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
                    A clean black tee for builders, coders, and people who ship.
                    Bold white print. Simple. Direct. Made for daily wear.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <a
                    href="{{ route('checkout') }}"
                    wire:navigate
                    class="rounded-2xl bg-zinc-950 px-6 py-4 text-base font-semibold text-white shadow-sm transition hover:bg-zinc-800"
                >
                    Buy Now — {{ $this->formattedPrice }}
                </a>

                <a
                    href="#details"
                    class="rounded-2xl border border-zinc-200 px-6 py-4 text-base font-semibold text-zinc-900 transition hover:bg-zinc-50"
                >
                    View Details
                </a>
            </div>

            <div class="grid max-w-xl grid-cols-3 gap-4 border-t border-zinc-200 pt-8">
                <div>
                    <p class="text-2xl font-bold">RM49</p>
                    <p class="text-sm text-zinc-500">Launch price</p>
                </div>

                <div>
                    <p class="text-2xl font-bold">S–XXL</p>
                    <p class="text-sm text-zinc-500">Available sizes</p>
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

    <section id="details" class="border-t border-zinc-200 bg-zinc-50 px-6 py-20 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-3">
            <div class="rounded-3xl bg-white p-8 shadow-sm">
                <h2 class="text-xl font-bold">Bold everyday tee</h2>
                <p class="mt-3 text-zinc-600">
                    Black T-shirt with large white “I love Codex.” print. Easy to match, easy to wear.
                </p>
            </div>

            <div class="rounded-3xl bg-white p-8 shadow-sm">
                <h2 class="text-xl font-bold">Simple checkout</h2>
                <p class="mt-3 text-zinc-600">
                    Pay securely using ToyyibPay or Stripe. Choose your size and quantity during checkout.
                </p>
            </div>

            <div class="rounded-3xl bg-white p-8 shadow-sm">
                <h2 class="text-xl font-bold">For people who ship</h2>
                <p class="mt-3 text-zinc-600">
                    Made for developers, builders, makers, and anyone who loves turning ideas into products.
                </p>
            </div>
        </div>
    </section>
</div>