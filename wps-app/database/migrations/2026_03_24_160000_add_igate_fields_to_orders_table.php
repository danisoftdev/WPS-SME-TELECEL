<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('igate_submitted_at')->nullable()->after('internal_notes');
            $table->unsignedSmallInteger('igate_last_http_code')->nullable()->after('igate_submitted_at');
            $table->text('igate_last_error')->nullable()->after('igate_last_http_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['igate_submitted_at', 'igate_last_http_code', 'igate_last_error']);
        });
    }
};
