<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite tidak support ENUM — skip (SQLite menyimpan sebagai TEXT, semua nilai valid)
        if (DB::getDriverName() !== 'mysql') return;

        DB::statement("ALTER TABLE leases MODIFY billing_cycle ENUM('daily','weekly','monthly','quarterly','yearly') NOT NULL DEFAULT 'monthly'");
        DB::statement("ALTER TABLE booking_requests MODIFY billing_cycle ENUM('daily','weekly','monthly','quarterly','yearly') NOT NULL DEFAULT 'monthly'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') return;

        DB::statement("ALTER TABLE leases MODIFY billing_cycle ENUM('monthly','quarterly','yearly') NOT NULL DEFAULT 'monthly'");
        DB::statement("ALTER TABLE booking_requests MODIFY billing_cycle ENUM('monthly','quarterly','yearly') NOT NULL DEFAULT 'monthly'");
    }
};
