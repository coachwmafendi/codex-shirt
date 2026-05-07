<?php

use App\Models\Order;
use App\Services\Payments\ToyyibPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

function toyyibPayOrder(): Order
{
    return Order::create([
        'order_number' => 'CODX-20260507-TEST01',
        'customer_name' => 'Test Customer',
        'customer_email' => 'customer@example.com',
        'customer_phone' => '60123456789',
        'shipping_address_line_1' => 'No. 1',
        'shipping_address_line_2' => null,
        'shipping_city' => 'Kuala Lumpur',
        'shipping_state' => 'Kuala Lumpur',
        'shipping_postcode' => '50000',
        'shipping_country' => 'Malaysia',
        'product_name' => 'I love Codex T-Shirt',
        'product_sku' => 'CODEX-TSHIRT-BLACK',
        'size' => 'M',
        'quantity' => 1,
        'unit_price' => 4900,
        'total_amount' => 4900,
        'payment_gateway' => 'toyyibpay',
        'payment_status' => 'pending',
    ]);
}

test('it creates a bill when ToyyibPay returns a json response with a bom', function () {
    Http::fake([
        'https://dev.toyyibpay.com/index.php/api/createBill' => Http::response("\xEF\xBB\xBF[{\"BillCode\":\"ABC123\"}]", 200),
    ]);

    $order = toyyibPayOrder();

    $paymentUrl = app(ToyyibPayService::class)->createBill($order);

    expect($paymentUrl)->toBe('https://dev.toyyibpay.com/ABC123');
    expect($order->refresh()->toyyibpay_bill_code)->toBe('ABC123');
});

test('it throws when ToyyibPay does not return a bill code', function () {
    Http::fake([
        'https://dev.toyyibpay.com/index.php/api/createBill' => Http::response('{"message":"invalid"}', 200),
    ]);

    $order = toyyibPayOrder();

    expect(fn () => app(ToyyibPayService::class)->createBill($order))
        ->toThrow(\RuntimeException::class, 'ToyyibPay did not return a bill code.');
});
