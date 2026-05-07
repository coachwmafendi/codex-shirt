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
                throw new \RuntimeException('ToyyibPay did not return a bill code.');
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
