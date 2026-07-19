<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\UtilityReading;
use Illuminate\Database\Seeder;

class UtilityReadingSeeder extends Seeder
{
    public function run(): void
    {
        $leases = Lease::where('status', 'active')->with('room')->get();

        foreach ($leases as $lease) {
            $electricBase = rand(100, 500);
            $waterBase    = rand(10, 50);

            // 3 bulan ke belakang + bulan ini
            for ($m = 3; $m >= 0; $m--) {
                $period = now()->subMonths($m)->startOfMonth()->toDateString();

                $prevElec = $electricBase + ($m * 50);
                $currElec = $prevElec + rand(30, 80);

                $prevWater = $waterBase + ($m * 5);
                $currWater = $prevWater + rand(3, 10);

                // Listrik
                UtilityReading::updateOrCreate(
                    ['room_id' => $lease->room_id, 'type' => 'electricity', 'billing_period' => $period],
                    [
                        'lease_id'         => $lease->id,
                        'previous_reading' => $prevElec,
                        'current_reading'  => $currElec,
                        'rate_per_unit'    => 1500,
                        'amount'           => ($currElec - $prevElec) * 1500,
                        'reading_date'     => now()->subMonths($m)->toDateString(),
                        'added_to_invoice' => $m > 0,
                    ]
                );

                // Air
                UtilityReading::updateOrCreate(
                    ['room_id' => $lease->room_id, 'type' => 'water', 'billing_period' => $period],
                    [
                        'lease_id'         => $lease->id,
                        'previous_reading' => $prevWater,
                        'current_reading'  => $currWater,
                        'rate_per_unit'    => 5000,
                        'amount'           => ($currWater - $prevWater) * 5000,
                        'reading_date'     => now()->subMonths($m)->toDateString(),
                        'added_to_invoice' => $m > 0,
                    ]
                );
            }
        }
    }
}
