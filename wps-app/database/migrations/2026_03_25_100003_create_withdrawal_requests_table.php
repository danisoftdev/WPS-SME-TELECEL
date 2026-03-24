<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('status', 32)->default('pending'); // pending, approved, paid, rejected, failed
            $table->string('momo_phone', 32);
            $table->string('momo_account_name', 120);
            $table->text('rejection_reason')->nullable();
            $table->text('admin_note')->nullable();
            $table->string('paystack_recipient_code')->nullable();
            $table->string('paystack_transfer_reference')->nullable();
            $table->string('paystack_transfer_code')->nullable();
            $table->text('paystack_last_response')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
