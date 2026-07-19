<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\MaintenanceRequest;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $leases = Lease::where('status', 'active')->with(['room', 'occupant'])->get();

        $requests = [
            [
                'title'       => 'AC tidak dingin',
                'description' => 'AC di kamar sudah 3 hari tidak terasa dingin. Sudah coba ganti suhu tapi tetap tidak berfungsi.',
                'priority'    => 'high',
                'status'      => 'in_progress',
            ],
            [
                'title'       => 'Keran kamar mandi bocor',
                'description' => 'Keran wastafel di kamar mandi menetes terus-menerus sejak kemarin malam.',
                'priority'    => 'medium',
                'status'      => 'open',
            ],
            [
                'title'       => 'Lampu kamar mati',
                'description' => 'Lampu utama kamar tiba-tiba mati. Sudah diganti bolam tapi tetap mati. Sepertinya ada masalah di instalasi.',
                'priority'    => 'medium',
                'status'      => 'open',
            ],
            [
                'title'       => 'Kunci pintu macet',
                'description' => 'Kunci pintu kamar sering macet dan susah dibuka. Harus dipaksa berkali-kali.',
                'priority'    => 'high',
                'status'      => 'resolved',
            ],
            [
                'title'       => 'Wifi lemot di kamar',
                'description' => 'Sinyal wifi sangat lemah di kamar saya. Di area lobi normal, tapi di kamar hampir tidak bisa browsing.',
                'priority'    => 'low',
                'status'      => 'open',
            ],
            [
                'title'       => 'Plafon bocor saat hujan',
                'description' => 'Waktu hujan deras kemarin, ada air menetes dari plafon di sudut kamar. Sudah pasang ember sementara.',
                'priority'    => 'urgent',
                'status'      => 'in_progress',
            ],
            [
                'title'       => 'Stopkontak rusak',
                'description' => 'Stopkontak dekat meja belajar tidak berfungsi. Sudah coba berbagai alat tapi tidak ada listrik.',
                'priority'    => 'medium',
                'status'      => 'waiting_parts',
            ],
        ];

        foreach ($requests as $i => $req) {
            $lease = $leases->get($i % $leases->count());
            if (!$lease) continue;

            MaintenanceRequest::create([
                'room_id'     => $lease->room_id,
                'occupant_id' => $lease->occupant_id,
                'title'       => $req['title'],
                'description' => $req['description'],
                'priority'    => $req['priority'],
                'status'      => $req['status'],
                'estimated_cost' => $req['status'] !== 'open' ? rand(50000, 500000) : null,
            ]);
        }
    }
}
