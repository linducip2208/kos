<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('occupants', function (Blueprint $table) {
            $table->string('portal_password')->nullable()->after('notes');
            $table->string('remember_token', 100)->nullable()->after('portal_password');
            $table->timestamp('portal_last_login')->nullable()->after('remember_token');
            $table->boolean('portal_active')->default(true)->after('portal_last_login');
        });
    }

    public function down(): void
    {
        Schema::table('occupants', function (Blueprint $table) {
            $table->dropColumn(['portal_password', 'remember_token', 'portal_last_login', 'portal_active']);
        });
    }
};
