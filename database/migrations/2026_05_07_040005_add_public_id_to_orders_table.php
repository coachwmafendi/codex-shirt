<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Order;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'public_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('public_id')->nullable()->unique()->after('id');
            });
        }

        Order::query()
            ->whereNull('public_id')
            ->each(function (Order $order) {
                $order->forceFill([
                    'public_id' => 'ord_' . Str::random(24),
                ])->save();
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'public_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('public_id');
            });
        }
    }
};