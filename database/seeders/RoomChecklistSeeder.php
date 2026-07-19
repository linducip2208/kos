<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\RoomChecklist;
use Illuminate\Database\Seeder;

class RoomChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $defaultItems = [
            ['item' => 'Kasur & bantal', 'condition' => 'good',    'notes' => ''],
            ['item' => 'Lemari pakaian', 'condition' => 'good',    'notes' => ''],
            ['item' => 'Meja & kursi',   'condition' => 'good',    'notes' => ''],
            ['item' => 'AC / kipas',     'condition' => 'good',    'notes' => ''],
            ['item' => 'Lampu kamar',    'condition' => 'good',    'notes' => ''],
            ['item' => 'Kunci pintu',    'condition' => 'good',    'notes' => ''],
            ['item' => 'Kamar mandi',    'condition' => 'good',    'notes' => ''],
            ['item' => 'Jendela',        'condition' => 'good',    'notes' => ''],
            ['item' => 'Lantai',         'condition' => 'good',    'notes' => ''],
            ['item' => 'Dinding/cat',    'condition' => 'good',    'notes' => ''],
        ];

        $leases = Lease::where('status', 'active')->with(['room', 'occupant'])->take(3)->get();

        foreach ($leases as $i => $lease) {
            // Check-in checklist
            RoomChecklist::create([
                'lease_id'    => $lease->id,
                'room_id'     => $lease->room_id,
                'occupant_id' => $lease->occupant_id,
                'type'        => 'check_in',
                'items'       => $defaultItems,
                'damage_cost' => 0,
                'deposit_refund' => 0,
                'signed_by'   => $lease->occupant->name,
                'signed_at'   => $lease->start_date,
            ]);
        }

        // Satu check-out dengan kerusakan (dari kontrak expired jika ada)
        $expiredLease = Lease::where('status', 'expired')->with(['room', 'occupant'])->first();
        if ($expiredLease) {
            $itemsWithDamage = $defaultItems;
            $itemsWithDamage[3]['condition'] = 'damaged';
            $itemsWithDamage[3]['notes']     = 'AC tidak berfungsi normal, perlu service';

            RoomChecklist::create([
                'lease_id'       => $expiredLease->id,
                'room_id'        => $expiredLease->room_id,
                'occupant_id'    => $expiredLease->occupant_id,
                'type'           => 'check_out',
                'items'          => $itemsWithDamage,
                'damage_cost'    => 350000,
                'deposit_refund' => $expiredLease->deposit - 350000,
                'signed_by'      => $expiredLease->occupant->name ?? 'Penyewa',
                'signed_at'      => $expiredLease->end_date,
            ]);
        }
    }
}
