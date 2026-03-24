<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundle_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('total_data_gb', 12, 2); // e.g. 1000
            $table->decimal('amount', 14, 2); // e.g. 3400 GH¢
            $table->unsignedInteger('max_beneficiaries')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_subscriptions');
    }
};
