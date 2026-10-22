<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

/**
 * Lampirkan foto demo (lokal, lisensi bebas — lihat
 * storage/app/public/seed/ATTRIBUTION.txt) ke properti & tipe kamar
 * yang SUDAH ADA. Idempoten: update berdasarkan nama, tidak create.
 *
 * php artisan db:seed --class=SeedPhotosSeeder
 */
class SeedPhotosSeeder extends Seeder
{
    public function run(): void
    {
        Property::where('name', 'Kos Bahagia Pusat')->first()?->update([
            'photos' => ['seed/properties/alt-1.jpg', 'seed/rooms/deluxe-1.jpg'],
        ]);

        Property::where('name', 'Kos Bahagia Selatan')->first()?->update([
            'photos' => ['seed/properties/alt-3.jpg'],
        ]);

        $map = [
            'Kos Bahagia Pusat|Standar' => ['seed/rooms/standar-1.jpg', 'seed/rooms/standar-2.jpg'],
            'Kos Bahagia Pusat|Deluxe' => ['seed/rooms/deluxe-1.jpg'],
            'Kos Bahagia Pusat|VIP' => ['seed/rooms/vip-1.jpg', 'seed/rooms/vip-2.jpg'],
            'Kos Bahagia Selatan|Standar' => ['seed/rooms/deluxe-2.jpg'],
        ];

        foreach (RoomType::with('property')->get() as $type) {
            $key = $type->property->name.'|'.$type->name;
            if (isset($map[$key])) {
                $type->update(['photos' => $map[$key]]);
            }
        }
    }
}
