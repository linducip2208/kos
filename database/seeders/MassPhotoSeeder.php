<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Foto seed AI-generated lokal (ComfyUI SDXL RealVisXL — lihat
 * storage/app/public/seed/ATTRIBUTION.txt). Idempoten: hanya menyentuh
 * baris yang photos-nya mengandung 'picsum'.
 *
 * php artisan db:seed --class=MassPhotoSeeder
 */
class MassPhotoSeeder extends Seeder
{
    public function run(): void
    {
        $disk = Storage::disk('public');

        $exteriors = collect(range(1, 28))
            ->map(fn ($i) => 'seed/mass/ext-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT).'.jpg')
            ->filter(fn ($p) => $disk->exists($p))
            ->values();

        $byType = [
            'Standar' => ['seed/rooms/standar-1.jpg', 'seed/rooms/standar-2.jpg', 'seed/mass/room-h1.jpg'],
            'Deluxe' => ['seed/rooms/deluxe-1.jpg', 'seed/rooms/deluxe-2.jpg'],
            'VIP' => ['seed/rooms/vip-1.jpg', 'seed/mass/room-h2.jpg'], // room-h2 = copy vip-1 (kamar). vip-2 = ruang tamu, hanya untuk galeri.
        ];
        foreach ($byType as $k => $list) {
            $byType[$k] = collect($list)->filter(fn ($p) => $disk->exists($p))->values()->all();
        }

        $propCount = $typeCount = 0;

        if ($exteriors->isNotEmpty()) {
            $i = 0;
            foreach (Property::where('photos', 'like', '%picsum%')->orderBy('id')->cursor() as $prop) {
                $prop->update(['photos' => [
                    $exteriors[$i % $exteriors->count()],
                    $exteriors[($i + 7) % $exteriors->count()],
                ]]);
                $i++;
                $propCount++;
            }
        }

        foreach (RoomType::where('photos', 'like', '%picsum%')->orderBy('id')->cursor() as $type) {
            $pool = $byType[$type->name] ?? [];
            if ($pool === []) {
                continue;
            }
            $type->update(['photos' => [$pool[$type->id % count($pool)]]]);
            $typeCount++;
        }

        $this->command->info("Foto diganti: {$propCount} properti, {$typeCount} tipe kamar.");
    }
}
