<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::withCount([
            'rooms',
            'rooms as available_rooms_count' => fn ($q) => $q->where('status', 'available'),
        ])->where('is_active', true)->get();

        return response()->json($properties->map(fn ($p) => [
            'id'              => $p->id,
            'slug'            => $p->slug,
            'name'            => $p->name,
            'address'         => $p->address,
            'city'            => $p->city,
            'province'        => $p->province,
            'description'     => $p->description,
            'facilities'      => $p->facilities ?? [],
            'photos'          => collect($p->photos ?? [])->map(fn ($ph) => photo_url($ph))->values()->all(),
            'total_rooms'     => $p->rooms_count,
            'available_rooms' => $p->available_rooms_count,
            'booking_url'     => url("/booking/{$p->id}"),
        ]));
    }

    public function show($key)
    {
        // Terima slug maupun ID (backward compat dengan mobile client lama).
        $property = Property::query()
            ->where('slug', $key)
            ->orWhere(fn ($q) => $q->whereKey($key)->where('is_active', true))
            ->with(['rooms' => fn ($q) => $q->where('status', 'available')->with('roomType')])
            ->active()
            ->firstOrFail();

        return response()->json([
            'id'          => $property->id,
            'slug'        => $property->slug,
            'name'        => $property->name,
            'address'     => $property->address,
            'city'        => $property->city,
            'province'    => $property->province,
            'description' => $property->description,
            'facilities'  => $property->facilities ?? [],
            'photos'      => collect($property->photos ?? [])->map(fn ($ph) => photo_url($ph))->values()->all(),
            'rooms'       => $property->rooms->map(fn ($r) => [
                'id'             => $r->id,
                'room_number'    => $r->room_number,
                'floor'          => $r->floor,
                'size'           => $r->size,
                'price_monthly'  => $r->price_monthly,
                'price_yearly'   => $r->price_yearly,
                'type'           => $r->roomType?->name,
                'type_photos'    => collect($r->roomType?->photos ?? [])->map(fn ($ph) => photo_url($ph))->values()->all(),
                'facilities'     => $r->facilities,
            ]),
        ]);
    }
}
