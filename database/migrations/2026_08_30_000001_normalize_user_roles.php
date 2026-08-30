<?php

use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'staff')->update(['role' => 'property_manager']);
        DB::table('users')->where('role', 'viewer')->update(['role' => 'auditor']);
        DB::table('users')->whereNotIn('role', array_keys(Permissions::ROLES))->update(['role' => 'property_manager']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','owner','property_manager','finance','cashier','customer_service','marketing','maintenance','security','auditor') NOT NULL DEFAULT 'property_manager'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('owner','staff','viewer') NOT NULL DEFAULT 'staff'");
        }

        DB::table('users')->where('role', 'property_manager')->update(['role' => 'staff']);
        DB::table('users')->where('role', 'auditor')->update(['role' => 'viewer']);
    }
};
