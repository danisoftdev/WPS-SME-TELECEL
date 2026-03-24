<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('whatsapp_phone', 32)->nullable()->after('description');
            $table->string('contact_email')->nullable()->after('whatsapp_phone');
            $table->string('location', 255)->nullable()->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['description', 'whatsapp_phone', 'contact_email', 'location']);
        });
    }
};
