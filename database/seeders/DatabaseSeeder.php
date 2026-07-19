<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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
