<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('daily_order_limit')->constrained('stores')->nullOnDelete();
            $table->foreignId('parent_user_id')->nullable()->after('store_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['parent_user_id']);
            $table->dropColumn(['store_id', 'parent_user_id']);
        });
    }
};
