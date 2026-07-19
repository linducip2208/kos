<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->decimal('base_price_daily', 15, 2)->nullable()->after('base_price_monthly');
            $table->decimal('base_price_weekly', 15, 2)->nullable()->after('base_price_daily');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('price_daily', 15, 2)->nullable()->after('price_monthly');
            $table->decimal('price_weekly', 15, 2)->nullable()->after('price_daily');
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn(['base_price_daily', 'base_price_weekly']);
        });
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['price_daily', 'price_weekly']);
        });
    }
};
