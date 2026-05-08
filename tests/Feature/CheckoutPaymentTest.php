<?php

use App\Models\Order;
use App\Services\Payments\ToyyibPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('it surfaces a gateway error when ToyyibPay checkout fails', function () {
    app()->bind(ToyyibPayService::class, function () {
        return new class extends ToyyibPayService
        {
            public function createBill(Order $order): string
            {
                throw new RuntimeException('ToyyibPay did not return a bill code.');
            }
        };
    });

    Livewire::test('pages::checkout')
        ->set('name', 'Wan Muhammad Afnan')
        ->set('email', 'afnan@example.com')
        ->set('phone', '60193831240')
        ->set('addressLine1', 'Lot 13347')
        ->set('addressLine2', 'Kampung VV')
        ->set('city', 'Ketereh')
        ->set('state', 'Kelantan')
        ->set('postcode', '16450')
        ->set('country', 'Malaysia')
        ->set('size', 'M')
        ->set('quantity', 1)
        ->set('gateway', 'toyyibpay')
        ->call('submit')
        ->assertHasErrors(['gateway']);

    expect(Order::count())->toBe(1);
});

test('it creates a sticker order with the sticker product data', function () {
    app()->bind(ToyyibPayService::class, function () {
        return new class extends ToyyibPayService
        {
            public function createBill(Order $order): string
            {
                expect($order->product_name)->toBe('Codex Sticker');
                expect($order->product_sku)->toBe('CODEX-STICKER');
                expect($order->unit_price)->toBe(500);
                expect($order->size)->toBe('Single sticker');

                return 'https://example.test/pay';
            }
        };
    });

    Livewire::test('pages::checkout')
        ->set('productKey', 'sticker')
        ->set('name', 'Wan Muhammad Afnan')
        ->set('email', 'afnan@example.com')
        ->set('phone', '60193831240')
        ->set('addressLine1', 'Lot 13347')
        ->set('addressLine2', 'Kampung VV')
        ->set('city', 'Ketereh')
        ->set('state', 'Kelantan')
        ->set('postcode', '16450')
        ->set('country', 'Malaysia')
        ->set('quantity', 3)
        ->set('gateway', 'toyyibpay')
        ->call('submit');

    $order = Order::query()->latest('id')->first();

    expect($order?->product_name)->toBe('Codex Sticker');
    expect($order?->product_sku)->toBe('CODEX-STICKER');
    expect($order?->unit_price)->toBe(500);
    expect($order?->total_amount)->toBe(1500);
    expect($order?->size)->toBe('Single sticker');
});
