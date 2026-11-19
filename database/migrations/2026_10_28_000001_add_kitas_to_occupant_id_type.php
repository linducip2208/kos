<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Form OccupantResource menawarkan 'kitas' tapi ENUM DB hanya
 * ('ktp','sim','passport') → di MySQL produksi, simpan dengan
 * id_type=kitas gagal 500. Samakan ENUM dengan opsi form.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE occupants MODIFY id_type ENUM('ktp','sim','passport','kitas') NOT NULL DEFAULT 'ktp'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE occupants SET id_type = 'ktp' WHERE id_type NOT IN ('ktp','sim','passport')");
        DB::statement("ALTER TABLE occupants MODIFY id_type ENUM('ktp','sim','passport') NOT NULL DEFAULT 'ktp'");
    }
};
