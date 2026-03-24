<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bundle_subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance_gb', 12, 2)->default(0); // remaining data from pool
            $table->json('beneficiaries')->nullable(); // array of phone numbers
            $table->timestamps();
            $table->index(['user_id', 'bundle_subscription_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
