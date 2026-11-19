<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Invoice::STATUSES + syncPaymentStatus() memakai 'partial', tapi ENUM DB
 * hanya ('draft','sent','paid','overdue','cancelled') → di MySQL produksi,
 * pembayaran sebagian / refund invoice lunas gagal 500. Samakan ENUM.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE invoices MODIFY status ENUM('draft','sent','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE invoices SET status = 'sent' WHERE status = 'partial'");
        DB::statement("ALTER TABLE invoices MODIFY status ENUM('draft','sent','paid','overdue','cancelled') NOT NULL DEFAULT 'draft'");
    }
};
