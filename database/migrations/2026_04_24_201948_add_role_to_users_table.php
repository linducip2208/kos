<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['owner', 'staff', 'viewer'])->default('staff')->after('email');
            $table->string('phone', 20)->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('phone');
            $table->unsignedBigInteger('tenant_id')->nullable()->after('is_active')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'is_active', 'tenant_id']);
        });
    }
};
