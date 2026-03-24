<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bundle_subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('selling_price', 14, 2);
            $table->string('customer_phone', 32);
            $table->decimal('amount_total', 14, 2);
            $table->decimal('platform_fee', 14, 2)->default(0);
            $table->decimal('net_to_merchant', 14, 2);
            $table->string('reference', 64)->unique();
            $table->string('status', 32)->default('pending'); // pending, success, failed
            $table->json('paystack_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_checkouts');
    }
};
