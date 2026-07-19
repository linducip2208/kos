<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\Occupant;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminId   = User::first()->id;
        $occupants = Occupant::orderBy('id')->get();
        $rooms     = Room::where('status', 'occupied')->orderBy('id')->get();

        // Pasangkan setiap kamar occupied dengan penyewa
        $pairs = [
            [$rooms->get(0), $occupants->get(0), now()->subMonths(6),  now()->addMonths(6),  'active'],   // 101 - Andi
            [$rooms->get(1), $occupants->get(1), now()->subMonths(3),  now()->addMonths(9),  'active'],   // 102 - Siti
            [$rooms->get(2), $occupants->get(2), now()->subMonths(10), now()->addMonths(2),  'active'],   // 104 - Budi (hampir habis)
            [$rooms->get(3), $occupants->get(3), now()->subMonths(2),  now()->addMonths(10), 'active'],   // 201 - Dewi
            [$rooms->get(4), $occupants->get(4), now()->subMonths(4),  now()->addMonths(8),  'active'],   // 202 - Rizky
            [$rooms->get(5), $occupants->get(5), now()->subMonths(7),  now()->addMonths(5),  'active'],   // 203 - Maya
            [$rooms->get(6), $occupants->get(6), now()->subMonths(12), now()->addMonths(1),  'active'],   // A1  - Hendra (hampir habis)
        ];

        foreach ($pairs as $i => [$room, $occupant, $start, $end, $status]) {
            if (!$room || !$occupant) continue;

            Lease::create([
                'room_id'       => $room->id,
                'occupant_id'   => $occupant->id,
                'lease_number'  => 'LSE-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'start_date'    => $start->toDateString(),
                'end_date'      => $end->toDateString(),
                'price'         => $room->price_monthly,
                'deposit'       => $room->price_monthly,
                'billing_cycle' => 'monthly',
                'billing_date'  => 1,
                'status'        => $status,
                'created_by'    => $adminId,
            ]);
        }

        // Satu kontrak expired (historis)
        $expiredRoom = Room::where('status', 'available')->first();
        if ($expiredRoom) {
            Lease::create([
                'room_id'       => $expiredRoom->id,
                'occupant_id'   => $occupants->first()->id,
                'lease_number'  => 'LSE-0099',
                'start_date'    => now()->subYears(2)->toDateString(),
                'end_date'      => now()->subYear()->toDateString(),
                'price'         => $expiredRoom->price_monthly ?? 800000,
                'deposit'       => $expiredRoom->price_monthly ?? 800000,
                'billing_cycle' => 'monthly',
                'billing_date'  => 1,
                'status'        => 'expired',
                'created_by'    => $adminId,
            ]);
        }
    }
}
