<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Pengaman penjualan: data demo (akun password lemah + contoh)
        // dilarang keras di production. Gunakan
        // `php artisan koskosan:create-owner` di server produksi.
        if (app()->isProduction()) {
            throw new \RuntimeException(
                'DatabaseSeeder diblokir di environment production. '.
                'Seeder berisi akun demo dan data contoh — jangan dijalankan di server produksi. '.
                'Buat akun owner dengan: php artisan koskosan:create-owner'
            );
        }

        $this->call([
            UserSeeder::class,
            SettingSeeder::class,
            PropertySeeder::class,
            OccupantSeeder::class,
            LeaseSeeder::class,
            InvoiceSeeder::class,
            MaintenanceSeeder::class,
            UtilityReadingSeeder::class,
            BookingRequestSeeder::class,
            EContractSeeder::class,
            RoomChecklistSeeder::class,
            BlogSeeder::class,
        ]);
    }
}
