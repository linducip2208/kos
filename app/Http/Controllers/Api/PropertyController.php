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
            'name'            => $p->name,
            'address'         => $p->address,
            'description'     => $p->description,
            'total_rooms'     => $p->rooms_count,
            'available_rooms' => $p->available_rooms_count,
            'booking_url'     => url("/booking/{$p->id}"),
        ]));
    }

    public function show(Property $property)
    {
        $property->load(['rooms' => fn ($q) => $q->where('status', 'available')->with('roomType')]);

        return response()->json([
            'id'          => $property->id,
            'name'        => $property->name,
            'address'     => $property->address,
            'description' => $property->description,
            'rooms'       => $property->rooms->map(fn ($r) => [
                'id'             => $r->id,
                'room_number'    => $r->room_number,
                'floor'          => $r->floor,
                'size'           => $r->size,
                'price_monthly'  => $r->price_monthly,
                'price_yearly'   => $r->price_yearly,
                'type'           => $r->roomType?->name,
                'facilities'     => $r->facilities,
            ]),
        ]);
    }
}
