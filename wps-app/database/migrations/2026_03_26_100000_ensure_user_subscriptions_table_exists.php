<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Safety net when the original migration batch did not create user_subscriptions
 * (e.g. partial migrate, or DB restored without that table).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_subscriptions')) {
            return;
        }

        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bundle_subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance_gb', 12, 2)->default(0);
            $table->json('beneficiaries')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'bundle_subscription_id']);
        });
    }

    public function down(): void
    {
        // No-op: table may have been created by 2026_03_12_141004_create_user_subscriptions_table.
    }
};
