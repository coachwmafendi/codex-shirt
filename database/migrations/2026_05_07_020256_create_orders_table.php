<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();

        $table->string('order_number')->unique();

        $table->string('customer_name');
        $table->string('customer_email');
        $table->string('customer_phone');

        $table->string('shipping_address_line_1');
        $table->string('shipping_address_line_2')->nullable();
        $table->string('shipping_city');
        $table->string('shipping_state');
        $table->string('shipping_postcode');
        $table->string('shipping_country')->default('Malaysia');

        $table->string('product_name');
        $table->string('product_sku')->nullable();
        $table->string('size');
        $table->unsignedInteger('quantity')->default(1);
        $table->unsignedInteger('unit_price'); // sen
        $table->unsignedInteger('total_amount'); // sen

        $table->string('payment_gateway'); // toyyibpay / stripe
        $table->string('payment_status')->default('pending');

        $table->string('stripe_session_id')->nullable();
        $table->string('stripe_payment_intent_id')->nullable();

        $table->string('toyyibpay_bill_code')->nullable();
        $table->string('toyyibpay_transaction_id')->nullable();

        $table->timestamp('paid_at')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
