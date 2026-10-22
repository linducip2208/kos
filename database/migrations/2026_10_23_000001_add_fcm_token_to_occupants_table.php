<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Token FCM per penghuni untuk push notification aplikasi mobile.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('occupants', function (Blueprint $table) {
            $table->string('fcm_token', 255)->nullable()->after('portal_last_login');
        });
    }

    public function down(): void
    {
        Schema::table('occupants', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });
    }
};
