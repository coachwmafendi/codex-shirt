<?php

namespace App\Models;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
        'shipping_country',
        'product_name',
        'product_sku',
        'size',
        'quantity',
        'unit_price',
        'total_amount',
        'payment_gateway',
        'payment_status',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'toyyibpay_bill_code',
        'toyyibpay_transaction_id',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function getFormattedTotalAttribute(): string
    {
        return 'RM'.number_format($this->total_amount / 100, 2);
    }

    protected static function booted(): void
{
    static::creating(function (Order $order) {
        if (blank($order->public_id)) {
            $order->public_id = 'ord_' . Str::random(24);
        }
    });
}
}
