<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('momo_phone', 32)->nullable()->after('parent_user_id');
            $table->string('momo_account_name', 120)->nullable()->after('momo_phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['momo_phone', 'momo_account_name']);
        });
    }
};
