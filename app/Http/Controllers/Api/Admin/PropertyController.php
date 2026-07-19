<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index()
    {
        return response()->json(
            Property::withCount(['rooms', 'rooms as available_count' => fn ($q) => $q->where('status', 'available')])
                ->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'address'     => 'required|string',
            'city'        => 'required|string|max:100',
            'province'    => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'description' => 'nullable|string',
            'facilities'  => 'nullable|array',
            'rules'       => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $property = Property::create($data);
        return response()->json($property, 201);
    }

    public function show(Property $property)
    {
        $property->load(['roomTypes', 'rooms.roomType']);
        return response()->json($property);
    }

    public function update(Request $request, Property $property)
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:150',
            'address'     => 'sometimes|string',
            'city'        => 'sometimes|string|max:100',
            'province'    => 'sometimes|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'description' => 'nullable|string',
            'facilities'  => 'nullable|array',
            'rules'       => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $property->update($data);
        return response()->json($property);
    }

    public function destroy(Property $property)
    {
        $property->delete();
        return response()->json(['message' => 'Properti dihapus.']);
    }
}
