<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_agent_bundle_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bundle_subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('selling_price', 14, 2);
            $table->timestamps();

            $table->unique(['user_id', 'bundle_subscription_id'], 'sub_agent_bundle_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_agent_bundle_prices');
    }
};
