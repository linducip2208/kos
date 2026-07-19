<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        // ── Properti 1 ──────────────────────────────────────────────────────────
        $p1 = Property::create([
            'name'        => 'Kos Bahagia Pusat',
            'address'     => 'Jl. Melati No. 12, RT 03/RW 05',
            'city'        => 'Bandung',
            'province'    => 'Jawa Barat',
            'postal_code' => '40132',
            'description' => 'Kos strategis di pusat kota, dekat kampus dan pusat perbelanjaan. Lingkungan aman dan nyaman.',
            'facilities'  => ['wifi', 'parkir_motor', 'keamanan_24jam', 'dapur_bersama', 'laundry'],
            'rules'       => "Jam malam 23.00\nTidak merokok di dalam kamar\nTamu hanya sampai pukul 21.00",
            'is_active'   => true,
        ]);

        $types1 = [
            RoomType::create(['property_id' => $p1->id, 'name' => 'Standar',  'description' => 'Kamar standar dengan fasilitas dasar',  'base_price_monthly' => 800000,  'facilities' => ['kasur', 'lemari', 'meja_belajar']]),
            RoomType::create(['property_id' => $p1->id, 'name' => 'Deluxe',   'description' => 'Kamar luas dengan AC dan kamar mandi dalam', 'base_price_monthly' => 1200000, 'facilities' => ['kasur', 'lemari', 'meja_belajar', 'ac', 'km_dalam']]),
            RoomType::create(['property_id' => $p1->id, 'name' => 'VIP',      'description' => 'Kamar premium dengan TV dan kulkas',    'base_price_monthly' => 1800000, 'facilities' => ['kasur', 'lemari', 'meja_belajar', 'ac', 'km_dalam', 'tv', 'kulkas']]),
        ];

        $roomsP1 = [
            // Lantai 1
            ['number' => '101', 'floor' => 1, 'type' => $types1[0], 'price' => 800000,  'status' => 'occupied'],
            ['number' => '102', 'floor' => 1, 'type' => $types1[0], 'price' => 800000,  'status' => 'occupied'],
            ['number' => '103', 'floor' => 1, 'type' => $types1[0], 'price' => 800000,  'status' => 'available'],
            ['number' => '104', 'floor' => 1, 'type' => $types1[1], 'price' => 1200000, 'status' => 'occupied'],
            ['number' => '105', 'floor' => 1, 'type' => $types1[1], 'price' => 1200000, 'status' => 'available'],
            // Lantai 2
            ['number' => '201', 'floor' => 2, 'type' => $types1[1], 'price' => 1200000, 'status' => 'occupied'],
            ['number' => '202', 'floor' => 2, 'type' => $types1[1], 'price' => 1200000, 'status' => 'occupied'],
            ['number' => '203', 'floor' => 2, 'type' => $types1[2], 'price' => 1800000, 'status' => 'occupied'],
            ['number' => '204', 'floor' => 2, 'type' => $types1[2], 'price' => 1800000, 'status' => 'maintenance'],
            ['number' => '205', 'floor' => 2, 'type' => $types1[2], 'price' => 1800000, 'status' => 'available'],
        ];

        foreach ($roomsP1 as $r) {
            Room::create([
                'property_id'   => $p1->id,
                'room_type_id'  => $r['type']->id,
                'room_number'   => $r['number'],
                'floor'         => $r['floor'],
                'price_monthly' => $r['price'],
                'price_yearly'  => $r['price'] * 11,
                'status'        => $r['status'],
                'is_active'     => true,
            ]);
        }

        // ── Properti 2 ──────────────────────────────────────────────────────────
        $p2 = Property::create([
            'name'        => 'Kos Bahagia Selatan',
            'address'     => 'Jl. Anggrek No. 45, RT 07/RW 02',
            'city'        => 'Bandung',
            'province'    => 'Jawa Barat',
            'postal_code' => '40265',
            'description' => 'Kos tenang di kawasan perumahan, cocok untuk profesional muda.',
            'facilities'  => ['wifi', 'parkir_motor', 'parkir_mobil', 'dapur_bersama'],
            'is_active'   => true,
        ]);

        $typeStd2 = RoomType::create(['property_id' => $p2->id, 'name' => 'Standar', 'base_price_monthly' => 900000, 'facilities' => ['kasur', 'lemari', 'ac']]);

        $roomsP2 = [
            ['number' => 'A1', 'floor' => 1, 'price' => 900000,  'status' => 'occupied'],
            ['number' => 'A2', 'floor' => 1, 'price' => 900000,  'status' => 'occupied'],
            ['number' => 'A3', 'floor' => 1, 'price' => 900000,  'status' => 'available'],
            ['number' => 'B1', 'floor' => 2, 'price' => 1000000, 'status' => 'occupied'],
            ['number' => 'B2', 'floor' => 2, 'price' => 1000000, 'status' => 'available'],
        ];

        foreach ($roomsP2 as $r) {
            Room::create([
                'property_id'   => $p2->id,
                'room_type_id'  => $typeStd2->id,
                'room_number'   => $r['number'],
                'floor'         => $r['floor'],
                'price_monthly' => $r['price'],
                'price_yearly'  => $r['price'] * 11,
                'status'        => $r['status'],
                'is_active'     => true,
            ]);
        }
    }
}
